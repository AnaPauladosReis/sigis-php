<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projetos.php');
}

$nome = trim($_POST['nome']);
$descricao = trim($_POST['descricao']);
$grupoId = !empty($_POST['grupo_id']) ? (int)$_POST['grupo_id'] : null;

if ($nome === '') {
    flash_set('Informe o nome do projeto.', 'danger');
    redirect('projetos.php');
}

$stmt = $pdo->prepare('INSERT INTO projetos (nome, descricao, grupo_id, status) VALUES (?, ?, ?, ?)');
$stmt->execute(array($nome, $descricao !== '' ? $descricao : 'Sem descrição.', $grupoId, 'Ativo'));

flash_set('Projeto "' . $nome . '" cadastrado com sucesso.');
redirect('projetos.php');
