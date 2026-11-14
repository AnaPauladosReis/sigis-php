<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$doacoes = $pdo->query(
    'SELECT d.id, d.data_doacao, d.vidas_impactadas, i.nome AS instituicao_nome, p.nome AS projeto_nome, t.id AS termo_id, t.numero AS termo_numero
     FROM doacoes d
     JOIN instituicoes i ON i.id = d.instituicao_id
     LEFT JOIN projetos p ON p.id = d.projeto_id
     LEFT JOIN termos t ON t.doacao_id = d.id
     ORDER BY d.data_doacao DESC, d.id DESC'
)->fetchAll();

require __DIR__ . '/includes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <div class="fw-bold" style="font-size:1.35rem">Histórico de Doações</div>
    <div class="text-muted">Doações já direcionadas. Doações lançadas por engano podem ser canceladas, o que devolve os itens ao estoque.</div>
  </div>
  <a href="doacoes.php" class="btn btn-sigis">+ Nova Doação</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-sigis mb-0">
      <thead><tr><th class="ps-3">Data</th><th>Instituição</th><th>Projeto</th><th>Vidas Impactadas</th><th>Termo</th><th class="pe-3 text-end">Ações</th></tr></thead>
      <tbody>
        <?php foreach ($doacoes as $d) { ?>
          <tr>
            <td class="ps-3 fw-medium text-dark"><?php echo e(date('d/m/Y', strtotime($d['data_doacao']))); ?></td>
            <td><?php echo e($d['instituicao_nome']); ?></td>
            <td><?php echo e($d['projeto_nome'] ?: '—'); ?></td>
            <td><?php echo fmt_num($d['vidas_impactadas'], 1); ?></td>
            <td>
              <?php if ($d['termo_id']) { ?>
                <a href="termo.php?id=<?php echo $d['termo_id']; ?>" target="_blank" rel="noopener"><?php echo e($d['termo_numero']); ?></a>
              <?php } else { ?>
                —
              <?php } ?>
            </td>
            <td class="pe-3 text-end">
              <div class="d-flex gap-1 justify-content-end flex-nowrap">
                <form method="post" action="doacoes_cancelar.php" class="d-inline" onsubmit="return confirm('Cancelar esta doação para \'<?php echo e($d['instituicao_nome']); ?>\'? Os itens voltarão ao estoque e o termo <?php echo e($d['termo_numero']); ?> será removido. Esta ação não pode ser desfeita.');">
                  <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-icon-delete" title="Cancelar doação (estornar itens ao estoque)">
                    <i class="bi bi-arrow-counterclockwise"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php } ?>
        <?php if (!count($doacoes)) { ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma doação registrada ainda.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
