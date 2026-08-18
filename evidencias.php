<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$sql = "SELECT ev.*, i.nome as instituicao_nome FROM evidencias ev LEFT JOIN instituicoes i ON i.id = ev.instituicao_id ORDER BY ev.data_evidencia DESC, ev.id DESC LIMIT 8";
$evidencias = $pdo->query($sql)->fetchAll();
$instituicoes = $pdo->query('SELECT * FROM instituicoes ORDER BY nome')->fetchAll();

$relatorios = array(
    array('nome' => 'Projetos Sociais', 'periodo' => 'Todos os registros', 'tipo' => 'projetos'),
    array('nome' => 'Instituições Atendidas', 'periodo' => 'Todos os registros', 'tipo' => 'instituicoes'),
    array('nome' => 'Entradas de Materiais', 'periodo' => 'Todos os registros', 'tipo' => 'entradas'),
    array('nome' => 'Produção Social', 'periodo' => 'Todos os registros', 'tipo' => 'producao'),
    array('nome' => 'Doações Realizadas', 'periodo' => 'Todos os registros', 'tipo' => 'doacoes'),
    array('nome' => 'Vidas Impactadas', 'periodo' => 'Consolidado geral', 'tipo' => 'vidas'),
);

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Evidências &amp; Relatórios</div>
    <div class="text-muted">Registro fotográfico das ações e relatórios gerenciais consolidados</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalEvidencia">+ Nova Evidência</button>
</div>

<div class="fw-semibold mb-2" style="font-size:.9rem">Evidências recentes</div>
<div class="row g-3 mb-4">
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
        <div class="p-2">
          <div class="small fw-semibold text-dark"><?php echo e($ev['instituicao_nome'] ?: '—'); ?></div>
          <div class="small text-muted"><?php echo e(date('d/m/Y', strtotime($ev['data_evidencia']))); ?></div>
        </div>
      </div>
    </div>
  <?php } ?>
  <?php if (!count($evidencias)) { ?>
    <div class="text-muted small">Nenhuma evidência cadastrada ainda.</div>
  <?php } ?>
</div>

<div class="fw-semibold mb-2" style="font-size:.9rem">Relatórios gerenciais</div>
<div class="row g-3">
  <?php foreach ($relatorios as $r) { ?>
    <div class="col-md-4">
      <div class="card p-3 d-flex flex-row justify-content-between align-items-center">
        <div>
          <div class="fw-semibold" style="font-size:.85rem"><?php echo e($r['nome']); ?></div>
          <div class="small text-muted"><?php echo e($r['periodo']); ?></div>
        </div>
        <a class="small fw-semibold text-decoration-none" style="color:var(--blue)" href="relatorios_exportar.php?tipo=<?php echo e($r['tipo']); ?>">Exportar</a>
      </div>
    </div>
  <?php } ?>
</div>

<div class="modal fade" id="modalEvidencia" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="evidencias_salvar.php" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Nova Evidência</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Instituição</label>
          <select name="instituicao_id" class="form-select">
            <option value="">— Não informado —</option>
            <?php foreach ($instituicoes as $i) { ?>
              <option value="<?php echo $i['id']; ?>"><?php echo e($i['nome']); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Data</label>
            <input type="date" name="data_evidencia" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Arquivo (imagem)</label>
            <input type="file" name="arquivo" class="form-control" accept="image/*" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Enviar</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
