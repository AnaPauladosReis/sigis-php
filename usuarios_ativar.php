<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}
csrf_check();

$id = (int)$_POST['id'];
$ativo = (int)$_POST['ativo'] ? 1 : 0;

$stmt = $pdo->prepare('SELECT perfil, ativo FROM usuarios WHERE id = ?');
$stmt->execute(array($id));
$usuario = $stmt->fetch();

if (!$usuario) {
    flash_set('Usuário não encontrado.', 'danger');
    redirect('usuarios.php');
}

if ($ativo === 0 && $usuario['perfil'] === 'Administrador') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = 'Administrador' AND ativo = 1 AND id != ?");
    $stmt->execute(array($id));
    if ((int)$stmt->fetchColumn() === 0) {
        flash_set('Não é possível desativar o último administrador ativo.', 'danger');
        redirect('usuarios.php');
    }
}

$stmt = $pdo->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?');
$stmt->execute(array($ativo, $id));

flash_set($ativo ? 'Usuário ativado.' : 'Usuário desativado.');
redirect('usuarios.php');
