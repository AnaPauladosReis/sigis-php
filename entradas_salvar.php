<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('entradas.php');
}

$data = $_POST['data_entrada'];
$origem = trim($_POST['origem']);
$projetoId = !empty($_POST['projeto_id']) ? (int)$_POST['projeto_id'] : null;
$itemId = (int)$_POST['item_id'];
$quantidade = (float)$_POST['quantidade'];
$peso = ($_POST['peso'] !== '') ? (float)$_POST['peso'] : null;

if ($quantidade <= 0) {
    flash_set('Informe uma quantidade válida.', 'danger');
    redirect('entradas.php');
}

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO entradas (data_entrada, origem, projeto_id, item_id, quantidade, peso, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(array($data, $origem, $projetoId, $itemId, $quantidade, $peso, $_SESSION['user_id']));

// atualiza (ou cria) o saldo em estoque
$stmt = $pdo->prepare('SELECT id FROM estoque WHERE item_id = ?');
$stmt->execute(array($itemId));
$row = $stmt->fetch();
if ($row) {
    $stmt = $pdo->prepare('UPDATE estoque SET saldo = saldo + ? WHERE item_id = ?');
    $stmt->execute(array($quantidade, $itemId));
} else {
    $stmt = $pdo->prepare('INSERT INTO estoque (item_id, saldo, minimo) VALUES (?, ?, 0)');
    $stmt->execute(array($itemId, $quantidade));
}
$pdo->commit();

flash_set('Entrada registrada e estoque atualizado.');
redirect('entradas.php');
