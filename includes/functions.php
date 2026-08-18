<?php
/**
 * Funcoes utilitarias compartilhadas por todo o sistema.
 */

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function current_user() {
    return array(
        'id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null,
        'nome' => isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : '',
        'perfil' => isset($_SESSION['user_perfil']) ? $_SESSION['user_perfil'] : '',
    );
}

function fmt_num($n, $decimals = 0) {
    return number_format((float)$n, $decimals, ',', '.');
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function flash_set($msg, $type = 'success') {
    $_SESSION['flash'] = array('msg' => $msg, 'type' => $type);
}

function flash_get() {
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/** Nome de exibicao da tela atual, usado para destacar o item ativo no menu. */
function active_screen() {
    $file = basename($_SERVER['PHP_SELF'], '.php');
    return $file;
}
