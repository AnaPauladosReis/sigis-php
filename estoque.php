<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT es.*, i.nome as item_nome, i.unidade, g.nome as grupo_nome
        FROM estoque es
        JOIN itens i ON i.id = es.item_id
        LEFT JOIN grupos_impacto g ON g.id = i.grupo_id
        WHERE i.nome LIKE ?
        ORDER BY i.nome";
$stmt = $pdo->prepare($sql);
$stmt->execute(array('%' . $q . '%'));
$estoque = $stmt->fetchAll();

foreach ($estoque as &$e) {
    $minimo = (float)$e['minimo'];
    $saldo = (float)$e['saldo'];
    $base = $minimo > 0 ? $minimo * 2.5 : max($saldo, 1);
    $pct = $base > 0 ? min(100, round(($saldo / $base) * 100)) : 0;
    $baixo = $minimo > 0 && $saldo < $minimo;
    $e['pct'] = $pct;
    $e['baixo'] = $baixo;
}
unset($e);

require __DIR__ . '/includes/layout_top.php';
?>
<div class="fw-bold mb-1" style="font-size:1.35rem">Estoque de Itens</div>
<div class="text-muted mb-3">Saldos disponíveis para produção social e doação</div>

<form method="get" class="mb-3">
  <input type="text" name="q" class="form-control" style="width:320px" placeholder="Buscar item..." value="<?php echo e($q); ?>" onchange="this.form.submit()">
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Item</th><th>Grupo</th><th>Saldo</th><th>Mínimo</th><th>Nível</th><th class="pe-3">Status</th><?php if ($u['perfil'] === 'Administrador') { ?><th class="pe-3 text-end">Ações</th><?php } ?></tr></thead>
      <tbody>
        <?php foreach ($estoque as $e) {
            $statusLabel = $e['baixo'] ? 'Baixo' : 'OK';
            $statusBg = $e['baixo'] ? '#FBEDE3' : '#E8F5EC';
            $statusColor = $e['baixo'] ? '#C1662F' : '#2E8B57';
        ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($e['item_nome']); ?></td>
            <td><?php echo e($e['grupo_nome']); ?></td>
            <td><?php echo fmt_num_min($e['saldo']) . ' ' . e($e['unidade']); ?></td>
            <td class="text-muted"><?php echo $e['minimo'] > 0 ? fmt_num_min($e['minimo']) . ' ' . e($e['unidade']) : '—'; ?></td>
            <td style="width:140px">
              <div class="nivel-track"><div class="nivel-fill" style="width:<?php echo $e['pct']; ?>%;background:<?php echo $statusColor; ?>"></div></div>
            </td>
            <td class="pe-3"><span class="badge rounded-pill" style="background:<?php echo $statusBg; ?>;color:<?php echo $statusColor; ?>;font-weight:700"><?php echo $statusLabel; ?></span></td>
            <?php if ($u['perfil'] === 'Administrador') { ?>
              <td class="pe-3 text-end">
                <button type="button" class="btn btn-sm btn-icon-edit" title="Editar estoque mínimo" data-bs-toggle="modal" data-bs-target="#modalMinimo<?php echo $e['item_id']; ?>">
                  <i class="bi bi-pencil"></i>
                </button>
              </td>
            <?php } ?>
          </tr>
        <?php } ?>
        <?php if (!count($estoque)) { ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Nenhum item em estoque.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($u['perfil'] === 'Administrador') { ?>
  <?php foreach ($estoque as $e) { ?>
    <div class="modal fade" id="modalMinimo<?php echo $e['item_id']; ?>" tabindex="-1">
      <div class="modal-dialog">
        <form class="modal-content" method="post" action="estoque_minimo_salvar.php">
          <?php csrf_field(); ?>
          <input type="hidden" name="item_id" value="<?php echo $e['item_id']; ?>">
          <div class="modal-header">
            <h5 class="modal-title">Estoque mínimo · <?php echo e($e['item_nome']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label class="form-label small fw-semibold">Mínimo (<?php echo e($e['unidade']); ?>)</label>
            <input type="number" step="0.001" min="0" name="minimo" class="form-control" value="<?php echo e(fmt_num_input($e['minimo'])); ?>" required>
            <div class="text-muted small mt-2">Itens com saldo abaixo do mínimo aparecem como "Baixo" aqui e no dashboard.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-sigis">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  <?php } ?>
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
