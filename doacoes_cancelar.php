<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('doacoes_historico.php');
}
csrf_check();

$id = (int)$_POST['id'];

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT item_id, quantidade FROM doacao_itens WHERE doacao_id = ?');
    $stmt->execute(array($id));
    $itens = $stmt->fetchAll();

    if (!count($itens)) {
        throw new Exception('Doação não encontrada.');
    }

    foreach ($itens as $it) {
        estoque_movimentar($pdo, (int)$it['item_id'], (float)$it['quantidade'], 'ajuste', 'doacao_cancelada', $id);
    }

    $pdo->prepare('DELETE FROM doacao_itens WHERE doacao_id = ?')->execute(array($id));
    $pdo->prepare('DELETE FROM termos WHERE doacao_id = ?')->execute(array($id));
    $pdo->prepare('DELETE FROM doacoes WHERE id = ?')->execute(array($id));

    $pdo->commit();
    flash_set('Doação cancelada. Os itens foram devolvidos ao estoque.');
} catch (Exception $ex) {
    $pdo->rollBack();
    error_log($ex->getMessage());
    flash_set('Não foi possível cancelar a doação. Tente novamente.', 'danger');
}

redirect('doacoes_historico.php');
