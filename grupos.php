<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$grupos = $pdo->query('SELECT * FROM grupos_impacto ORDER BY nome')->fetchAll();
$stmtItens = $pdo->prepare('SELECT * FROM itens WHERE grupo_id = ? ORDER BY nome');
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
        <div class="text-muted small">· <?php echo count($g['itens']); ?> itens cadastrados</div>
      </div>
      <a href="#" class="small fw-semibold text-decoration-none" style="color:var(--blue)" data-bs-toggle="modal" data-bs-target="#modalItem<?php echo $g['id']; ?>">+ Novo item</a>
    </div>
    <div class="table-responsive">
      <table class="table table-sigis mb-0">
        <thead><tr><th class="ps-3">Item</th><th>Unidade</th><th>Peso unitário</th><th class="pe-3">Multiplicador de vidas</th></tr></thead>
        <tbody>
          <?php foreach ($g['itens'] as $it) { ?>
            <tr>
              <td class="ps-3 fw-medium text-dark"><?php echo e($it['nome']); ?></td>
              <td><?php echo e($it['unidade']); ?></td>
              <td><?php echo fmt_num($it['peso_unitario'], 3); ?> kg</td>
              <td class="pe-3 fw-bold" style="color:var(--blue)"><?php echo fmt_num($it['multiplicador'], 3); ?></td>
            </tr>
          <?php } ?>
          <?php if (!count($g['itens'])) { ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Nenhum item cadastrado neste grupo.</td></tr>
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
          <div class="mb-1">
            <label class="form-label small fw-semibold">Multiplicador de vidas</label>
            <input type="number" step="0.001" min="0" name="multiplicador" class="form-control" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sigis">Salvar item</button>
        </div>
      </form>
    </div>
  </div>
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
