<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('instituicoes.php');
}
csrf_check();

$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$razao_social = trim($_POST['razao_social']);
$cnpj = trim($_POST['cnpj']);
$bairro = trim($_POST['bairro']);
$responsavel = trim($_POST['responsavel']);
$tipo = trim($_POST['tipo']);
$acolhimento = !empty($_POST['acolhimento']) ? 1 : 0;
$ativo = !empty($_POST['ativo']) ? 1 : 0;

if ($nome === '' || $cnpj === '') {
    flash_set('Informe ao menos nome e CNPJ.', 'danger');
    redirect('instituicoes.php');
}
if (!validar_cnpj($cnpj)) {
    flash_set('CNPJ inválido. Confira o número informado.', 'danger');
    redirect('instituicoes.php');
}

try {
    $stmt = $pdo->prepare('UPDATE instituicoes SET nome = ?, razao_social = ?, cnpj = ?, bairro = ?, responsavel = ?, tipo = ?, acolhimento = ?, ativo = ? WHERE id = ?');
    $stmt->execute(array($nome, $razao_social, $cnpj, $bairro, $responsavel, $tipo, $acolhimento, $ativo, $id));
    flash_set('Instituição "' . $nome . '" atualizada com sucesso.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_set('Já existe uma instituição cadastrada com esse CNPJ.', 'danger');
    } else {
        throw $e;
    }
}

redirect('instituicoes.php');
