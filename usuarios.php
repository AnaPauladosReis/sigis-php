<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

$perfis = array('Colaborador', 'Administrador', 'Gestor');
$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Usuários</div>
    <div class="text-muted">Contas com acesso ao sistema</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Novo Usuário</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Nome</th><th>E-mail</th><th>Perfil</th><th>Status</th><th class="pe-3 text-end">Ações</th></tr></thead>
      <tbody>
        <?php foreach ($usuarios as $u) { ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($u['nome']); ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><?php echo e($u['perfil']); ?></td>
            <td class="fw-semibold" style="color:<?php echo $u['ativo'] ? '#2E8B57' : '#98A2B3'; ?>"><?php echo $u['ativo'] ? 'Ativo' : 'Inativo'; ?></td>
            <td class="pe-3 text-end">
              <div class="d-flex gap-1 justify-content-end flex-nowrap">
                <button type="button" class="btn btn-sm btn-icon-edit" title="Editar usuário" data-bs-toggle="modal" data-bs-target="#modalEditUsuario<?php echo $u['id']; ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="post" action="usuarios_redefinir_senha.php" class="d-inline" onsubmit="return confirm('Gerar uma nova senha temporária para \'<?php echo e($u['nome']); ?>\'?');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-icon-edit" title="Redefinir senha">
                    <i class="bi bi-key"></i>
                  </button>
                </form>
                <form method="post" action="usuarios_ativar.php" class="d-inline" onsubmit="return confirm('<?php echo $u['ativo'] ? 'Desativar' : 'Ativar'; ?> o usuário \'<?php echo e($u['nome']); ?>\'?');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                  <input type="hidden" name="ativo" value="<?php echo $u['ativo'] ? '0' : '1'; ?>">
                  <button type="submit" class="btn btn-sm btn-icon-delete" title="<?php echo $u['ativo'] ? 'Desativar' : 'Ativar'; ?> usuário">
                    <i class="bi bi-<?php echo $u['ativo'] ? 'person-x' : 'person-check'; ?>"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php } ?>
        <?php if (!count($usuarios)) { ?>
          <tr><td colspan="5" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="usuarios_salvar.php">
      <?php csrf_field(); ?>
      <div class="modal-header">
        <h5 class="modal-title">Novo Usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">E-mail</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Perfil</label>
          <select name="perfil" class="form-select" required>
            <?php foreach ($perfis as $p) { ?>
              <option value="<?php echo e($p); ?>"><?php echo e($p); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="text-muted small">Uma senha temporária é gerada automaticamente; o usuário precisará trocá-la no primeiro acesso.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Salvar</button>
      </div>
    </form>
  </div>
</div>

<?php foreach ($usuarios as $u) { ?>
  <div class="modal fade" id="modalEditUsuario<?php echo $u['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="usuarios_editar.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Editar Usuário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?php echo e($u['nome']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">E-mail</label>
            <input type="email" name="email" class="form-control" value="<?php echo e($u['email']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Perfil</label>
            <select name="perfil" class="form-select" required>
              <?php foreach ($perfis as $p) { ?>
                <option value="<?php echo e($p); ?>" <?php echo $u['perfil'] === $p ? 'selected' : ''; ?>><?php echo e($p); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sigis">Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
