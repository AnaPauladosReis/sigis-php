<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projetos.php');
}
csrf_check();

$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$descricao = trim($_POST['descricao']);
$grupoId = !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null;
$status = trim($_POST['status']);

if ($nome === '') {
    flash_set('Informe o nome do projeto.', 'danger');
    redirect('projetos.php');
}
if (!in_array($status, array('Ativo', 'Inativo', 'Encerrado'), true)) {
    flash_set('Status inválido.', 'danger');
    redirect('projetos.php');
}
if ($grupoId !== null && !existe_id($pdo, 'grupos_impacto', $grupoId)) {
    flash_set('Grupo de impacto inválido.', 'danger');
    redirect('projetos.php');
}

$stmt = $pdo->prepare('UPDATE projetos SET nome = ?, descricao = ?, grupo_id = ?, status = ? WHERE id = ?');
$stmt->execute(array($nome, $descricao !== '' ? $descricao : 'Sem descrição.', $grupoId, $status, $id));

flash_set('Projeto "' . $nome . '" atualizado com sucesso.');
redirect('projetos.php');
