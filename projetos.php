<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = 'SELECT * FROM projetos WHERE nome LIKE ? ORDER BY id';
$stmt = $pdo->prepare($sql);
$stmt->execute(array('%' . $q . '%'));
$projetos = $stmt->fetchAll();

$stmtGrupos = $pdo->prepare('SELECT COUNT(DISTINCT i.grupo_id) FROM entradas e JOIN itens i ON i.id = e.item_id WHERE e.projeto_id = ?');
$stmtItens = $pdo->prepare('SELECT COALESCE(SUM(quantidade),0) FROM entradas WHERE projeto_id = ?');
$stmtVidas = $pdo->prepare('SELECT COALESCE(SUM(vidas_impactadas),0) FROM doacoes WHERE projeto_id = ?');

foreach ($projetos as &$p) {
    $stmtGrupos->execute(array($p['id']));
    $p['grupos'] = (int)$stmtGrupos->fetchColumn();
    $stmtItens->execute(array($p['id']));
    $p['itensDoados'] = (float)$stmtItens->fetchColumn();
    $stmtVidas->execute(array($p['id']));
    $p['vidas'] = (float)$stmtVidas->fetchColumn();
}
unset($p);

$gruposAll = $pdo->query('SELECT * FROM grupos_impacto ORDER BY nome')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Projetos Sociais</div>
    <div class="text-muted">Iniciativas de economia circular e impacto social geridas pela GERSC</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalProjeto">+ Novo Projeto</button>
</div>

<form method="get" class="mb-3">
  <input type="text" name="q" class="form-control" style="width:320px" placeholder="Buscar por nome do projeto..." value="<?php echo e($q); ?>" onchange="this.form.submit()">
</form>

<div class="row g-3">
  <?php foreach ($projetos as $p) {
      $badgeBg = $p['status'] === 'Encerrado' ? '#F3E7E7' : '#E8F5EC';
      $badgeColor = $p['status'] === 'Encerrado' ? '#9B2C2C' : '#2E8B57';
  ?>
    <div class="col-md-6">
      <div class="card p-3 h-100">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="fw-bold" style="font-size:1rem"><?php echo e($p['nome']); ?></div>
          <span class="badge rounded-pill" style="background:<?php echo $badgeBg; ?>;color:<?php echo $badgeColor; ?>;font-weight:700"><?php echo e($p['status']); ?></span>
        </div>
        <div class="text-muted small mb-3" style="min-height:40px"><?php echo e($p['descricao']); ?></div>
        <div class="d-flex gap-4 pt-2 border-top project-stats">
          <div><div class="val"><?php echo $p['grupos']; ?></div><div class="lbl">Grupos</div></div>
          <div><div class="val"><?php echo fmt_num($p['itensDoados']); ?></div><div class="lbl">Itens doados</div></div>
          <div><div class="val" style="color:var(--blue)"><?php echo fmt_num($p['vidas'], 1); ?></div><div class="lbl">Vidas impactadas</div></div>
        </div>
      </div>
    </div>
  <?php } ?>
  <?php if (!count($projetos)) { ?>
    <div class="text-muted text-center py-5">Nenhum projeto encontrado.</div>
  <?php } ?>
</div>

<!-- Modal Novo Projeto -->
<div class="modal fade" id="modalProjeto" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="projetos_salvar.php">
      <div class="modal-header">
        <h5 class="modal-title">Novo Projeto Social</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Cadastre uma nova iniciativa da GERSC</p>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome do projeto</label>
          <input type="text" name="nome" class="form-control" placeholder="Ex: Retalhos do Bem" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Descrição</label>
          <textarea name="descricao" class="form-control" rows="4" placeholder="Descreva o objetivo do projeto..."></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Grupo de impacto vinculado</label>
          <select name="grupo_id" class="form-select">
            <?php foreach ($gruposAll as $g) { ?>
              <option value="<?php echo $g['id']; ?>"><?php echo e($g['nome']); ?></option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Salvar Projeto</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
