<?php
/**
 * API Pokémon Individual: Retorna os detalhes completos de um Pokémon.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/bd.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$nationalId = isset($_GET['national_id']) ? (int) $_GET['national_id'] : null;

if (!$id && !$slug && !$nationalId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro de identificação do Pokémon não informado (id, slug ou national_id).'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = getDbConnection();

if ($pdo !== null) {
    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `id` = :id LIMIT 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        } elseif ($nationalId) {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `national_id` = :national_id LIMIT 1");
            $stmt->bindValue(':national_id', $nationalId, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM `pokemons` WHERE `slug` = :slug LIMIT 1");
            $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        }
        $stmt->execute();
        $pokemon = $stmt->fetch();

        if ($pokemon) {
            echo json_encode(['success' => true, 'pokemon' => $pokemon], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar pokemon: " . $e->getMessage());
    }
}

// Fallback JSON
$jsonPath = __DIR__ . '/../database/pokemons_seed.json';
if (file_exists($jsonPath)) {
    $all = json_decode(file_get_contents($jsonPath), true);
    foreach ($all as $p) {
        if (($id && $p['id'] == $id) || 
            ($nationalId && $p['national_id'] == $nationalId) || 
            ($slug && $p['slug'] === $slug)) {
            echo json_encode(['success' => true, 'pokemon' => $p], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Pokémon não encontrado.'], JSON_UNESCAPED_UNICODE);
