<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

// Campos fixos do cabeçalho/rodapé do modelo oficial "Declaração Doação de Itens (Parceiros)" do SESI.
// Alterar aqui caso a regional, a cidade de emissão ou o(a) signatário(a) mudem.
define('TERMO_TIPO', 'Parceiros');
define('TERMO_REGIONAL', 'Regional Norte Nordeste');
define('TERMO_CIDADE', 'Joinville');
define('TERMO_SIGNATARIO_NOME', 'Simone Brandão');
define('TERMO_SIGNATARIO_CARGO', 'Interlocutora da Responsabilidade Social e Cultura SESI');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$termo = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM termos WHERE id = ?');
    $stmt->execute(array($id));
    $termo = $stmt->fetch();
}

$doacao = null;
$itens = array();
if ($termo) {
    $stmt = $pdo->prepare('SELECT d.*, i.nome as instituicao_nome, i.cnpj as instituicao_cnpj, p.nome as projeto_nome FROM doacoes d JOIN instituicoes i ON i.id = d.instituicao_id LEFT JOIN projetos p ON p.id = d.projeto_id WHERE d.id = ?');
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

<?php if ($id > 0 && !$termo) { ?>
  <div class="card p-4 text-center text-muted no-print">Termo não encontrado. Confira em <a href="doacoes.php">Direcionamento de Doações</a>.</div>
<?php } elseif ($termo) { ?>
  <div class="termo-declaracao mb-4">
    <img src="assets/img/logo-sesi.png" alt="SESI" class="termo-logo">

    <div class="termo-titulo">DECLARAÇÃO DOAÇÃO DE ITENS (<?php echo e(TERMO_TIPO); ?>)&nbsp;&nbsp;N° <?php echo e($termo['numero']); ?></div>
    <div class="termo-titulo"><?php echo e(TERMO_REGIONAL); ?></div>

    <div class="termo-data"><?php echo e(TERMO_CIDADE); ?>, <?php echo e(fmt_data_extenso($termo['emitido_em'])); ?></div>

    <div class="termo-label">INSTITUIÇÃO BENEFICIADA:</div>
    <div class="termo-inst-nome"><?php echo e($doacao['instituicao_nome']); ?></div>
    <div class="termo-cnpj">CNPJ: <?php echo e($doacao['instituicao_cnpj']); ?></div>

    <p class="termo-texto">
      Declaro para os devidos fins que recebi da Gerência de Responsabilidade Social e Cultura do SESI-SC,
      através do Programa Oportuniza, os itens relacionados abaixo, sendo estes utilizados exclusivamente nas
      atividades na Instituição e para seu público atendido.
    </p>

    <table class="termo-table mb-2">
      <thead><tr><th style="width:140px">Quantidade</th><th>Descrição do Item</th></tr></thead>
      <tbody>
        <?php foreach ($itens as $it) { ?>
          <tr><td><?php echo fmt_qtd_termo($it['quantidade'], $it['unidade']); ?></td><td><?php echo e($it['nome']); ?></td></tr>
        <?php } ?>
      </tbody>
    </table>

    <table class="termo-table termo-table-inline mb-3">
      <tr><th>Quantidade de pessoas impactadas:</th><td><?php echo fmt_num($doacao['vidas_impactadas'], 0); ?></td></tr>
    </table>

    <table class="termo-table termo-field-table mb-4">
      <tr><th colspan="2" class="termo-field-header">Dados do Representante Legal ou Outorgado:</th></tr>
      <tr><td class="termo-field-label">Nome Completo:</td><td>&nbsp;</td></tr>
      <tr><td class="termo-field-label">CPF:</td><td>&nbsp;</td></tr>
      <tr><td class="termo-field-label">Telefone de contato:</td><td>&nbsp;</td></tr>
      <tr><td class="termo-field-label">Assinatura:</td><td>&nbsp;</td></tr>
    </table>

    <div class="termo-assinatura">
      <div class="termo-assinatura-linha"></div>
      <div class="termo-assinatura-nome"><?php echo e(TERMO_SIGNATARIO_NOME); ?></div>
      <div class="termo-assinatura-cargo"><?php echo e(TERMO_SIGNATARIO_CARGO); ?></div>
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
            <td class="pe-3"><a href="termo.php?id=<?php echo $t['id']; ?>" target="_blank" rel="noopener">Ver</a></td>
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
