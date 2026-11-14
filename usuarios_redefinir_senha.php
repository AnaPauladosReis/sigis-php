<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}
csrf_check();

$id = (int)$_POST['id'];

$stmt = $pdo->prepare('SELECT nome FROM usuarios WHERE id = ?');
$stmt->execute(array($id));
$usuario = $stmt->fetch();

if (!$usuario) {
    flash_set('Usuário não encontrado.', 'danger');
    redirect('usuarios.php');
}

$senhaTemporaria = gerar_senha_temporaria();
$hash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ?, trocar_senha = 1 WHERE id = ?');
$stmt->execute(array($hash, $id));

flash_set('Nova senha temporária para "' . $usuario['nome'] . '": ' . $senhaTemporaria . ' (repasse com segurança; será exigida a troca no próximo acesso).');
redirect('usuarios.php');
