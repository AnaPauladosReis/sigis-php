<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('producao.php');
}
csrf_check();

$data = $_POST['data_producao'];
$projetoId = !empty($_POST['projeto_id']) ? (int)$_POST['projeto_id'] : null;
$itemId = (int)$_POST['item_consumido_id'];
$qtdConsumida = (float)$_POST['qtd_consumida'];
$produto = trim($_POST['produto_gerado']);
$qtdGerada = (float)$_POST['quantidade_gerada'];
$unidadeGerada = trim($_POST['unidade_gerada']);
$residuo = ($_POST['residuo_kg'] !== '') ? (float)$_POST['residuo_kg'] : 0;

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO producoes (data_producao, projeto_id, item_consumido_id, qtd_consumida, produto_gerado, quantidade_gerada, unidade_gerada, residuo_kg, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($data, $projetoId, $itemId, $qtdConsumida, $produto, $qtdGerada, $unidadeGerada, $residuo, $_SESSION['user_id']));
    $producaoId = (int)$pdo->lastInsertId();

    estoque_movimentar($pdo, $itemId, -$qtdConsumida, 'producao_consumo', 'producao', $producaoId);

    $pdo->commit();
    flash_set('Produção registrada e estoque atualizado.');
} catch (RuntimeException $ex) {
    $pdo->rollBack();
    flash_set('Saldo insuficiente em estoque para essa produção.', 'danger');
} catch (Exception $ex) {
    $pdo->rollBack();
    error_log($ex->getMessage());
    flash_set('Não foi possível registrar a produção. Tente novamente.', 'danger');
}

redirect('producao.php');
