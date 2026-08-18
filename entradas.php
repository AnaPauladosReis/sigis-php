<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$sql = "SELECT e.*, p.nome as projeto_nome, i.nome as item_nome, i.unidade
        FROM entradas e
        LEFT JOIN projetos p ON p.id = e.projeto_id
        JOIN itens i ON i.id = e.item_id
        ORDER BY e.data_entrada DESC, e.id DESC";
$entradas = $pdo->query($sql)->fetchAll();

$projetos = $pdo->query("SELECT * FROM projetos WHERE status = 'Ativo' ORDER BY nome")->fetchAll();
$itens = $pdo->query('SELECT * FROM itens ORDER BY nome')->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Entradas de Materiais</div>
    <div class="text-muted">Registro de materiais recebidos por origem e projeto social</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalEntrada">+ Registrar Entrada</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Data</th><th>Origem</th><th>Projeto</th><th>Item</th><th>Quantidade</th><th class="pe-3">Peso</th></tr></thead>
      <tbody>
        <?php foreach ($entradas as $e) { ?>
          <tr>
            <td class="ps-3"><?php echo e(date('d/m/Y', strtotime($e['data_entrada']))); ?></td>
            <td><?php echo e($e['origem']); ?></td>
            <td class="fw-medium text-dark"><?php echo e($e['projeto_nome'] ?: '—'); ?></td>
            <td><?php echo e($e['item_nome']); ?></td>
            <td><?php echo fmt_num($e['quantidade'], 3) . ' ' . e($e['unidade']); ?></td>
            <td class="pe-3"><?php echo $e['peso'] !== null ? fmt_num($e['peso'], 1) . ' kg' : '—'; ?></td>
          </tr>
        <?php } ?>
        <?php if (!count($entradas)) { ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma entrada registrada ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalEntrada" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="entradas_salvar.php">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Entrada</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Data</label>
            <input type="date" name="data_entrada" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Origem</label>
            <input type="text" name="origem" class="form-control" placeholder="Doação espontânea..." required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Projeto</label>
          <select name="projeto_id" class="form-select">
            <option value="">— Sem projeto —</option>
            <?php foreach ($projetos as $p) { ?>
              <option value="<?php echo $p['id']; ?>"><?php echo e($p['nome']); ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Item</label>
            <select name="item_id" class="form-select" required>
              <?php foreach ($itens as $it) { ?>
                <option value="<?php echo $it['id']; ?>"><?php echo e($it['nome']); ?> (<?php echo e($it['unidade']); ?>)</option>
              <?php } ?>
            </select>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Quantidade</label>
            <input type="number" step="0.001" min="0.001" name="quantidade" class="form-control" required>
          </div>
        </div>
        <div class="mb-1">
          <label class="form-label small fw-semibold">Peso (kg)</label>
          <input type="number" step="0.001" min="0" name="peso" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sigis">Registrar</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
