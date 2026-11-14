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
      <thead><tr><th class="ps-3">Instituição</th><th>Razão Social</th><th>CNPJ</th><th>Bairro</th><th>Responsável</th><th>Tipo</th><th>Acolhimento</th><th>Status</th><th class="pe-3 text-end">Ações</th></tr></thead>
      <tbody>
        <?php foreach ($instituicoes as $i) { ?>
          <tr>
            <td class="ps-3 fw-medium"><a href="instituicao.php?id=<?php echo $i['id']; ?>" class="text-dark text-decoration-none"><?php echo e($i['nome']); ?></a></td>
            <td><?php echo e($i['razao_social']); ?></td>
            <td><?php echo e($i['cnpj']); ?></td>
            <td><?php echo e($i['bairro']); ?></td>
            <td><?php echo e($i['responsavel']); ?></td>
            <td><?php echo e($i['tipo']); ?></td>
            <td class="fw-semibold" style="color:<?php echo $i['acolhimento'] ? '#2E8B57' : '#98A2B3'; ?>"><?php echo $i['acolhimento'] ? 'Sim' : 'Não'; ?></td>
            <td class="fw-semibold" style="color:<?php echo $i['ativo'] ? '#2E8B57' : '#98A2B3'; ?>"><?php echo $i['ativo'] ? 'Ativa' : 'Inativa'; ?></td>
            <td class="pe-3 text-end">
              <div class="d-flex gap-1 justify-content-end flex-nowrap">
                <button type="button" class="btn btn-sm btn-icon-edit" title="Editar instituição" data-bs-toggle="modal" data-bs-target="#modalEditInstituicao<?php echo $i['id']; ?>">
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="post" action="instituicoes_excluir.php" class="d-inline" onsubmit="return confirm('Excluir a instituição \'<?php echo e($i['nome']); ?>\'? Esta ação não pode ser desfeita.');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-icon-delete" title="Excluir instituição">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php } ?>
        <?php if (!count($instituicoes)) { ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Nenhuma instituição encontrada.</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalInstituicao" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="instituicoes_salvar.php">
      <?php csrf_field(); ?>
      <div class="modal-header">
        <h5 class="modal-title">Nova Instituição</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Razão Social</label>
          <input type="text" name="razao_social" class="form-control">
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

<?php foreach ($instituicoes as $i) { ?>
  <div class="modal fade" id="modalEditInstituicao<?php echo $i['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="post" action="instituicoes_editar.php">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
        <div class="modal-header">
          <h5 class="modal-title">Editar Instituição</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nome</label>
            <input type="text" name="nome" class="form-control" value="<?php echo e($i['nome']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Razão Social</label>
            <input type="text" name="razao_social" class="form-control" value="<?php echo e($i['razao_social']); ?>">
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label small fw-semibold">CNPJ</label>
              <input type="text" name="cnpj" class="form-control" value="<?php echo e($i['cnpj']); ?>" required>
            </div>
            <div class="col">
              <label class="form-label small fw-semibold">Bairro</label>
              <input type="text" name="bairro" class="form-control" value="<?php echo e($i['bairro']); ?>">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col">
              <label class="form-label small fw-semibold">Responsável</label>
              <input type="text" name="responsavel" class="form-control" value="<?php echo e($i['responsavel']); ?>">
            </div>
            <div class="col">
              <label class="form-label small fw-semibold">Tipo</label>
              <input type="text" name="tipo" class="form-control" value="<?php echo e($i['tipo']); ?>" placeholder="Ex: Casa de acolhimento">
            </div>
          </div>
          <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" id="acolhimentoCkEdit<?php echo $i['id']; ?>" name="acolhimento" value="1" <?php echo $i['acolhimento'] ? 'checked' : ''; ?>>
            <label class="form-check-label small" for="acolhimentoCkEdit<?php echo $i['id']; ?>">Instituição de acolhimento</label>
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="ativaCkEdit<?php echo $i['id']; ?>" name="ativo" value="1" <?php echo $i['ativo'] ? 'checked' : ''; ?>>
            <label class="form-check-label small" for="ativaCkEdit<?php echo $i['id']; ?>">Instituição ativa</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sigis-outline" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sigis">Salvar alterações</button>
        </div>
      </form>
    </div>
  </div>
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
