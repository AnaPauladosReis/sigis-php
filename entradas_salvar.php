<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('entradas.php');
}
csrf_check();

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
try {
    $stmt = $pdo->prepare('INSERT INTO entradas (data_entrada, origem, projeto_id, item_id, quantidade, peso, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($data, $origem, $projetoId, $itemId, $quantidade, $peso, $_SESSION['user_id']));

    $entradaId = (int)$pdo->lastInsertId();
    estoque_movimentar($pdo, $itemId, $quantidade, 'entrada', 'entrada', $entradaId);

    $pdo->commit();
    flash_set('Entrada registrada e estoque atualizado.');
} catch (Exception $ex) {
    $pdo->rollBack();
    error_log($ex->getMessage());
    flash_set('Não foi possível registrar a entrada. Tente novamente.', 'danger');
}

redirect('entradas.php');
