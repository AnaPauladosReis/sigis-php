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
      <thead><tr><th class="ps-3">Item</th><th>Grupo</th><th>Saldo</th><th>Nível</th><th class="pe-3">Status</th></tr></thead>
      <tbody>
        <?php foreach ($estoque as $e) {
            $statusLabel = $e['baixo'] ? 'Baixo' : 'OK';
            $statusBg = $e['baixo'] ? '#FBEDE3' : '#E8F5EC';
            $statusColor = $e['baixo'] ? '#C1662F' : '#2E8B57';
        ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($e['item_nome']); ?></td>
            <td><?php echo e($e['grupo_nome']); ?></td>
            <td><?php echo fmt_num($e['saldo'], 3) . ' ' . e($e['unidade']); ?></td>
            <td style="width:140px">
              <div class="nivel-track"><div class="nivel-fill" style="width:<?php echo $e['pct']; ?>%;background:<?php echo $statusColor; ?>"></div></div>
            </td>
            <td class="pe-3"><span class="badge rounded-pill" style="background:<?php echo $statusBg; ?>;color:<?php echo $statusColor; ?>;font-weight:700"><?php echo $statusLabel; ?></span></td>
          </tr>
        <?php } ?>
        <?php if (!count($estoque)) { ?>
          <tr><td colspan="5" class="text-center text-muted py-4">Nenhum item em estoque.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
