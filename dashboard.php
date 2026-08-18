<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$hoje = new DateTime();
$inicioMes = $hoje->format('Y-m-01');

// ---- KPIs ----
$instituicoesAtendidas = (int)$pdo->query('SELECT COUNT(DISTINCT instituicao_id) FROM doacoes')->fetchColumn();
$stmt = $pdo->prepare('SELECT COUNT(DISTINCT instituicao_id) FROM doacoes WHERE data_doacao >= ?');
$stmt->execute(array($inicioMes));
$instituicoesMes = (int)$stmt->fetchColumn();

$itensDoados = (float)$pdo->query('SELECT COALESCE(SUM(quantidade),0) FROM doacao_itens')->fetchColumn();
$stmt = $pdo->prepare('SELECT COALESCE(SUM(di.quantidade),0) FROM doacao_itens di JOIN doacoes d ON d.id = di.doacao_id WHERE d.data_doacao >= ?');
$stmt->execute(array($inicioMes));
$itensDoadosMes = (float)$stmt->fetchColumn();

$residuoTotal = (float)$pdo->query('SELECT COALESCE(SUM(residuo_kg),0) FROM producoes')->fetchColumn();
$stmt = $pdo->prepare('SELECT COALESCE(SUM(residuo_kg),0) FROM producoes WHERE data_producao >= ?');
$stmt->execute(array($inicioMes));
$residuoMes = (float)$stmt->fetchColumn();

$vidasTotal = (float)$pdo->query('SELECT COALESCE(SUM(vidas_impactadas),0) FROM doacoes')->fetchColumn();
$stmt = $pdo->prepare('SELECT COALESCE(SUM(vidas_impactadas),0) FROM doacoes WHERE data_doacao >= ?');
$stmt->execute(array($inicioMes));
$vidasMes = (float)$stmt->fetchColumn();

$kpis = array(
    array('label' => 'Instituições atendidas', 'value' => fmt_num($instituicoesAtendidas), 'delta' => '+' . fmt_num($instituicoesMes) . ' este mês'),
    array('label' => 'Itens doados', 'value' => fmt_num($itensDoados), 'delta' => '+' . fmt_num($itensDoadosMes) . ' este mês'),
    array('label' => 'Resíduos reaproveitados', 'value' => fmt_num($residuoTotal) . ' kg', 'delta' => '+' . fmt_num($residuoMes) . ' kg este mês'),
    array('label' => 'Vidas impactadas', 'value' => fmt_num($vidasTotal, 1), 'delta' => '+' . fmt_num($vidasMes, 1) . ' este mês'),
);

// ---- Producao social por mes (ultimos 6 meses) ----
$producaoChart = array();
$maxQtd = 0;
$meses = array();
for ($i = 5; $i >= 0; $i--) {
    $m = new DateTime("first day of -$i month");
    $meses[$m->format('Y-m')] = 0.0;
}
$stmt = $pdo->prepare("SELECT DATE_FORMAT(data_producao, '%Y-%m') as ym, COALESCE(SUM(quantidade_gerada),0) as total FROM producoes WHERE data_producao >= ? GROUP BY ym");
$stmt->execute(array((new DateTime('first day of -5 month'))->format('Y-m-01')));
foreach ($stmt->fetchAll() as $row) {
    if (isset($meses[$row['ym']])) {
        $meses[$row['ym']] = (float)$row['total'];
    }
}
$maxQtd = max(1, max($meses));
$mesesPt = array('01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez');
foreach ($meses as $ym => $total) {
    $mm = substr($ym, 5, 2);
    $pct = max(6, round(($total / $maxQtd) * 100));
    $producaoChart[] = array('label' => $mesesPt[$mm], 'height' => $pct . '%');
}

// ---- Distribuicao de vidas impactadas por projeto ----
$distribuicaoLegenda = array();
$stmt = $pdo->query("SELECT p.nome, COALESCE(SUM(d.vidas_impactadas),0) as vidas FROM doacoes d JOIN projetos p ON p.id = d.projeto_id GROUP BY p.id, p.nome HAVING vidas > 0 ORDER BY vidas DESC");
$rows = $stmt->fetchAll();
$totalVidasProjetos = 0;
foreach ($rows as $r) { $totalVidasProjetos += (float)$r['vidas']; }
$palette = array('#2B5FAD', '#5B85C4', '#8FA8D6', '#C7D4EA', '#EEF0F3');
$gradientParts = array();
$acc = 0;
foreach ($rows as $i => $r) {
    $pct = $totalVidasProjetos > 0 ? round(((float)$r['vidas'] / $totalVidasProjetos) * 100) : 0;
    $color = isset($palette[$i]) ? $palette[$i] : '#EEF0F3';
    $distribuicaoLegenda[] = array('label' => $r['nome'], 'color' => $color, 'pct' => $pct . '%');
    $gradientParts[] = $color . ' ' . $acc . '% ' . ($acc + $pct) . '%';
    $acc += $pct;
}
$donutGradient = count($gradientParts) ? implode(', ', $gradientParts) : '#EEF0F3 0% 100%';

// ---- Atividade recente (ultimos 6 eventos) ----
$sqlAtiv = "
(SELECT e.data_entrada as dt, e.criado_em as ts, 'Entrada de material registrada' as acao, p.nome as projeto, COALESCE(u.nome,'Administrador') as usuario
 FROM entradas e LEFT JOIN projetos p ON p.id = e.projeto_id LEFT JOIN usuarios u ON u.id = e.criado_por)
UNION ALL
(SELECT pr.data_producao as dt, pr.criado_em as ts, 'Produção social registrada' as acao, p.nome as projeto, COALESCE(u.nome,'Administrador') as usuario
 FROM producoes pr LEFT JOIN projetos p ON p.id = pr.projeto_id LEFT JOIN usuarios u ON u.id = pr.criado_por)
UNION ALL
(SELECT d.data_doacao as dt, d.criado_em as ts, 'Doação direcionada' as acao, p.nome as projeto, COALESCE(u.nome,'Administrador') as usuario
 FROM doacoes d LEFT JOIN projetos p ON p.id = d.projeto_id LEFT JOIN usuarios u ON u.id = d.criado_por)
UNION ALL
(SELECT t.emitido_em as dt, t.emitido_em as ts, 'Termo de doação emitido' as acao, p.nome as projeto, 'Administrador' as usuario
 FROM termos t JOIN doacoes d ON d.id = t.doacao_id LEFT JOIN projetos p ON p.id = d.projeto_id)
ORDER BY ts DESC
LIMIT 6";
$atividades = $pdo->query($sqlAtiv)->fetchAll();

$pageTitle = 'Dashboard administrativo';
require __DIR__ . '/includes/layout_top.php';
?>
<div class="mb-1 fw-bold" style="font-size:1.35rem"><?php echo e($pageTitle); ?></div>
<div class="text-muted mb-4">Visão consolidada das ações da Gerência de Responsabilidade Social</div>

<div class="row g-3 mb-3">
  <?php foreach ($kpis as $k) { ?>
    <div class="col-6 col-xl-3">
      <div class="card p-3 h-100">
        <div class="kpi-label mb-2"><?php echo e($k['label']); ?></div>
        <div class="kpi-value"><?php echo e($k['value']); ?></div>
        <div class="kpi-delta mt-1"><?php echo e($k['delta']); ?></div>
      </div>
    </div>
  <?php } ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-7">
    <div class="card p-3 h-100">
      <h3 class="fs-6 fw-semibold mb-3">Produção social por mês</h3>
      <div class="bars">
        <?php foreach ($producaoChart as $b) { ?>
          <div class="bar-col">
            <div class="bar" style="height:<?php echo e($b['height']); ?>"></div>
            <div class="small text-muted"><?php echo e($b['label']); ?></div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card p-3 h-100">
      <h3 class="fs-6 fw-semibold mb-3">Distribuição por projeto</h3>
      <?php if (count($distribuicaoLegenda)) { ?>
        <div class="donut" style="background:conic-gradient(<?php echo $donutGradient; ?>)"></div>
        <div>
          <?php foreach ($distribuicaoLegenda as $d) { ?>
            <div class="d-flex align-items-center gap-2 small mb-2">
              <div class="legend-dot" style="background:<?php echo e($d['color']); ?>"></div>
              <div class="flex-fill"><?php echo e($d['label']); ?></div>
              <div class="fw-semibold"><?php echo e($d['pct']); ?></div>
            </div>
          <?php } ?>
        </div>
      <?php } else { ?>
        <div class="text-muted small">Nenhuma doação registrada ainda.</div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="p-3 pb-2 fw-semibold" style="font-size:.9rem">Atividade recente</div>
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Data</th><th>Ação</th><th>Projeto</th><th class="pe-3">Usuário</th></tr></thead>
      <tbody>
        <?php foreach ($atividades as $a) { ?>
          <tr>
            <td class="ps-3"><?php echo e(date('d/m', strtotime($a['dt']))); ?></td>
            <td class="fw-medium text-dark"><?php echo e($a['acao']); ?></td>
            <td><?php echo e($a['projeto'] ?: '—'); ?></td>
            <td class="pe-3 text-muted"><?php echo e($a['usuario']); ?></td>
          </tr>
        <?php } ?>
        <?php if (!count($atividades)) { ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma atividade registrada ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
