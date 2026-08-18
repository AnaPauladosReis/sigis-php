<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('grupos.php');
}

$grupoId = (int)$_POST['grupo_id'];
$nome = trim($_POST['nome']);
$unidade = trim($_POST['unidade']);
$peso = (float)$_POST['peso_unitario'];
$mult = (float)$_POST['multiplicador'];

if ($nome === '' || $unidade === '') {
    flash_set('Preencha nome e unidade do item.', 'danger');
    redirect('grupos.php');
}

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO itens (grupo_id, nome, unidade, peso_unitario, multiplicador) VALUES (?, ?, ?, ?, ?)');
$stmt->execute(array($grupoId, $nome, $unidade, $peso, $mult));
$itemId = (int)$pdo->lastInsertId();

$stmt = $pdo->prepare('INSERT INTO estoque (item_id, saldo, minimo) VALUES (?, 0, 0)');
$stmt->execute(array($itemId));
$pdo->commit();

flash_set('Item "' . $nome . '" cadastrado com sucesso.');
redirect('grupos.php');
