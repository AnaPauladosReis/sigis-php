<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM instituicoes WHERE id = ?');
$stmt->execute(array($id));
$instituicao = $stmt->fetch();

if (!$instituicao) {
    flash_set('Instituição não encontrada.', 'danger');
    redirect('instituicoes.php');
}

$doacoes = array();
$totalVidas = 0;
$stmtItens = $pdo->prepare('SELECT di.quantidade, it.nome, it.unidade FROM doacao_itens di JOIN itens it ON it.id = di.item_id WHERE di.doacao_id = ? ORDER BY it.nome');
$stmtDoacoes = $pdo->prepare(
    'SELECT d.id, d.data_doacao, d.vidas_impactadas, t.id AS termo_id, t.numero AS termo_numero
     FROM doacoes d
     LEFT JOIN termos t ON t.doacao_id = d.id
     WHERE d.instituicao_id = ?
     ORDER BY d.data_doacao DESC, d.id DESC'
);
$stmtDoacoes->execute(array($id));
foreach ($stmtDoacoes->fetchAll() as $d) {
    $stmtItens->execute(array($d['id']));
    $itens = array();
    foreach ($stmtItens->fetchAll() as $it) {
        $itens[] = fmt_num_min($it['quantidade']) . ' ' . $it['unidade'] . ' ' . $it['nome'];
    }
    $d['itens_resumo'] = implode(', ', $itens);
    $doacoes[] = $d;
    $totalVidas += (float)$d['vidas_impactadas'];
}

$stmt = $pdo->prepare('SELECT * FROM evidencias WHERE instituicao_id = ? ORDER BY data_evidencia DESC, id DESC');
$stmt->execute(array($id));
$evidencias = $stmt->fetchAll();

$pageTitle = $instituicao['nome'];
require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">
      <?php echo e($instituicao['nome']); ?>
      <?php if (!$instituicao['ativo']) { ?>
        <span class="badge rounded-pill ms-1" style="background:#EEF0F3;color:#98A2B3;font-weight:700">Inativa</span>
      <?php } ?>
    </div>
    <div class="text-muted"><?php echo e($instituicao['razao_social'] ?: $instituicao['cnpj']); ?></div>
  </div>
  <a href="instituicoes.php" class="btn btn-sigis-outline">Voltar</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card p-3">
      <div class="fw-semibold mb-2" style="font-size:.9rem">Dados cadastrais</div>
      <div class="row small">
        <div class="col-6 mb-2"><div class="text-muted">CNPJ</div><div class="fw-medium text-dark"><?php echo e($instituicao['cnpj']); ?></div></div>
        <div class="col-6 mb-2"><div class="text-muted">Bairro</div><div class="fw-medium text-dark"><?php echo e($instituicao['bairro'] ?: '—'); ?></div></div>
        <div class="col-6 mb-2"><div class="text-muted">Responsável</div><div class="fw-medium text-dark"><?php echo e($instituicao['responsavel'] ?: '—'); ?></div></div>
        <div class="col-6 mb-2"><div class="text-muted">Tipo</div><div class="fw-medium text-dark"><?php echo e($instituicao['tipo'] ?: '—'); ?></div></div>
        <div class="col-6"><div class="text-muted">Acolhimento</div><div class="fw-medium text-dark"><?php echo $instituicao['acolhimento'] ? 'Sim' : 'Não'; ?></div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="summary-box d-flex flex-column justify-content-center p-3 h-100">
      <div class="fw-semibold small" style="color:var(--accent-text)">Total de vidas impactadas</div>
      <div class="fw-bold fs-3" style="color:var(--blue)"><?php echo fmt_num($totalVidas, 1); ?></div>
      <div class="text-muted small"><?php echo count($doacoes); ?> doaç<?php echo count($doacoes) === 1 ? 'ão' : 'ões'; ?> recebida<?php echo count($doacoes) === 1 ? '' : 's'; ?></div>
    </div>
  </div>
</div>

<div class="card mb-4">
  <div class="p-3 pb-2 fw-semibold" style="font-size:.9rem">Doações recebidas</div>
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Data</th><th>Itens</th><th>Vidas</th><th class="pe-3">Termo</th></tr></thead>
      <tbody>
        <?php foreach ($doacoes as $d) { ?>
          <tr>
            <td class="ps-3"><?php echo e(date('d/m/Y', strtotime($d['data_doacao']))); ?></td>
            <td class="text-muted"><?php echo e($d['itens_resumo'] ?: '—'); ?></td>
            <td class="fw-semibold" style="color:var(--blue)"><?php echo fmt_num($d['vidas_impactadas'], 1); ?></td>
            <td class="pe-3">
              <?php if ($d['termo_id']) { ?>
                <a href="termo.php?id=<?php echo $d['termo_id']; ?>" target="_blank" rel="noopener"><?php echo e($d['termo_numero']); ?></a>
              <?php } else { ?>
                —
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <?php if (!count($doacoes)) { ?>
          <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma doação recebida ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="p-3 pb-2 fw-semibold" style="font-size:.9rem">Evidências</div>
  <div class="row g-3 p-3 pt-0">
    <?php foreach ($evidencias as $ev) {
        $path = 'uploads/evidencias/' . $ev['arquivo'];
        $exists = file_exists(__DIR__ . '/' . $path);
    ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 overflow-hidden">
          <div class="evid-thumb">
            <?php if ($exists) { ?>
              <img src="<?php echo e($path); ?>" alt="">
            <?php } else { ?>
              <div class="small font-monospace text-muted"><?php echo e($ev['arquivo']); ?></div>
            <?php } ?>
          </div>
          <div class="p-2 small text-muted"><?php echo e(date('d/m/Y', strtotime($ev['data_evidencia']))); ?></div>
        </div>
      </div>
    <?php } ?>
    <?php if (!count($evidencias)) { ?>
      <div class="text-muted small">Nenhuma evidência cadastrada para esta instituição.</div>
    <?php } ?>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
