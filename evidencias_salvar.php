<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('evidencias.php');
}
csrf_check();

$instituicaoId = !empty($_POST['instituicao_id']) ? (int)$_POST['instituicao_id'] : null;
$data = $_POST['data_evidencia'];

if (empty($_FILES['arquivo'])) {
    flash_set('Selecione um arquivo de imagem válido.', 'danger');
    redirect('evidencias.php');
}

$errosUpload = array(
    UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor.',
    UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido pelo formulário.',
    UPLOAD_ERR_PARTIAL => 'O upload foi interrompido. Tente novamente.',
    UPLOAD_ERR_NO_FILE => 'Selecione um arquivo de imagem válido.',
    UPLOAD_ERR_NO_TMP_DIR => 'Falha ao salvar o arquivo no servidor.',
    UPLOAD_ERR_CANT_WRITE => 'Falha ao salvar o arquivo no servidor.',
    UPLOAD_ERR_EXTENSION => 'Upload bloqueado por uma extensão do servidor.',
);
$erroUpload = $_FILES['arquivo']['error'];
if ($erroUpload !== UPLOAD_ERR_OK) {
    $msg = isset($errosUpload[$erroUpload]) ? $errosUpload[$erroUpload] : 'Falha ao enviar o arquivo.';
    flash_set($msg, 'danger');
    redirect('evidencias.php');
}

$tamanhoMaximo = 5 * 1024 * 1024; // 5 MB
if ($_FILES['arquivo']['size'] > $tamanhoMaximo) {
    flash_set('Arquivo muito grande. O tamanho máximo é 5 MB.', 'danger');
    redirect('evidencias.php');
}

$extensoesPorMime = array(
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
);
$tipo = mime_content_type($_FILES['arquivo']['tmp_name']);
if (!isset($extensoesPorMime[$tipo]) || !@getimagesize($_FILES['arquivo']['tmp_name'])) {
    flash_set('Formato de arquivo não suportado. Envie JPG, PNG, WEBP ou GIF.', 'danger');
    redirect('evidencias.php');
}

$nomeArquivo = 'evidencia-' . bin2hex(random_bytes(8)) . '.' . $extensoesPorMime[$tipo];
$destino = __DIR__ . '/uploads/evidencias/' . $nomeArquivo;

if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
    flash_set('Falha ao salvar o arquivo no servidor.', 'danger');
    redirect('evidencias.php');
}

$stmt = $pdo->prepare('INSERT INTO evidencias (instituicao_id, arquivo, data_evidencia) VALUES (?, ?, ?)');
$stmt->execute(array($instituicaoId, $nomeArquivo, $data));

flash_set('Evidência enviada com sucesso.');
redirect('evidencias.php');
