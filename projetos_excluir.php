<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projetos.php');
}
csrf_check();

$id = (int)$_POST['id'];

try {
    $stmt = $pdo->prepare('DELETE FROM projetos WHERE id = ?');
    $stmt->execute(array($id));
    flash_set('Projeto excluído com sucesso.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_set('Não é possível excluir: há itens, entradas, produções ou doações vinculadas a este projeto.', 'danger');
    } else {
        throw $e;
    }
}

redirect('projetos.php');
