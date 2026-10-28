<?php
/**
 * API Scan: Processa o escaneamento da câmera do Pokémon.
 * Utiliza sessões nativas do PHP ($_SESSION) e PDO MySQL para persistência.
 * Trata também a probabilidade ultrarara (0,0000001%) do Fantasma do Comunismo!
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

require_once __DIR__ . '/../config/bd.php';

// Inicializa a lista de Pokémons escaneados na sessão nativa
if (!isset($_SESSION['scanned_pokemons'])) {
    $_SESSION['scanned_pokemons'] = [];
}
if (!isset($_SESSION['scan_total_count'])) {
    $_SESSION['scan_total_count'] = 0;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$pokemonId = isset($data['pokemon_id']) ? (int) $data['pokemon_id'] : null;
$pokemonName = isset($data['pokemon_name']) ? trim($data['pokemon_name']) : null;
$forceSecret = isset($data['force_secret']) && ($data['force_secret'] === '1917' || $data['force_secret'] === true);

// Incrementa contador na sessão
$_SESSION['scan_total_count']++;

// Lógica da probabilidade ultrarara: 0,0000001% (1 em 1.000.000.000)
// rand(1, 1000000000) === 1917
$isSecretLiberation = false;
$rolledNumber = mt_rand(1, 100000000);
if ($rolledNumber === 1917 || $forceSecret) {
    $isSecretLiberation = true;
    $pokemonId = 9999;
}

$pdo = getDbConnection();
$pokemon = null;

if ($pdo !== null) {
    try {
        if ($isSecretLiberation) {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `national_id` = 9999 OR `slug` = 'fantasma-do-comunismo' LIMIT 1");
            $stmt->execute();
            $pokemon = $stmt->fetch();
        } elseif ($pokemonId) {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `id` = :id OR `national_id` = :nat_id LIMIT 1");
            $stmt->execute([':id' => $pokemonId, ':nat_id' => $pokemonId]);
            $pokemon = $stmt->fetch();
        } elseif ($pokemonName) {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `name` LIKE :name OR `slug` LIKE :slug LIMIT 1");
            $stmt->execute([':name' => "%{$pokemonName}%", ':slug' => "%" . strtolower($pokemonName) . "%"]);
            $pokemon = $stmt->fetch();
        } else {
            // Se nenhum Pokémon específico foi indicado no scan, seleciona um aleatório do catálogo
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `is_secret` = 0 ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $pokemon = $stmt->fetch();
        }

        // Se encontrou o Pokémon, salva histórico no banco
        if ($pokemon) {
            $sessionId = session_id();
            $stmtHist = $pdo->prepare("INSERT INTO `scan_history` (`pokemon_id`, `session_id`) VALUES (:poke_id, :sess_id)");
            $stmtHist->execute([
                ':poke_id' => $pokemon['id'],
                ':sess_id' => $sessionId
            ]);
        }
    } catch (PDOException $e) {
        error_log("Erro no scan PDO: " . $e->getMessage());
    }
}

// Fallback JSON se não encontrado no MySQL
if (!$pokemon) {
    $jsonPath = __DIR__ . '/../database/pokemons_seed.json';
    if (file_exists($jsonPath)) {
        $all = json_decode(file_get_contents($jsonPath), true);
        if ($isSecretLiberation) {
            foreach ($all as $p) {
                if ($p['national_id'] == 9999) {
                    $pokemon = $p;
                    break;
                }
            }
        } elseif ($pokemonId) {
            foreach ($all as $p) {
                if ($p['id'] == $pokemonId || $p['national_id'] == $pokemonId) {
                    $pokemon = $p;
                    break;
                }
            }
        } elseif ($pokemonName) {
            foreach ($all as $p) {
                if (stripos($p['name'], $pokemonName) !== false) {
                    $pokemon = $p;
                    break;
                }
            }
        }
        if (!$pokemon && !empty($all)) {
            // Seleciona um não-secreto aleatório
            $regulars = array_filter($all, fn($p) => empty($p['is_secret']));
            $pokemon = $regulars[array_rand($regulars)];
        }
    }
}

if (!$pokemon) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Nenhum espécime de Pokémon pôde ser identificado nos sensores ópticos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Salva na sessão nativa do PHP
if (!in_array($pokemon['national_id'], $_SESSION['scanned_pokemons'])) {
    $_SESSION['scanned_pokemons'][] = $pokemon['national_id'];
}

echo json_encode([
    'success' => true,
    'liberated_from_capitalism' => $isSecretLiberation,
    'session_scanned_count' => count($_SESSION['scanned_pokemons']),
    'total_scans_made' => $_SESSION['scan_total_count'],
    'pokemon' => $pokemon,
    'message' => $isSecretLiberation
        ? '⚠️ ALERTA HISTÓRICO: O FANTASMA DO COMUNISMO FOI ENCONTRADO! VOCÊ FOI LIBERTO DO CAPITALISMO!'
        : "Pokémon identificado com sucesso: {$pokemon['name']}!"
], JSON_UNESCAPED_UNICODE);
