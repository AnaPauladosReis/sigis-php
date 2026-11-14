<?php
/**
 * Move o saldo de um item em estoque de forma atomica: o saldo minimo (0) e
 * verificado no proprio UPDATE, nao numa leitura anterior, para que duas
 * movimentacoes concorrentes nao deixem o saldo negativo (RN015).
 *
 * $tipo/$refTipo/$refId ainda nao sao persistidos (a tabela de
 * movimentacoes entra na tarefa 5.4), mas ja ficam na assinatura para nao
 * precisar mudar as chamadas depois.
 */
function estoque_movimentar(PDO $pdo, int $itemId, float $delta, string $tipo, ?string $refTipo = null, ?int $refId = null): void {
    $stmt = $pdo->prepare('UPDATE estoque SET saldo = saldo + ? WHERE item_id = ? AND saldo + ? >= 0');
    $stmt->execute(array($delta, $itemId, $delta));
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('Saldo insuficiente em estoque para o item ' . $itemId . '.');
    }
}
