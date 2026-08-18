<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('evidencias.php');
}

$instituicaoId = !empty($_POST['instituicao_id']) ? (int)$_POST['instituicao_id'] : null;
$data = $_POST['data_evidencia'];

if (empty($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    flash_set('Selecione um arquivo de imagem válido.', 'danger');
    redirect('evidencias.php');
}

$permitidos = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
$tipo = mime_content_type($_FILES['arquivo']['tmp_name']);
if (!in_array($tipo, $permitidos, true)) {
    flash_set('Formato de arquivo não suportado. Envie JPG, PNG, WEBP ou GIF.', 'danger');
    redirect('evidencias.php');
}

$ext = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
$ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
$nomeArquivo = 'evidencia-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.' . strtolower($ext);
$destino = __DIR__ . '/uploads/evidencias/' . $nomeArquivo;

if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
    flash_set('Falha ao salvar o arquivo no servidor.', 'danger');
    redirect('evidencias.php');
}

$stmt = $pdo->prepare('INSERT INTO evidencias (instituicao_id, arquivo, data_evidencia) VALUES (?, ?, ?)');
$stmt->execute(array($instituicaoId, $nomeArquivo, $data));

flash_set('Evidência enviada com sucesso.');
redirect('evidencias.php');
