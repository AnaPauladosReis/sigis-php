<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM termos WHERE id = ?');
    $stmt->execute(array($id));
} else {
    $stmt = $pdo->query('SELECT * FROM termos ORDER BY id DESC LIMIT 1');
}
$termo = $stmt->fetch();

$doacao = null;
$itens = array();
if ($termo) {
    $stmt = $pdo->prepare('SELECT d.*, i.nome as instituicao_nome, p.nome as projeto_nome FROM doacoes d JOIN instituicoes i ON i.id = d.instituicao_id LEFT JOIN projetos p ON p.id = d.projeto_id WHERE d.id = ?');
    $stmt->execute(array($termo['doacao_id']));
    $doacao = $stmt->fetch();

    $stmt = $pdo->prepare('SELECT di.quantidade, it.nome, it.unidade FROM doacao_itens di JOIN itens it ON it.id = di.item_id WHERE di.doacao_id = ?');
    $stmt->execute(array($termo['doacao_id']));
    $itens = $stmt->fetchAll();
}

$termosTodos = $pdo->query('SELECT t.id, t.numero, t.emitido_em, i.nome as instituicao_nome FROM termos t JOIN doacoes d ON d.id = t.doacao_id JOIN instituicoes i ON i.id = d.instituicao_id ORDER BY t.id DESC LIMIT 10')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3 no-print">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Termo de Doação</div>
    <div class="text-muted">Documento oficial gerado automaticamente pelo sistema</div>
  </div>
  <?php if ($termo) { ?>
    <div class="d-flex gap-2">
      <button class="btn btn-sigis-outline" onclick="window.print()">Imprimir</button>
      <button class="btn btn-sigis" onclick="window.print()">Exportar PDF</button>
    </div>
  <?php } ?>
</div>

<?php if (!$termo) { ?>
  <div class="card p-4 text-center text-muted no-print">Nenhum termo de doação emitido ainda. Confira em <a href="doacoes.php">Direcionamento de Doações</a>.</div>
<?php } else { ?>
  <div class="card termo-doc mb-4">
    <div class="termo-header d-flex justify-content-between align-items-center pb-3 mb-4">
      <div class="d-flex align-items-center gap-2"><div class="brand-mark sm" style="border-radius:6px"></div><div class="fw-bold" style="font-size:.95rem;color:var(--navy)">GERSC · SESI</div></div>
      <div class="text-end"><div class="text-uppercase small text-muted fw-semibold">Termo Nº</div><div class="fw-bold" style="color:var(--navy)"><?php echo e($termo['numero']); ?></div></div>
    </div>
    <div class="text-center fw-bold text-uppercase mb-1" style="letter-spacing:.3px;color:var(--navy)">Termo de Doação</div>
    <div class="text-center small text-muted mb-4">Emitido em <?php echo e(date('d/m/Y', strtotime($termo['emitido_em']))); ?></div>
    <p style="font-size:.9rem;line-height:1.7;color:#344054">
      A Gerência de Responsabilidade Social do SESI certifica a doação dos itens abaixo relacionados à instituição
      <strong style="color:var(--navy)"><?php echo e($doacao['instituicao_nome']); ?></strong><?php echo $doacao['projeto_nome'] ? ', no âmbito do projeto social <strong style="color:var(--navy)">' . e($doacao['projeto_nome']) . '</strong>' : ''; ?>.
    </p>
    <table class="table mb-4">
      <thead><tr style="border-bottom:1px solid var(--navy)"><th style="color:var(--navy)">Item doado</th><th class="text-end" style="color:var(--navy)">Quantidade</th></tr></thead>
      <tbody>
        <?php foreach ($itens as $it) { ?>
          <tr><td><?php echo e($it['nome']); ?></td><td class="text-end"><?php echo fmt_num($it['quantidade'], 2) . ' ' . e($it['unidade']); ?></td></tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="termo-vidas d-flex justify-content-between align-items-center px-3 py-3 mb-5">
      <div class="small fw-semibold" style="color:var(--accent-text)">Vidas impactadas por esta doação</div>
      <div class="fw-bold fs-4" style="color:var(--blue)"><?php echo fmt_num($doacao['vidas_impactadas'], 1); ?></div>
    </div>
    <div class="d-flex justify-content-between termo-sign mt-5">
      <div style="width:220px">Administrador GERSC</div>
      <div style="width:220px">Responsável pela instituição</div>
    </div>
  </div>
<?php } ?>

<div class="card no-print">
  <div class="p-3 pb-2 fw-semibold" style="font-size:.9rem">Termos emitidos recentemente</div>
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Número</th><th>Instituição</th><th>Emitido em</th><th class="pe-3"></th></tr></thead>
      <tbody>
        <?php foreach ($termosTodos as $t) { ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($t['numero']); ?></td>
            <td><?php echo e($t['instituicao_nome']); ?></td>
            <td><?php echo e(date('d/m/Y', strtotime($t['emitido_em']))); ?></td>
            <td class="pe-3"><a href="termo.php?id=<?php echo $t['id']; ?>">Ver</a></td>
          </tr>
        <?php } ?>
        <?php if (!count($termosTodos)) { ?>
          <tr><td colspan="4" class="text-center text-muted py-3">Nenhum termo emitido ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
