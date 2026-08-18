<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

$queries = array(
    'projetos' => array(
        'sql' => "SELECT p.nome AS Projeto, g.nome AS Grupo, p.status AS Status, p.descricao AS Descricao FROM projetos p LEFT JOIN grupos_impacto g ON g.id = p.grupo_id ORDER BY p.nome",
        'arquivo' => 'projetos_sociais.csv',
    ),
    'instituicoes' => array(
        'sql' => "SELECT nome AS Instituicao, cnpj AS CNPJ, bairro AS Bairro, responsavel AS Responsavel, tipo AS Tipo, IF(acolhimento,'Sim','Nao') AS Acolhimento FROM instituicoes ORDER BY nome",
        'arquivo' => 'instituicoes_atendidas.csv',
    ),
    'entradas' => array(
        'sql' => "SELECT e.data_entrada AS Data, e.origem AS Origem, p.nome AS Projeto, i.nome AS Item, e.quantidade AS Quantidade, i.unidade AS Unidade, e.peso AS Peso_kg
                  FROM entradas e LEFT JOIN projetos p ON p.id = e.projeto_id JOIN itens i ON i.id = e.item_id ORDER BY e.data_entrada DESC",
        'arquivo' => 'entradas_materiais.csv',
    ),
    'producao' => array(
        'sql' => "SELECT pr.data_producao AS Data, p.nome AS Projeto, i.nome AS Material_Consumido, pr.qtd_consumida AS Qtd_Consumida_kg, pr.produto_gerado AS Produto_Gerado, pr.quantidade_gerada AS Quantidade_Gerada, pr.unidade_gerada AS Unidade, pr.residuo_kg AS Residuo_kg
                  FROM producoes pr LEFT JOIN projetos p ON p.id = pr.projeto_id LEFT JOIN itens i ON i.id = pr.item_consumido_id ORDER BY pr.data_producao DESC",
        'arquivo' => 'producao_social.csv',
    ),
    'doacoes' => array(
        'sql' => "SELECT d.data_doacao AS Data, i.nome AS Instituicao, p.nome AS Projeto, d.vidas_impactadas AS Vidas_Impactadas, t.numero AS Termo
                  FROM doacoes d JOIN instituicoes i ON i.id = d.instituicao_id LEFT JOIN projetos p ON p.id = d.projeto_id LEFT JOIN termos t ON t.doacao_id = d.id
                  ORDER BY d.data_doacao DESC",
        'arquivo' => 'doacoes_realizadas.csv',
    ),
    'vidas' => array(
        'sql' => "SELECT i.nome AS Instituicao, COUNT(d.id) AS Qtd_Doacoes, COALESCE(SUM(d.vidas_impactadas),0) AS Total_Vidas_Impactadas
                  FROM instituicoes i LEFT JOIN doacoes d ON d.instituicao_id = i.id
                  GROUP BY i.id, i.nome ORDER BY Total_Vidas_Impactadas DESC",
        'arquivo' => 'vidas_impactadas.csv',
    ),
);

if (!isset($queries[$tipo])) {
    flash_set('Relatório inválido.', 'danger');
    redirect('evidencias.php');
}

$def = $queries[$tipo];
$stmt = $pdo->query($def['sql']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $def['arquivo'] . '"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF"); // BOM para abrir corretamente no Excel
if (count($rows)) {
    fputcsv($out, array_keys($rows[0]), ';');
    foreach ($rows as $row) {
        fputcsv($out, $row, ';');
    }
} else {
    fputcsv($out, array('Sem dados para este relatório'), ';');
}
fclose($out);
exit;
