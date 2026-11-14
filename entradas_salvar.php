<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('entradas.php');
}
csrf_check();

$data = $_POST['data_entrada'];
$origemId = !empty($_POST['origem_id']) ? (int)$_POST['origem_id'] : null;
$projetoId = !empty($_POST['projeto_id']) ? (int)$_POST['projeto_id'] : null;
$itemId = (int)$_POST['item_id'];
$quantidade = (float)$_POST['quantidade'];
$peso = ($_POST['peso'] !== '') ? (float)$_POST['peso'] : null;

if (!validar_data($data)) {
    flash_set('Informe uma data válida (não futura).', 'danger');
    redirect('entradas.php');
}
if ($origemId === null || !existe_id($pdo, 'origens', $origemId)) {
    flash_set('Selecione uma origem cadastrada.', 'danger');
    redirect('entradas.php');
}
if (!validar_numero_positivo($quantidade)) {
    flash_set('Informe uma quantidade válida.', 'danger');
    redirect('entradas.php');
}
if (!existe_id($pdo, 'itens', $itemId)) {
    flash_set('Item inválido.', 'danger');
    redirect('entradas.php');
}
if ($projetoId === null || !existe_id($pdo, 'projetos', $projetoId)) {
    flash_set('Selecione um projeto.', 'danger');
    redirect('entradas.php');
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT nome FROM origens WHERE id = ?');
    $stmt->execute(array($origemId));
    $origemNome = $stmt->fetchColumn();

    $stmt = $pdo->prepare('INSERT INTO entradas (data_entrada, origem, origem_id, projeto_id, item_id, quantidade, peso, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(array($data, $origemNome, $origemId, $projetoId, $itemId, $quantidade, $peso, $_SESSION['user_id']));

    $entradaId = (int)$pdo->lastInsertId();
    estoque_movimentar($pdo, $itemId, $quantidade, 'entrada', 'entrada', $entradaId);

    $pdo->commit();
    flash_set('Entrada registrada e estoque atualizado.');
} catch (Exception $ex) {
    $pdo->rollBack();
    error_log($ex->getMessage());
    flash_set('Não foi possível registrar a entrada. Tente novamente.', 'danger');
}

redirect('entradas.php');
