<?php
/**
 * Scraper de Pokémons - Pokédex PokemonDB
 * Fonte: https://pokemondb.net/pokedex/all
 * 
 * Extrai todos os dados da Pokédex Nacional:
 * - Número Nacional
 * - Nome e Slug
 * - Tipos (Primário e Secundário)
 * - Estatísticas Base (Total, HP, Attack, Defense, Sp. Atk, Sp. Def, Speed)
 * - Imagens em alta resolução
 * 
 * Pode ser executado via CLI (php backend/scraper.php) ou via HTTP GET.
 */

require_once __DIR__ . '/../config/bd.php';

// Limite de tempo e memória adequados para scraping
set_time_limit(600);
ini_set('memory_limit', '512M');

$isCli = (php_sapi_name() === 'cli');

/**
 * Função utilitária para requisição HTTP com cURL e fallback
 */
function fetchHtml(string $url): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 PokédexBot/2.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($html)) {
            return $html;
        }
    }

    // Fallback com stream_context
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\nAccept: text/html\r\n",
            'timeout' => 60
        ]
    ];
    $context = stream_context_create($opts);
    $html = @file_get_contents($url, false, $context);
    return $html ?: null;
}

/**
 * Converte nome para slug limpo
 */
function createSlug(string $name): string {
    $slug = strtolower($name);
    $slug = preg_replace('/[^\w\s-]/u', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Executa o processo de scraping
 */
function runScraper(int $limit = 0): array {
    global $isCli;

    $url = 'https://pokemondb.net/pokedex/all';
    $html = fetchHtml($url);

    if (!$html) {
        return [
            'success' => false,
            'message' => "Falha ao acessar $url. Verifique a conexão com a internet."
        ];
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $rows = $xpath->query('//table[@id="pokedex"]/tbody/tr');

    if ($rows->length === 0) {
        return [
            'success' => false,
            'message' => 'Tabela de Pokémons não encontrada na página.'
        ];
    }

    $pdo = getDbConnection();
    $useDb = ($pdo !== null);

    // Prepara statement de inserção segura com PDO
    $stmt = null;
    if ($useDb) {
        $sql = "INSERT INTO `pokemons` 
            (`national_id`, `name`, `slug`, `type1`, `type2`, `total`, `hp`, `attack`, `defense`, `sp_atk`, `sp_def`, `speed`, `image_url`, `is_secret`, `description`)
            VALUES 
            (:nat_id, :name, :slug, :type1, :type2, :total, :hp, :attack, :defense, :sp_atk, :sp_def, :speed, :image_url, 0, :desc)
            ON DUPLICATE KEY UPDATE 
            `name` = VALUES(`name`),
            `type1` = VALUES(`type1`),
            `type2` = VALUES(`type2`),
            `total` = VALUES(`total`),
            `hp` = VALUES(`hp`),
            `attack` = VALUES(`attack`),
            `defense` = VALUES(`defense`),
            `sp_atk` = VALUES(`sp_atk`),
            `sp_def` = VALUES(`sp_def`),
            `speed` = VALUES(`speed`),
            `image_url` = VALUES(`image_url`)";
        $stmt = $pdo->prepare($sql);
    }

    $scrapedPokemons = [];
    $processedCount = 0;
    $insertedCount = 0;

    foreach ($rows as $row) {
        if ($limit > 0 && $processedCount >= $limit) {
            break;
        }

        $cells = $row->getElementsByTagName('td');
        if ($cells->length < 10) {
            continue;
        }

        // 0: Número (#001)
        $numText = trim($cells->item(0)->textContent);
        $nationalId = (int) ltrim($numText, '#0');
        if ($nationalId === 0) {
            $nationalId = (int) preg_replace('/\D/', '', $numText);
        }

        // 1: Nome
        $nameNode = $cells->item(1)->getElementsByTagName('a')->item(0);
        $name = $nameNode ? trim($nameNode->textContent) : trim($cells->item(1)->textContent);
        
        // Verifica forma alternativa (ex: Mega, Alolan, etc. no subtítulo)
        $subNameNode = $cells->item(1)->getElementsByTagName('small')->item(0);
        if ($subNameNode) {
            $formName = trim($subNameNode->textContent);
            if (!empty($formName)) {
                $name .= ' (' . $formName . ')';
            }
        }

        // 2: Tipos
        $typeLinks = $cells->item(2)->getElementsByTagName('a');
        $type1 = $typeLinks->item(0) ? trim($typeLinks->item(0)->textContent) : 'Normal';
        $type2 = $typeLinks->item(1) ? trim($typeLinks->item(1)->textContent) : null;

        // 3: Total
        $total = (int) trim($cells->item(3)->textContent);

        // 4 a 9: Stats (HP, Attack, Defense, Sp. Atk, Sp. Def, Speed)
        $hp = (int) trim($cells->item(4)->textContent);
        $attack = (int) trim($cells->item(5)->textContent);
        $defense = (int) trim($cells->item(6)->textContent);
        $spAtk = (int) trim($cells->item(7)->textContent);
        $spDef = (int) trim($cells->item(8)->textContent);
        $speed = (int) trim($cells->item(9)->textContent);

        // Slug e URL de imagem 3D oficial em PNG transparente (Pokémon Home 3D)
        $baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' (', $name)[0]));
        $slug = createSlug($name);
        $imageUrl = sprintf('https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/home/%d.png', $nationalId);

        $description = sprintf(
            'Pokémon número %03d com atributos base de %d. Especialista no tipo %s%s.',
            $nationalId,
            $total,
            $type1,
            $type2 ? " e $type2" : ""
        );

        $pokemonData = [
            'national_id' => $nationalId,
            'name' => $name,
            'slug' => $slug,
            'type1' => $type1,
            'type2' => $type2,
            'total' => $total,
            'hp' => $hp,
            'attack' => $attack,
            'defense' => $defense,
            'sp_atk' => $spAtk,
            'sp_def' => $spDef,
            'speed' => $speed,
            'image_url' => $imageUrl,
            'description' => $description
        ];

        $scrapedPokemons[] = $pokemonData;
        $processedCount++;

        // Persistência no MySQL com PDO Prepared Statements
        if ($useDb && $stmt) {
            try {
                $stmt->execute([
                    ':nat_id' => $nationalId,
                    ':name' => $name,
                    ':slug' => $slug,
                    ':type1' => $type1,
                    ':type2' => $type2,
                    ':total' => $total,
                    ':hp' => $hp,
                    ':attack' => $attack,
                    ':defense' => $defense,
                    ':sp_atk' => $spAtk,
                    ':sp_def' => $spDef,
                    ':speed' => $speed,
                    ':image_url' => $imageUrl,
                    ':desc' => $description
                ]);
                $insertedCount++;
            } catch (PDOException $e) {
                // Log sem travar o lote
                error_log("Erro ao salvar Pokémon {$name}: " . $e->getMessage());
            }
        }
    }

    // Garante que o Fantasma do Comunismo permaneça na base
    if ($useDb) {
        $secretCheck = $pdo->query("SELECT COUNT(*) FROM `pokemons` WHERE `national_id` = 9999")->fetchColumn();
        if ($secretCheck == 0) {
            $pdo->exec("INSERT INTO `pokemons` (`national_id`, `name`, `slug`, `type1`, `type2`, `total`, `hp`, `attack`, `defense`, `sp_atk`, `sp_def`, `speed`, `image_url`, `is_secret`, `description`) 
                VALUES (9999, 'Fantasma do Comunismo', 'fantasma-do-comunismo', 'Ghost', 'Revolutionary', 1917, 200, 191, 191, 200, 200, 180, 'assets/img/fantasma_comunismo.svg', 1, 'Um espectro que ronda a Pokédex! Proletários de todos os continentes, uni-vos!')");
        }
    }

    // Atualiza cache JSON local
    $jsonPath = __DIR__ . '/../database/pokemons_seed.json';
    if (!empty($scrapedPokemons)) {
        // Inclui o fantasma no JSON se não presente
        $hasSecret = false;
        foreach ($scrapedPokemons as $p) {
            if ($p['national_id'] === 9999) {
                $hasSecret = true;
                break;
            }
        }
        if (!$hasSecret) {
            $scrapedPokemons[] = [
                'id' => 9999,
                'national_id' => 9999,
                'name' => 'Fantasma do Comunismo',
                'slug' => 'fantasma-do-comunismo',
                'type1' => 'Ghost',
                'type2' => 'Revolutionary',
                'total' => 1917,
                'hp' => 200,
                'attack' => 191,
                'defense' => 191,
                'sp_atk' => 200,
                'sp_def' => 200,
                'speed' => 180,
                'image_url' => 'assets/img/fantasma_comunismo.svg',
                'is_secret' => 1,
                'description' => 'Um espectro que ronda a Pokédex! Proletários de todos os continentes, uni-vos!'
            ];
        }
        @file_put_contents($jsonPath, json_encode($scrapedPokemons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    return [
        'success' => true,
        'source' => $url,
        'total_scraped' => count($scrapedPokemons),
        'db_inserted' => $insertedCount,
        'db_active' => $useDb,
        'message' => sprintf(
            'Scraping concluído com sucesso! %d Pokémons processados da Pokédex nacional.%s',
            count($scrapedPokemons),
            $useDb ? " $insertedCount salvos no MySQL." : " Salvos no cache JSON (MySQL desconectado)."
        )
    ];
}

// Execução direta (CLI ou Web)
if ($isCli) {
    echo "==========================================\n";
    echo "   POKÉDEX SCRAPER - POKEMONDB.NET/POKEDEX/ALL\n";
    echo "==========================================\n";
    echo "Iniciando raspagem de dados...\n";
    $result = runScraper();
    echo $result['message'] . "\n";
} else {
    // Resposta JSON para acionamento via painel web ou API
    header('Content-Type: application/json; charset=utf-8');
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;
    $result = runScraper($limit);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
