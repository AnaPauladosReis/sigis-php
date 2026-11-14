<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}
csrf_check();

$id = (int)$_POST['id'];
$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$perfil = trim($_POST['perfil']);
$perfisValidos = array('Colaborador', 'Administrador', 'Gestor');

if ($nome === '' || $email === '' || !in_array($perfil, $perfisValidos, true)) {
    flash_set('Preencha nome, e-mail e um perfil válido.', 'danger');
    redirect('usuarios.php');
}

$stmt = $pdo->prepare('SELECT perfil, ativo FROM usuarios WHERE id = ?');
$stmt->execute(array($id));
$atual = $stmt->fetch();

if ($atual && $atual['perfil'] === 'Administrador' && $perfil !== 'Administrador' && $atual['ativo']) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE perfil = 'Administrador' AND ativo = 1 AND id != ?");
    $stmt->execute(array($id));
    if ((int)$stmt->fetchColumn() === 0) {
        flash_set('Não é possível rebaixar o último administrador ativo.', 'danger');
        redirect('usuarios.php');
    }
}

try {
    $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ?, perfil = ? WHERE id = ?');
    $stmt->execute(array($nome, $email, $perfil, $id));
    if ($id === (int)$_SESSION['user_id']) {
        $_SESSION['user_nome'] = $nome;
        $_SESSION['user_perfil'] = $perfil;
    }
    flash_set('Usuário "' . $nome . '" atualizado com sucesso.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_set('Já existe um usuário cadastrado com esse e-mail.', 'danger');
    } else {
        throw $e;
    }
}

redirect('usuarios.php');
