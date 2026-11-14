<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('usuarios.php');
}
csrf_check();

$nome = trim($_POST['nome']);
$email = trim($_POST['email']);
$perfil = trim($_POST['perfil']);
$perfisValidos = array('Colaborador', 'Administrador', 'Gestor');

if ($nome === '' || $email === '' || !in_array($perfil, $perfisValidos, true)) {
    flash_set('Preencha nome, e-mail e um perfil válido.', 'danger');
    redirect('usuarios.php');
}

$senhaTemporaria = gerar_senha_temporaria();
$hash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, trocar_senha) VALUES (?, ?, ?, ?, 1, 1)');
    $stmt->execute(array($nome, $email, $hash, $perfil));
    flash_set('Usuário "' . $nome . '" criado. Senha temporária: ' . $senhaTemporaria . ' (repasse com segurança; será exigida a troca no primeiro acesso).');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        flash_set('Já existe um usuário cadastrado com esse e-mail.', 'danger');
    } else {
        throw $e;
    }
}

redirect('usuarios.php');
