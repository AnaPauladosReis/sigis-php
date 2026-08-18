<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$sql = "SELECT pr.*, p.nome as projeto_nome, i.nome as item_nome
        FROM producoes pr
        LEFT JOIN projetos p ON p.id = pr.projeto_id
        LEFT JOIN itens i ON i.id = pr.item_consumido_id
        ORDER BY pr.data_producao DESC, pr.id DESC";
$producoes = $pdo->query($sql)->fetchAll();

$projetos = $pdo->query("SELECT * FROM projetos WHERE status = 'Ativo' ORDER BY nome")->fetchAll();
$itens = $pdo->query("SELECT es.item_id, i.nome, i.unidade, es.saldo FROM estoque es JOIN itens i ON i.id = es.item_id ORDER BY i.nome")->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Produção Social</div>
    <div class="text-muted">Transformação de materiais recebidos em produtos sociais</div>
  </div>
  <button class="btn btn-sigis" data-bs-toggle="modal" data-bs-target="#modalProducao">+ Registrar Produção</button>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Data</th><th>Projeto</th><th>Material consumido</th><th>Produto gerado</th><th>Quantidade</th><th class="pe-3">Resíduo reaproveitado</th></tr></thead>
      <tbody>
        <?php foreach ($producoes as $p) { ?>
          <tr>
            <td class="ps-3"><?php echo e(date('d/m/Y', strtotime($p['data_producao']))); ?></td>
            <td class="fw-medium text-dark"><?php echo e($p['projeto_nome'] ?: '—'); ?></td>
            <td><?php echo e($p['item_nome']) . ' · ' . fmt_num($p['qtd_consumida'], 2) . ' kg'; ?></td>
            <td><?php echo e($p['produto_gerado']); ?></td>
            <td><?php echo fmt_num($p['quantidade_gerada'], 0) . ' ' . e($p['unidade_gerada']); ?></td>
            <td class="pe-3 fw-semibold" style="color:#2E8B57"><?php echo fmt_num($p['residuo_kg'], 1); ?> kg</td>
          </tr>
        <?php } ?>
        <?php if (!count($producoes)) { ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma produção registrada ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalProducao" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="producao_salvar.php">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Produção</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Data</label>
            <input type="date" name="data_producao" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Projeto</label>
            <select name="projeto_id" class="form-select">
              <option value="">— Sem projeto —</option>
              <?php foreach ($projetos as $p) { ?>
                <option value="<?php echo $p['id']; ?>"><?php echo e($p['nome']); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Material consumido (do estoque)</label>
            <select name="item_consumido_id" class="form-select" required>
              <?php foreach ($itens as $it) { ?>
                <option value="<?php echo $it['item_id']; ?>"><?php echo e($it['nome']); ?> — saldo: <?php echo fmt_num($it['saldo'], 2); ?> <?php echo e($it['unidade']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Qtd. consumida (kg)</label>
            <input type="number" step="0.001" min="0.001" name="qtd_consumida" class="form-control" required>
          </div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col">
            <label class="form-label small fw-semibold">Produto gerado</label>
            <input type="text" name="produto_gerado" class="form-control" placeholder="Ex: Naninhas" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Quantidade gerada</label>
            <input type="number" step="1" min="0" name="quantidade_gerada" class="form-control" required>
          </div>
          <div class="col">
            <label class="form-label small fw-semibold">Unidade</label>
            <input type="text" name="unidade_gerada" class="form-control" value="un" required>
          </div>
        </div>
        <div class="mb-1">
          <label class="form-label small fw-semibold">Resíduo reaproveitado (kg)</label>
          <input type="number" step="0.001" min="0" name="residuo_kg" class="form-control" value="0">
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
