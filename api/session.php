<?php
/**
 * API Sessão Nativa: Retorna e gerencia o estado da sessão do usuário.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../config/bd.php';

if (!isset($_SESSION['scanned_pokemons'])) {
    $_SESSION['scanned_pokemons'] = [];
}
if (!isset($_SESSION['scan_total_count'])) {
    $_SESSION['scan_total_count'] = 0;
}

// Ação de reset
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $_SESSION['scanned_pokemons'] = [];
    $_SESSION['scan_total_count'] = 0;
    echo json_encode(['success' => true, 'message' => 'Histórico da Pokédex resetado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'session_id' => session_id(),
    'scanned_count' => count($_SESSION['scanned_pokemons']),
    'total_scans' => $_SESSION['scan_total_count'],
    'scanned_ids' => $_SESSION['scanned_pokemons'],
    'db_connected' => isDbConnected()
], JSON_UNESCAPED_UNICODE);
