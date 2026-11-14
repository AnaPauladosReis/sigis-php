<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('grupos.php');
}
csrf_check();

$nome = trim($_POST['nome']);
$cor = trim($_POST['cor']);

if ($nome === '') {
    flash_set('Informe o nome do grupo.', 'danger');
    redirect('grupos.php');
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
    $cor = '#2B5FAD';
}

$stmt = $pdo->prepare('INSERT INTO grupos_impacto (nome, cor) VALUES (?, ?)');
$stmt->execute(array($nome, $cor));

flash_set('Grupo "' . $nome . '" cadastrado com sucesso.');
redirect('grupos.php');
