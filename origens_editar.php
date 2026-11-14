<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('origens.php');
}
csrf_check();

$categoriasValidas = array('Indústria', 'Multa social', 'Biblioteca', 'Campanha', 'Gincana', 'Troca solidária', 'Doação espontânea', 'Outra');
$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$categoria = trim($_POST['categoria']);
$ativo = !empty($_POST['ativo']) ? 1 : 0;

if ($nome === '') {
    flash_set('Informe o nome da origem.', 'danger');
    redirect('origens.php');
}
if (!in_array($categoria, $categoriasValidas, true)) {
    flash_set('Categoria inválida.', 'danger');
    redirect('origens.php');
}

$stmt = $pdo->prepare('UPDATE origens SET nome = ?, categoria = ?, ativo = ? WHERE id = ?');
$stmt->execute(array($nome, $categoria, $ativo, $id));

flash_set('Origem "' . $nome . '" atualizada com sucesso.');
redirect('origens.php');
