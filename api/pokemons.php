<?php
/**
 * API Pokémons: Busca dinâmica, listagem, filtros e sugestões em tempo real.
 * Usa PDO Prepared Statements para 100% de proteção contra SQL Injection.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/bd.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$isSuggestion = isset($_GET['suggest']) && $_GET['suggest'] == '1';
$page = max(1, isset($_GET['page']) ? (int) $_GET['page'] : 1);
$limit = isset($_GET['limit']) ? min(100, max(1, (int) $_GET['limit'])) : ($isSuggestion ? 8 : 24);
$offset = ($page - 1) * $limit;

$pdo = getDbConnection();

// Se o MySQL estiver disponível via PDO
if ($pdo !== null) {
    try {
        $conditions = ["(`is_secret` = 0 OR :include_secret = 1)"];
        $params = [
            ':include_secret' => ($search === 'comunismo' || $search === 'fantasma' || $search === '9999') ? 1 : 0
        ];

        // Busca dinâmica por nome ou ID
        if (!empty($search)) {
            $conditions[] = "(`name` LIKE :search_name OR `national_id` = :search_id OR `slug` LIKE :search_slug)";
            $params[':search_name'] = "%{$search}%";
            $params[':search_slug'] = "%" . strtolower($search) . "%";
            $params[':search_id'] = is_numeric($search) ? (int) $search : -1;
        }

        // Filtro por tipo elemental
        if (!empty($type) && $type !== 'all') {
            $conditions[] = "(`type1` = :type1 OR `type2` = :type2)";
            $params[':type1'] = $type;
            $params[':type2'] = $type;
        }

        $whereClause = implode(' AND ', $conditions);

        // Contagem total para paginação
        $countSql = "SELECT COUNT(*) FROM `pokemons` WHERE $whereClause";
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalItems = (int) $countStmt->fetchColumn();

        // Consulta de dados com Prepared Statement
        $selectFields = $isSuggestion 
            ? "`id`, `national_id`, `name`, `type1`, `type2`, `image_url`"
            : "*";

        $sql = "SELECT $selectFields FROM `pokemons` WHERE $whereClause ORDER BY `national_id` ASC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $pokemons = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'source' => 'mysql_pdo',
            'page' => $page,
            'limit' => $limit,
            'total' => $totalItems,
            'total_pages' => ceil($totalItems / $limit),
            'pokemons' => $pokemons
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (PDOException $e) {
        error_log("Erro na query de pokémons: " . $e->getMessage());
        // Em caso de falha transitória na query, cai no fallback JSON
    }
}

// Fallback elegante via arquivo JSON local caso o MySQL ainda não esteja iniciado
$jsonPath = __DIR__ . '/../database/pokemons_seed.json';
$allPokemons = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

$filtered = array_filter($allPokemons, function ($p) use ($search, $type) {
    if (!empty($p['is_secret']) && $p['is_secret'] == 1) {
        if ($search !== 'comunismo' && $search !== 'fantasma' && $search !== '9999') {
            return false;
        }
    }

    if (!empty($search)) {
        $term = mb_strtolower($search);
        $nameMatch = mb_stripos($p['name'], $term) !== false;
        $idMatch = (string) $p['national_id'] === $term;
        if (!$nameMatch && !$idMatch) {
            return false;
        }
    }

    if (!empty($type) && $type !== 'all') {
        $typeMatch = (strcasecmp($p['type1'], $type) === 0) || (!empty($p['type2']) && strcasecmp($p['type2'], $type) === 0);
        if (!$typeMatch) {
            return false;
        }
    }

    return true;
});

$totalItems = count($filtered);
$slice = array_slice(array_values($filtered), $offset, $limit);

echo json_encode([
    'success' => true,
    'source' => 'local_fallback',
    'notice' => 'Dados carregados do cache local (MySQL não conectado). Configure o MySQL no config/bd.php.',
    'page' => $page,
    'limit' => $limit,
    'total' => $totalItems,
    'total_pages' => ceil($totalItems / $limit),
    'pokemons' => $slice
], JSON_UNESCAPED_UNICODE);
