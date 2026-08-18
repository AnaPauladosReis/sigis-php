<?php
/**
 * Configuracao de conexao com o banco de dados MySQL.
 * Ajuste as credenciais abaixo conforme o seu ambiente (XAMPP/WAMP/Laragon/servidor).
 */
$DB_HOST = 'localhost';
$DB_NAME = 'sigis';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ));
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados. Verifique config/db.php e se o banco "sigis" foi importado (database/schema.sql). Detalhe: ' . htmlspecialchars($e->getMessage()));
}
