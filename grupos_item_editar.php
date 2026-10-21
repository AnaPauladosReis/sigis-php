<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('grupos.php');
}

$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$unidade = trim($_POST['unidade']);
$peso = (float)$_POST['peso_unitario'];
$mult = (float)$_POST['multiplicador'];
$grupoId = (int)$_POST['grupo_id'];
$projetoId = !empty($_POST['projeto_id']) ? (int)$_POST['projeto_id'] : null;

if ($nome === '' || $unidade === '') {
    flash_set('Preencha nome e unidade do item.', 'danger');
    redirect('grupos.php');
}

$stmt = $pdo->prepare('UPDATE itens SET nome = ?, unidade = ?, peso_unitario = ?, multiplicador = ?, grupo_id = ?, projeto_id = ? WHERE id = ?');
$stmt->execute(array($nome, $unidade, $peso, $mult, $grupoId, $projetoId, $id));

flash_set('Item "' . $nome . '" atualizado com sucesso.');
redirect('grupos.php');
