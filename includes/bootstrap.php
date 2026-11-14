<?php
set_exception_handler(function ($ex) {
    error_log($ex->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
    }
    require __DIR__ . '/erro.php';
    exit;
});

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(array(
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ));
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/estoque.php';

$sessaoInatividadeMax = 2 * 60 * 60; // 2 horas
if (!empty($_SESSION['user_id']) && !empty($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade']) > $sessaoInatividadeMax) {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit;
}
if (!empty($_SESSION['user_id'])) {
    $_SESSION['ultima_atividade'] = time();
}
