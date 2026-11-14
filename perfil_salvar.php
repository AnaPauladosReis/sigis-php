<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('perfil.php');
}
csrf_check();

$acao = isset($_POST['acao']) ? $_POST['acao'] : '';
$userId = (int)$_SESSION['user_id'];

if ($acao === 'dados') {
    $nome = trim($_POST['nome']);
    if ($nome === '') {
        flash_set('Informe seu nome.', 'danger');
        redirect('perfil.php');
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET nome = ? WHERE id = ?');
    $stmt->execute(array($nome, $userId));
    $_SESSION['user_nome'] = $nome;

    flash_set('Nome atualizado com sucesso.');
} elseif ($acao === 'senha') {
    $senhaAtual = (string)$_POST['senha_atual'];
    $novaSenha = (string)$_POST['nova_senha'];
    $confirmarSenha = (string)$_POST['confirmar_senha'];

    $stmt = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
    $stmt->execute(array($userId));
    $hashAtual = $stmt->fetchColumn();

    if (!password_verify($senhaAtual, $hashAtual)) {
        flash_set('Senha atual incorreta.', 'danger');
        redirect('perfil.php');
    }
    if (strlen($novaSenha) < 8) {
        flash_set('A nova senha precisa ter ao menos 8 caracteres.', 'danger');
        redirect('perfil.php');
    }
    if ($novaSenha !== $confirmarSenha) {
        flash_set('A confirmação não coincide com a nova senha.', 'danger');
        redirect('perfil.php');
    }

    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ?, trocar_senha = 0 WHERE id = ?');
    $stmt->execute(array($hash, $userId));
    $_SESSION['user_trocar_senha'] = false;

    flash_set('Senha alterada com sucesso.');
}

redirect('perfil.php');
