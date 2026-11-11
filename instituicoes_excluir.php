<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('instituicoes.php');
}

$id = (int)$_POST['id'];

try {
    $stmt = $pdo->prepare('DELETE FROM instituicoes WHERE id = ?');
    $stmt->execute(array($id));
    flash_set('Instituição excluída com sucesso.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_set('Não é possível excluir: há doações ou evidências vinculadas a esta instituição.', 'danger');
    } else {
        throw $e;
    }
}

redirect('instituicoes.php');
