<?php
/**
 * Cabecalho comum a todas as telas internas (pos-login).
 * Espera (opcional) que a pagina que o incluiu ja tenha definido $pageTitle.
 */
require_login();
$u = current_user();
$active = active_screen();

$screenTitles = array(
    'dashboard' => 'Dashboard administrativo',
    'projetos' => 'Projetos Sociais',
    'grupos' => 'Grupos de Impacto & Itens',
    'instituicoes' => 'Instituições Beneficiadas',
    'entradas' => 'Entradas de Materiais',
    'estoque' => 'Estoque de Itens',
    'producao' => 'Produção Social',
    'doacoes' => 'Direcionamento de Doações',
    'termo' => 'Termo de Doação',
    'evidencias' => 'Evidências & Relatórios',
);
if (empty($pageTitle)) {
    $pageTitle = isset($screenTitles[$active]) ? $screenTitles[$active] : 'SIGIS';
}

$navConfig = array(
    array('label' => 'Painel', 'items' => array(
        array('id' => 'dashboard', 'label' => 'Dashboard'),
    )),
    array('label' => 'Cadastros', 'items' => array(
        array('id' => 'projetos', 'label' => 'Projetos Sociais'),
        array('id' => 'grupos', 'label' => 'Grupos de Impacto'),
        array('id' => 'instituicoes', 'label' => 'Instituições Beneficiadas'),
    )),
    array('label' => 'Operações', 'items' => array(
        array('id' => 'entradas', 'label' => 'Entradas de Materiais'),
        array('id' => 'estoque', 'label' => 'Estoque'),
        array('id' => 'producao', 'label' => 'Produção Social'),
        array('id' => 'doacoes', 'label' => 'Direcionamento de Doações'),
    )),
    array('label' => 'Documentos', 'items' => array(
        array('id' => 'termo', 'label' => 'Termo de Doação'),
        array('id' => 'evidencias', 'label' => 'Evidências & Relatórios'),
    )),
);

$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SIGIS · <?php echo e($pageTitle); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
  <nav class="sigis-sidebar flex-column p-3 d-flex">
    <div class="d-flex align-items-center gap-2 mb-4 px-1">
      <div class="brand-mark sm"></div>
      <div class="text-white fw-bold fs-6">SIGIS</div>
    </div>
    <?php foreach ($navConfig as $group) { ?>
      <div class="nav-group-label"><?php echo e($group['label']); ?></div>
      <div class="mb-3">
        <?php foreach ($group['items'] as $item) { ?>
          <a class="nav-link <?php echo $active === $item['id'] ? 'active' : ''; ?>" href="<?php echo e($item['id']); ?>.php">
            <span class="dot"></span><?php echo e($item['label']); ?>
          </a>
        <?php } ?>
      </div>
    <?php } ?>
    <div class="mt-auto pt-3 border-top border-secondary-subtle d-flex align-items-center gap-2">
      <div class="sigis-avatar">AD</div>
      <div style="min-width:0">
        <div class="text-white small fw-semibold text-truncate"><?php echo e($u['nome']); ?></div>
        <a href="logout.php" class="small" style="color:#8FA8D6">Sair</a>
      </div>
    </div>
  </nav>

  <div class="flex-fill d-flex flex-column" style="min-width:0">
    <div class="sigis-topbar d-flex align-items-center justify-content-between px-4">
      <div class="fw-semibold" style="font-size:.95rem"><?php echo e($pageTitle); ?></div>
      <div class="d-flex align-items-center gap-3">
        <form class="d-none d-md-block" action="#" onsubmit="return false">
          <input type="text" class="form-control form-control-sm" placeholder="Buscar em SIGIS..." style="width:220px">
        </form>
        <div class="text-muted small"><?php echo date('d M Y'); ?></div>
      </div>
    </div>

    <div class="sigis-content flex-fill">
      <?php if ($flash) { ?>
        <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
          <?php echo e($flash['msg']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php } ?>
