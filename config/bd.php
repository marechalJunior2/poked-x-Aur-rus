<?php
/**
 * Configuração e Conexão com o Banco de Dados MySQL via PDO.
 * Arquitetura segura com Prepared Statements contra SQL Injection.
 */

// Inicia sessão nativa se ainda não iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do Banco de Dados
// Você pode ajustar as credenciais abaixo de acordo com seu ambiente local (XAMPP, Laragon, Docker, etc.)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'pokedex_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtém a instância de conexão PDO com o MySQL
 * 
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): ?PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Registra o erro internamente sem expor credenciais sensíveis
        error_log("Erro de Conexão PDO: " . $e->getMessage());
        return null;
    }
}

/**
 * Retorna se o banco de dados está online e funcional
 * 
 * @return bool
 */
function isDbConnected(): bool {
    return getDbConnection() !== null;
}
