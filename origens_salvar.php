<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('origens.php');
}
csrf_check();

$categoriasValidas = array('Indústria', 'Multa social', 'Biblioteca', 'Campanha', 'Gincana', 'Troca solidária', 'Doação espontânea', 'Outra');
$nome = trim($_POST['nome']);
$categoria = trim($_POST['categoria']);

if ($nome === '') {
    flash_set('Informe o nome da origem.', 'danger');
    redirect('origens.php');
}
if (!in_array($categoria, $categoriasValidas, true)) {
    flash_set('Categoria inválida.', 'danger');
    redirect('origens.php');
}

$stmt = $pdo->prepare('INSERT INTO origens (nome, categoria) VALUES (?, ?)');
$stmt->execute(array($nome, $categoria));

flash_set('Origem "' . $nome . '" cadastrada com sucesso.');
redirect('origens.php');
