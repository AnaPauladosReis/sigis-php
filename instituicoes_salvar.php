<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('instituicoes.php');
}

$nome = trim($_POST['nome']);
$cnpj = trim($_POST['cnpj']);
$bairro = trim($_POST['bairro']);
$responsavel = trim($_POST['responsavel']);
$tipo = trim($_POST['tipo']);
$acolhimento = !empty($_POST['acolhimento']) ? 1 : 0;

if ($nome === '' || $cnpj === '') {
    flash_set('Informe ao menos nome e CNPJ.', 'danger');
    redirect('instituicoes.php');
}

$stmt = $pdo->prepare('INSERT INTO instituicoes (nome, cnpj, bairro, responsavel, tipo, acolhimento) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute(array($nome, $cnpj, $bairro, $responsavel, $tipo, $acolhimento));

flash_set('Instituição "' . $nome . '" cadastrada com sucesso.');
redirect('instituicoes.php');
