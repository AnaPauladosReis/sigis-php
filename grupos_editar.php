<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('grupos.php');
}
csrf_check();

$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$cor = trim($_POST['cor']);
$ativo = !empty($_POST['ativo']) ? 1 : 0;

if ($nome === '') {
    flash_set('Informe o nome do grupo.', 'danger');
    redirect('grupos.php');
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
    $cor = '#2B5FAD';
}

if ($ativo === 0) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM itens WHERE grupo_id = ? AND ativo = 1');
    $stmt->execute(array($id));
    if ((int)$stmt->fetchColumn() > 0) {
        flash_set('Não é possível inativar: há itens ativos neste grupo. Inative os itens primeiro.', 'danger');
        redirect('grupos.php');
    }
}

$stmt = $pdo->prepare('UPDATE grupos_impacto SET nome = ?, cor = ?, ativo = ? WHERE id = ?');
$stmt->execute(array($nome, $cor, $ativo, $id));

flash_set('Grupo "' . $nome . '" atualizado com sucesso.');
redirect('grupos.php');
