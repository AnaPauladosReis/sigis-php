<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute(array($_SESSION['user_id']));
$usuario = $stmt->fetch();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="fw-bold mb-1" style="font-size:1.35rem">Meu Perfil</div>
<div class="text-muted mb-4">Seus dados de acesso ao SIGIS</div>

<?php if (!empty($_SESSION['user_trocar_senha'])) { ?>
  <div class="alert alert-warning small">Por segurança, defina uma nova senha para continuar usando o sistema.</div>
<?php } ?>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <div class="fw-semibold mb-3" style="font-size:.9rem">Dados pessoais</div>
      <form method="post" action="perfil_salvar.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="acao" value="dados">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome</label>
          <input type="text" name="nome" class="form-control" value="<?php echo e($usuario['nome']); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">E-mail</label>
          <input type="email" class="form-control" value="<?php echo e($usuario['email']); ?>" disabled>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Perfil</label>
          <input type="text" class="form-control" value="<?php echo e($usuario['perfil']); ?>" disabled>
        </div>
        <button type="submit" class="btn btn-sigis">Salvar nome</button>
      </form>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <div class="fw-semibold mb-3" style="font-size:.9rem">Alterar senha</div>
      <form method="post" action="perfil_salvar.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="acao" value="senha">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Senha atual</label>
          <input type="password" name="senha_atual" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nova senha</label>
          <input type="password" name="nova_senha" class="form-control" minlength="8" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Confirmar nova senha</label>
          <input type="password" name="confirmar_senha" class="form-control" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-sigis">Alterar senha</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
