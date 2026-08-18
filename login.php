<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = (string)$_POST['senha'];

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute(array($email));
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_perfil'] = $user['perfil'];
        redirect('dashboard.php');
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SIGIS · Acessar plataforma</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap row g-0">
  <div class="login-left col-lg-7 d-none d-lg-flex flex-column justify-content-center px-5" style="padding:64px 72px">
    <div class="blob1"></div><div class="blob2"></div>
    <div class="position-relative">
      <div class="d-flex align-items-center gap-2 mb-5">
        <div class="brand-mark"></div>
        <div class="fs-4 fw-bold">SIGIS</div>
      </div>
      <div class="text-uppercase small fw-semibold mb-2" style="color:#8FA8D6;letter-spacing:1.5px">Gerência de Responsabilidade Social</div>
      <h1 class="fw-bold mb-3" style="font-size:2.1rem;max-width:480px;line-height:1.25">Sistema Integrado de Gestão de Impacto Social, Produção Social e Doações</h1>
      <p style="color:#B8C4DA;max-width:440px">Centralize projetos sociais, controle de estoque, produção social, doações e indicadores de vidas impactadas em um único lugar.</p>
      <div class="d-flex gap-5 mt-5">
        <div><div class="fs-3 fw-bold">4</div><div class="small" style="color:#8FA8D6">Projetos ativos</div></div>
        <div><div class="fs-3 fw-bold">1.842</div><div class="small" style="color:#8FA8D6">Vidas impactadas</div></div>
        <div><div class="fs-3 fw-bold">12</div><div class="small" style="color:#8FA8D6">Instituições atendidas</div></div>
      </div>
    </div>
  </div>
  <div class="col-lg-5 d-flex align-items-center justify-content-center p-4" style="min-height:100vh">
    <div style="width:100%;max-width:380px">
      <h1 class="fw-bold mb-1" style="font-size:1.4rem">Acessar plataforma</h1>
      <p class="text-muted mb-4">Entre com suas credenciais institucionais</p>
      <?php if ($erro) { ?>
        <div class="alert alert-danger py-2 small"><?php echo e($erro); ?></div>
      <?php } ?>
      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Usuário</label>
          <input type="email" name="email" class="form-control" placeholder="admin@gersc.org.br" required value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
        </div>
        <div class="mb-2">
          <label class="form-label small fw-semibold">Senha</label>
          <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="text-end mb-3"><a href="#" class="small">Esqueci minha senha</a></div>
        <button type="submit" class="btn btn-sigis w-100 py-2">Entrar</button>
        <div class="text-center text-muted small mt-3">Perfil: Administrador · SESI GERSC</div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
