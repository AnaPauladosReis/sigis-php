<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$grupos = $pdo->query('SELECT * FROM grupos_impacto ORDER BY nome')->fetchAll();
$projetos = $pdo->query('SELECT id, nome FROM projetos ORDER BY nome')->fetchAll();
$stmtItens = $pdo->prepare('SELECT i.*, p.nome AS projeto_nome FROM itens i LEFT JOIN projetos p ON p.id = i.projeto_id WHERE i.grupo_id = ? ORDER BY i.nome');
foreach ($grupos as &$g) {
    $stmtItens->execute(array($g['id']));
    $g['itens'] = $stmtItens->fetchAll();
}
unset($g);

require __DIR__ . '/includes/layout_top.php';
?>
<div class="fw-bold mb-1" style="font-size:1.35rem">Grupos de Impacto &amp; Itens</div>
<div class="text-muted mb-4">Categorização dos materiais e multiplicadores usados no cálculo de vidas impactadas</div>

<?php foreach ($grupos as $g) { ?>
  <div class="card mb-3">
    <div class="grupo-header d-flex justify-content-between align-items-center px-3 py-2">
      <div class="d-flex align-items-center gap-2">
        <div style="width:8px;height:8px;border-radius:2px;background:<?php echo e($g['cor']); ?>"></div>
        <div class="fw-bold" style="font-size:.95rem"><?php echo e($g['nome']); ?></div>
        <div class="text-muted small">· <?php echo count($g['itens']); ?> <?php echo count($g['itens']) == 1 ? 'item cadastrado' : 'itens cadastrados'; ?></div>
      </div>
      <a href="#" class="small fw-semibold text-decoration-none" style="color:var(--blue)" data-bs-toggle="modal" data-bs-target="#modalItem<?php echo $g['id']; ?>">+ Novo item</a>
    </div>
    <div class="table-responsive">
      <table class="table table-sigis table-itens mb-0">
        <colgroup>
          <col style="width:34%">
          <col style="width:20%">
          <col style="width:10%">
          <col style="width:13%">
          <col style="width:15%">
          <col style="width:8%">
        </colgroup>
        <thead><tr><th class="ps-3">Item</th><th>Projeto</th><th>Unidade</th><th>Peso unitário</th><th>Multiplicador de vidas</th><th class="pe-3 text-end">Ações</th></tr></thead>
        <tbody>
          <?php foreach ($g['itens'] as $it) { ?>
            <tr>
              <td class="ps-3 fw-medium text-dark"><?php echo e($it['nome']); ?></td>
              <td class="text-muted"><?php echo e($it['projeto_nome']); ?></td>
              <td><?php echo e($it['unidade']); ?></td>
              <td><?php echo fmt_num_min($it['peso_unitario']); ?> kg</td>
              <td class="fw-bold" style="color:var(--blue)"><?php echo fmt_num_min($it['multiplicador']); ?></td>
              <td class="pe-3 text-end">
                <button type="button" class="btn btn-sm btn-icon-edit" title="Editar item" data-bs-toggle="modal" data-bs-target="#modalEditItem<?php echo $it['id']; ?>">
                  <i class="bi bi-pencil"></i>
                </button>
              </td>
            </tr>
          <?php } ?>
          <?php if (!count($g['itens'])) { ?>
            <tr><td colspan="6" class="text-center text-muted py-3">Nenhum item cadastrado neste grupo.</td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="modalItem<?php echo $g['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="grupos_item_salvar.php">
        <input type="hidden" name="grupo_id" value="<?php echo $g['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Novo item · <?php echo e($g['nome']); ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nome do item</label>
            <input type="text" name="nome" class="form-control" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label small fw-semibold">Unidade</label>
              <input type="text" name="unidade" class="form-control" placeholder="kg, un, peça..." required>
            </div>
            <div class="col">
              <label class="form-label small fw-semibold">Peso unitário (kg)</label>
              <input type="number" step="0.001" min="0" name="peso_unitario" class="form-control" value="1" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Multiplicador de vidas</label>
            <input type="number" step="0.1" min="0" name="multiplicador" class="form-control" value="1" required>
          </div>
          <div class="mb-1">
            <label class="form-label small fw-semibold">Projeto / frente de doação</label>
            <select name="projeto_id" class="form-select">
              <option value="">—</option>
              <?php foreach ($projetos as $p) { ?>
                <option value="<?php echo $p['id']; ?>"><?php echo e($p['nome']); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sigis">Salvar item</button>
        </div>
      </form>
    </div>
  </div>

  <?php foreach ($g['itens'] as $it) { ?>
    <div class="modal fade" id="modalEditItem<?php echo $it['id']; ?>" tabindex="-1">
      <div class="modal-dialog">
        <form class="modal-content" method="post" action="grupos_item_editar.php">
          <input type="hidden" name="id" value="<?php echo $it['id']; ?>">
          <div class="modal-header">
            <h5 class="modal-title">Editar item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nome do item</label>
              <input type="text" name="nome" class="form-control" value="<?php echo e($it['nome']); ?>" required>
            </div>
            <div class="row g-2 mb-3">
              <div class="col">
                <label class="form-label small fw-semibold">Unidade</label>
                <input type="text" name="unidade" class="form-control" value="<?php echo e($it['unidade']); ?>" required>
              </div>
              <div class="col">
                <label class="form-label small fw-semibold">Peso unitário (kg)</label>
                <input type="number" step="0.001" min="0" name="peso_unitario" class="form-control" value="<?php echo e(fmt_num_input($it['peso_unitario'])); ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Multiplicador de vidas</label>
              <input type="number" step="0.1" min="0" name="multiplicador" class="form-control" value="<?php echo e(fmt_num_input($it['multiplicador'])); ?>" required>
            </div>
            <div class="row g-2 mb-1">
              <div class="col">
                <label class="form-label small fw-semibold">Grupo de impacto</label>
                <select name="grupo_id" class="form-select">
                  <?php foreach ($grupos as $gOpt) { ?>
                    <option value="<?php echo $gOpt['id']; ?>" <?php echo $gOpt['id'] == $it['grupo_id'] ? 'selected' : ''; ?>><?php echo e($gOpt['nome']); ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="col">
                <label class="form-label small fw-semibold">Projeto / frente de doação</label>
                <select name="projeto_id" class="form-select">
                  <option value="">—</option>
                  <?php foreach ($projetos as $p) { ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $it['projeto_id'] ? 'selected' : ''; ?>><?php echo e($p['nome']); ?></option>
                  <?php } ?>
                </select>
              </div>
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
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
