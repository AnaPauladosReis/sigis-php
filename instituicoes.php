<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $pdo->prepare('SELECT * FROM instituicoes WHERE nome LIKE ? OR cnpj LIKE ? ORDER BY nome');
$stmt->execute(array('%' . $q . '%', '%' . $q . '%'));
$instituicoes = $stmt->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Instituições Beneficiadas</div>
    <div class="text-muted">Cadastro das instituições aptas a receber doações</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalInstituicao">+ Nova Instituição</button>
</div>

<form method="get" class="mb-3">
  <input type="text" name="q" class="form-control" style="width:320px" placeholder="Buscar por nome ou CNPJ..." value="<?php echo e($q); ?>" onchange="this.form.submit()">
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Instituição</th><th>CNPJ</th><th>Bairro</th><th>Responsável</th><th>Tipo</th><th class="pe-3">Acolhimento</th></tr></thead>
      <tbody>
        <?php foreach ($instituicoes as $i) { ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e($i['nome']); ?></td>
            <td><?php echo e($i['cnpj']); ?></td>
            <td><?php echo e($i['bairro']); ?></td>
            <td><?php echo e($i['responsavel']); ?></td>
            <td><?php echo e($i['tipo']); ?></td>
            <td class="pe-3 fw-semibold" style="color:<?php echo $i['acolhimento'] ? '#2E8B57' : '#98A2B3'; ?>"><?php echo $i['acolhimento'] ? 'Sim' : 'Não'; ?></td>
          </tr>
        <?php } ?>
        <?php if (!count($instituicoes)) { ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma instituição encontrada.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalInstituicao" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="instituicoes_salvar.php">
      <div class="modal-header">
        <h5 class="modal-title">Nova Instituição</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">CNPJ</label>
            <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0001-00" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Bairro</label>
            <input type="text" name="bairro" class="form-control">
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Responsável</label>
            <input type="text" name="responsavel" class="form-control">
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Tipo</label>
            <input type="text" name="tipo" class="form-control" placeholder="Ex: Casa de acolhimento">
          </div>
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="acolhimentoCk" name="acolhimento" value="1">
          <label class="form-check-label small" for="acolhimentoCk">Instituição de acolhimento</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Salvar</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
