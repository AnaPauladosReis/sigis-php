<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$categorias = array('Indústria', 'Multa social', 'Biblioteca', 'Campanha', 'Gincana', 'Troca solidária', 'Doação espontânea', 'Outra');
$origens = $pdo->query('SELECT * FROM origens ORDER BY nome')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Origens das Entradas</div>
    <div class="text-muted">De onde os materiais recebidos vêm</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalOrigem">+ Nova Origem</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Nome</th><th>Categoria</th><th>Status</th><th class="pe-3 text-end">Ações</th></tr></thead>
      <tbody>
        <?php foreach ($origens as $o) { ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($o['nome']); ?></td>
            <td><?php echo e($o['categoria']); ?></td>
            <td class="fw-semibold" style="color:<?php echo $o['ativo'] ? '#2E8B57' : '#98A2B3'; ?>"><?php echo $o['ativo'] ? 'Ativa' : 'Inativa'; ?></td>
            <td class="pe-3 text-end">
              <button type="button" class="btn btn-sm btn-icon-edit" title="Editar origem" data-bs-toggle="modal" data-bs-target="#modalEditOrigem<?php echo $o['id']; ?>">
                <i class="bi bi-pencil"></i>
              </button>
            </td>
          </tr>
        <?php } ?>
        <?php if (!count($origens)) { ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma origem cadastrada.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalOrigem" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="origens_salvar.php">
      <?php csrf_field(); ?>
      <div class="modal-header">
        <h5 class="modal-title">Nova Origem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome</label>
          <input type="text" name="nome" class="form-control" placeholder="Ex: Doação Fábrica X" required>
        </div>
        <div class="mb-1">
          <label class="form-label small fw-semibold">Categoria</label>
          <select name="categoria" class="form-select" required>
            <?php foreach ($categorias as $c) { ?>
              <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Salvar</button>
      </div>
    </form>
  </div>
</div>

<?php foreach ($origens as $o) { ?>
  <div class="modal fade" id="modalEditOrigem<?php echo $o['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="origens_editar.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $o['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Editar Origem</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?php echo e($o['nome']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Categoria</label>
            <select name="categoria" class="form-select" required>
              <?php foreach ($categorias as $c) { ?>
                <option value="<?php echo e($c); ?>" <?php echo $o['categoria'] === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="origemAtivaCk<?php echo $o['id']; ?>" name="ativo" value="1" <?php echo $o['ativo'] ? 'checked' : ''; ?>>
            <label class="form-check-label small" for="origemAtivaCk<?php echo $o['id']; ?>">Origem ativa</label>
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
