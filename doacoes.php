<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if (empty($_SESSION['wizard']) || !is_array($_SESSION['wizard'])) {
    $_SESSION['wizard'] = array('step' => 1, 'instituicao_id' => null, 'selecoes' => array());
}
$wizard = &$_SESSION['wizard'];

$instituicoes = $pdo->query('SELECT * FROM instituicoes ORDER BY nome')->fetchAll();
$estoqueItens = $pdo->query('SELECT es.item_id, i.nome, i.unidade, i.multiplicador, es.saldo FROM estoque es JOIN itens i ON i.id = es.item_id ORDER BY i.nome')->fetchAll();

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($acao === 'ir_passo2') {
        $instId = !empty($_POST['instituicao_id']) ? (int)$_POST['instituicao_id'] : null;
        if (!$instId) {
            $erro = 'Selecione uma instituição para continuar.';
        } else {
            $wizard['instituicao_id'] = $instId;
            $wizard['step'] = 2;
        }
    } elseif ($acao === 'voltar_passo1') {
        $wizard['step'] = 1;
    } elseif ($acao === 'ir_passo3') {
        $selecoes = array();
        if (!empty($_POST['qtd']) && is_array($_POST['qtd'])) {
            foreach ($_POST['qtd'] as $itemId => $qtd) {
                $qtd = max(0, (float)$qtd);
                if ($qtd > 0) {
                    $selecoes[(int)$itemId] = $qtd;
                }
            }
        }
        if (!count($selecoes)) {
            $erro = 'Informe ao menos um item para doar.';
        } else {
            $wizard['selecoes'] = $selecoes;
            $wizard['step'] = 3;
        }
    } elseif ($acao === 'voltar_passo2') {
        $wizard['step'] = 2;
    } elseif ($acao === 'confirmar') {
        if (empty($wizard['instituicao_id']) || empty($wizard['selecoes'])) {
            $erro = 'Fluxo inválido, reinicie a doação.';
        } else {
            // recalcula vidas e valida saldo no momento da confirmacao
            $vidasTotal = 0;
            $itensPorId = array();
            foreach ($estoqueItens as $it) { $itensPorId[$it['item_id']] = $it; }

            foreach ($wizard['selecoes'] as $itemId => $qtd) {
                if (!isset($itensPorId[$itemId]) || $qtd > (float)$itensPorId[$itemId]['saldo']) {
                    $erro = 'Saldo insuficiente para um dos itens selecionados. Revise as quantidades.';
                    break;
                }
                $vidasTotal += $qtd * (float)$itensPorId[$itemId]['multiplicador'];
            }
            $vidasTotal = round($vidasTotal, 1);

            if (!$erro) {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('INSERT INTO doacoes (instituicao_id, projeto_id, data_doacao, vidas_impactadas, criado_por) VALUES (?, NULL, CURDATE(), ?, ?)');
                    $stmt->execute(array($wizard['instituicao_id'], $vidasTotal, $_SESSION['user_id']));
                    $doacaoId = (int)$pdo->lastInsertId();

                    $stmtItem = $pdo->prepare('INSERT INTO doacao_itens (doacao_id, item_id, quantidade) VALUES (?, ?, ?)');
                    $stmtSaldo = $pdo->prepare('UPDATE estoque SET saldo = saldo - ? WHERE item_id = ?');
                    foreach ($wizard['selecoes'] as $itemId => $qtd) {
                        $stmtItem->execute(array($doacaoId, $itemId, $qtd));
                        $stmtSaldo->execute(array($qtd, $itemId));
                    }

                    // gera o numero sequencial do termo (comeca em TD-2026-00146)
                    $ultimo = 146;
                    $stmt = $pdo->query('SELECT numero FROM termos');
                    foreach ($stmt->fetchAll() as $row) {
                        if (preg_match('/(\d+)$/', $row['numero'], $m)) {
                            $ultimo = max($ultimo, (int)$m[1]);
                        }
                    }
                    $numero = 'TD-' . date('Y') . '-00' . ($ultimo + 1);

                    $stmt = $pdo->prepare('INSERT INTO termos (doacao_id, numero, emitido_em) VALUES (?, ?, CURDATE())');
                    $stmt->execute(array($doacaoId, $numero));
                    $termoId = (int)$pdo->lastInsertId();

                    $pdo->commit();
                } catch (Exception $ex) {
                    $pdo->rollBack();
                    $erro = 'Não foi possível registrar a doação: ' . $ex->getMessage();
                }

                if (!$erro) {
                    $_SESSION['wizard'] = array('step' => 1, 'instituicao_id' => null, 'selecoes' => array());
                    flash_set('Doação confirmada. Termo ' . $numero . ' gerado.');
                    redirect('termo.php?id=' . $termoId);
                }
            }
        }
    }
}

$instituicaoSelecionada = null;
if (!empty($wizard['instituicao_id'])) {
    foreach ($instituicoes as $i) {
        if ((int)$i['id'] === (int)$wizard['instituicao_id']) { $instituicaoSelecionada = $i; break; }
    }
}

$vidasTotalPreview = 0;
$resumoItens = array();
if ($wizard['step'] === 3 && !empty($wizard['selecoes'])) {
    $itensPorId = array();
    foreach ($estoqueItens as $it) { $itensPorId[$it['item_id']] = $it; }
    foreach ($wizard['selecoes'] as $itemId => $qtd) {
        if (isset($itensPorId[$itemId])) {
            $vidasTotalPreview += $qtd * (float)$itensPorId[$itemId]['multiplicador'];
            $resumoItens[] = array('nome' => $itensPorId[$itemId]['nome'], 'qtd' => $qtd, 'unidade' => $itensPorId[$itemId]['unidade']);
        }
    }
    $vidasTotalPreview = round($vidasTotalPreview, 1);
}

require __DIR__ . '/includes/layout_top.php';
?>
<div class="fw-bold mb-1" style="font-size:1.35rem">Direcionamento de Doações</div>
<div class="text-muted mb-4">Selecione a instituição e os itens a serem doados</div>

<?php if ($erro) { ?>
  <div class="alert alert-danger small"><?php echo e($erro); ?></div>
<?php } ?>

<div class="d-flex align-items-center gap-2 mb-4">
  <?php
  $steps = array(1 => 'Instituição', 2 => 'Itens', 3 => 'Confirmação');
  $i = 0;
  foreach ($steps as $num => $label) {
      $i++;
      $active = $num === $wizard['step'];
      $done = $num < $wizard['step'];
      $bg = ($active || $done) ? 'var(--blue)' : '#EEF0F3';
      $color = ($active || $done) ? '#fff' : '#98A2B3';
      $textColor = $active ? 'var(--navy)' : '#98A2B3';
  ?>
    <div class="d-flex align-items-center gap-2">
      <div class="wizard-num" style="background:<?php echo $bg; ?>;color:<?php echo $color; ?>"><?php echo $num; ?></div>
      <div class="small fw-semibold" style="color:<?php echo $textColor; ?>"><?php echo $label; ?></div>
    </div>
    <?php if ($i < count($steps)) { ?><div class="wizard-line"></div><?php } ?>
  <?php } ?>
</div>

<?php if ($wizard['step'] === 1) { ?>
  <div class="card p-3">
    <div class="fw-semibold mb-3" style="font-size:.9rem">Selecione a instituição beneficiada</div>
    <form method="post">
      <input type="hidden" name="acao" value="ir_passo2">
      <div class="row g-2 mb-3">
        <?php foreach ($instituicoes as $i) { ?>
          <div class="col-md-6">
            <label class="inst-card d-block p-2 px-3">
              <input type="radio" name="instituicao_id" value="<?php echo $i['id']; ?>" class="form-check-input me-2" <?php echo ($wizard['instituicao_id'] == $i['id']) ? 'checked' : ''; ?>>
              <span class="fw-semibold" style="font-size:.9rem"><?php echo e($i['nome']); ?></span>
              <div class="text-muted small ms-4"><?php echo e($i['bairro']) . ' · ' . e($i['tipo']); ?></div>
            </label>
          </div>
        <?php } ?>
      </div>
      <div class="text-end"><button type="submit" class="btn btn-sigis">Continuar</button></div>
    </form>
  </div>
<?php } elseif ($wizard['step'] === 2) { ?>
  <div class="card p-3">
    <div class="fw-semibold mb-1" style="font-size:.9rem">Selecione os itens e quantidades</div>
    <div class="text-muted small mb-3">Destinado a: <strong class="text-dark"><?php echo e($instituicaoSelecionada ? $instituicaoSelecionada['nome'] : '—'); ?></strong></div>
    <form method="post">
      <div class="table-responsive">
        <table class="table table-sigis">
          <thead><tr><th>Item</th><th>Disponível</th><th>Qtd. a doar</th><th>Vidas (mult.)</th></tr></thead>
          <tbody>
            <?php foreach ($estoqueItens as $it) {
                $qtdAtual = isset($wizard['selecoes'][$it['item_id']]) ? $wizard['selecoes'][$it['item_id']] : 0;
            ?>
              <tr>
                <td class="fw-medium text-dark"><?php echo e($it['nome']); ?></td>
                <td><?php echo fmt_num($it['saldo'], 2) . ' ' . e($it['unidade']); ?></td>
                <td><input type="number" step="0.001" min="0" max="<?php echo (float)$it['saldo']; ?>" name="qtd[<?php echo $it['item_id']; ?>]" value="<?php echo $qtdAtual ?: ''; ?>" class="form-control form-control-sm" style="width:110px"></td>
                <td class="fw-bold" style="color:var(--blue)"><?php echo fmt_num($it['multiplicador'], 3); ?> / <?php echo e($it['unidade']); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex justify-content-between">
        <button type="submit" formnovalidate name="acao" value="voltar_passo1" class="btn btn-sigis-outline">Voltar</button>
        <button type="submit" name="acao" value="ir_passo3" class="btn btn-sigis">Revisar doação</button>
      </div>
    </form>
  </div>
<?php } elseif ($wizard['step'] === 3) { ?>
  <div class="card p-4">
    <div class="fw-semibold mb-3" style="font-size:.9rem">Resumo da doação</div>
    <div class="row mb-3">
      <div class="col-6">
        <div class="text-uppercase small text-muted fw-semibold mb-1">Instituição</div>
        <div class="fw-semibold text-dark"><?php echo e($instituicaoSelecionada ? $instituicaoSelecionada['nome'] : '—'); ?></div>
      </div>
      <div class="col-6">
        <div class="text-uppercase small text-muted fw-semibold mb-1">Itens selecionados</div>
        <div class="fw-semibold text-dark"><?php echo count($resumoItens); ?></div>
      </div>
    </div>
    <div class="d-flex flex-column gap-2 mb-3">
      <?php foreach ($resumoItens as $r) { ?>
        <div class="d-flex justify-content-between px-3 py-2 rounded small" style="background:var(--bg)">
          <div class="fw-medium text-dark"><?php echo e($r['nome']); ?></div>
          <div class="text-secondary"><?php echo fmt_num($r['qtd'], 2) . ' ' . e($r['unidade']); ?></div>
        </div>
      <?php } ?>
    </div>
    <div class="summary-box d-flex justify-content-between align-items-center p-3 mb-3">
      <div class="fw-semibold small" style="color:var(--accent-text)">Total de vidas impactadas nesta doação</div>
      <div class="fw-bold fs-3" style="color:var(--blue)"><?php echo fmt_num($vidasTotalPreview, 1); ?></div>
    </div>
    <form method="post" class="d-flex justify-content-between">
      <button type="submit" formnovalidate name="acao" value="voltar_passo2" class="btn btn-sigis-outline">Voltar</button>
      <button type="submit" name="acao" value="confirmar" class="btn btn-sigis-green">Confirmar e Gerar Termo de Doação</button>
    </form>
  </div>
<?php } ?>
<?php require __DIR__ . '/includes/layout_bottom.php'; ?>
