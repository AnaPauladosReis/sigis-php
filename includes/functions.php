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

/** Formata um numero usando so as casas decimais necessarias (ate $maxDecimals), sem zeros a mais: 4.000 -> "4", 1.750 -> "1,75". */
function fmt_num_min($n, $maxDecimals = 3) {
    $n = round((float)$n, $maxDecimals);
    $decimals = $maxDecimals;
    for ($d = 0; $d <= $maxDecimals; $d++) {
        if (round($n, $d) === $n) {
            $decimals = $d;
            break;
        }
    }
    return number_format($n, $decimals, ',', '.');
}

/** Mesma ideia de fmt_num_min, mas com ponto decimal (sem separador de milhar) para usar em value="" de <input type="number">. */
function fmt_num_input($n, $maxDecimals = 3) {
    $n = round((float)$n, $maxDecimals);
    $decimals = $maxDecimals;
    for ($d = 0; $d <= $maxDecimals; $d++) {
        if (round($n, $d) === $n) {
            $decimals = $d;
            break;
        }
    }
    return number_format($n, $decimals, '.', '');
}

/** Formata uma data (Y-m-d ou timestamp) por extenso em pt-BR, ex.: "28 de janeiro de 2026". */
function fmt_data_extenso($data) {
    $meses = array(
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    );
    $ts = is_numeric($data) ? (int)$data : strtotime($data);
    return date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
}

/** Formata quantidade para o termo de doação: sem casas decimais quando inteiro, com unidade exceto "un". */
function fmt_qtd_termo($qtd, $unidade) {
    $qtd = (float)$qtd;
    $decimals = (floor($qtd) == $qtd) ? 0 : 2;
    $num = fmt_num($qtd, $decimals);
    return ($unidade && $unidade !== 'un') ? ($num . ' ' . $unidade) : $num;
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Acrescenta "?v=<timestamp>" a um asset estatico (ex: assets/css/style.css) para evitar cache velho do navegador apos deploys. */
function asset_url($relativePath) {
    $v = @filemtime(__DIR__ . '/../' . $relativePath);
    return $relativePath . ($v ? ('?v=' . $v) : '');
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
