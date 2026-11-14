<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
exigir_administrador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('estoque.php');
}
csrf_check();

$itemId = (int)$_POST['item_id'];
$minimo = (float)$_POST['minimo'];

if ($minimo < 0) {
    flash_set('Informe um estoque mínimo válido.', 'danger');
    redirect('estoque.php');
}

$stmt = $pdo->prepare('UPDATE estoque SET minimo = ? WHERE item_id = ?');
$stmt->execute(array($minimo, $itemId));

flash_set('Estoque mínimo atualizado.');
redirect('estoque.php');
