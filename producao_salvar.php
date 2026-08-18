<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('producao.php');
}

$data = $_POST['data_producao'];
$projetoId = !empty($_POST['projeto_id']) ? (int)$_POST['projeto_id'] : null;
$itemId = (int)$_POST['item_consumido_id'];
$qtdConsumida = (float)$_POST['qtd_consumida'];
$produto = trim($_POST['produto_gerado']);
$qtdGerada = (float)$_POST['quantidade_gerada'];
$unidadeGerada = trim($_POST['unidade_gerada']);
$residuo = ($_POST['residuo_kg'] !== '') ? (float)$_POST['residuo_kg'] : 0;

$stmt = $pdo->prepare('SELECT saldo FROM estoque WHERE item_id = ?');
$stmt->execute(array($itemId));
$saldoAtual = (float)$stmt->fetchColumn();

if ($qtdConsumida > $saldoAtual) {
    flash_set('Saldo insuficiente em estoque para essa produção (disponível: ' . fmt_num($saldoAtual, 2) . ' kg).', 'danger');
    redirect('producao.php');
}

$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO producoes (data_producao, projeto_id, item_consumido_id, qtd_consumida, produto_gerado, quantidade_gerada, unidade_gerada, residuo_kg, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute(array($data, $projetoId, $itemId, $qtdConsumida, $produto, $qtdGerada, $unidadeGerada, $residuo, $_SESSION['user_id']));

$stmt = $pdo->prepare('UPDATE estoque SET saldo = saldo - ? WHERE item_id = ?');
$stmt->execute(array($qtdConsumida, $itemId));
$pdo->commit();

flash_set('Produção registrada e estoque atualizado.');
redirect('producao.php');
