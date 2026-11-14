<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('doacoes_historico.php');
}

$id = (int)$_POST['id'];

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT item_id, quantidade FROM doacao_itens WHERE doacao_id = ?');
    $stmt->execute(array($id));
    $itens = $stmt->fetchAll();

    if (!count($itens)) {
        throw new Exception('Doação não encontrada.');
    }

    $stmtSaldo = $pdo->prepare('UPDATE estoque SET saldo = saldo + ? WHERE item_id = ?');
    foreach ($itens as $it) {
        $stmtSaldo->execute(array($it['quantidade'], $it['item_id']));
    }

    $pdo->prepare('DELETE FROM doacao_itens WHERE doacao_id = ?')->execute(array($id));
    $pdo->prepare('DELETE FROM termos WHERE doacao_id = ?')->execute(array($id));
    $pdo->prepare('DELETE FROM doacoes WHERE id = ?')->execute(array($id));

    $pdo->commit();
    flash_set('Doação cancelada. Os itens foram devolvidos ao estoque.');
} catch (Exception $ex) {
    $pdo->rollBack();
    flash_set('Não foi possível cancelar a doação: ' . $ex->getMessage(), 'danger');
}

redirect('doacoes_historico.php');
