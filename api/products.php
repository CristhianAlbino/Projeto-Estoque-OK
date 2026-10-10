<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}
require_valid_csrf();
if (!is_staff()) {
    http_response_code(403);
    exit('Acesso restrito aos funcionários.');
}

try {
    $pdo = db();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'create') {
        $name = trim((string) ($_POST['nome'] ?? ''));
        $category = trim((string) ($_POST['categoria'] ?? 'Outros'));
        $model = trim((string) ($_POST['modelo'] ?? ''));
        $year = trim((string) ($_POST['ano'] ?? ''));
        $rawPrice = str_replace(',', '.', trim((string) ($_POST['preco'] ?? '')));
        $qty = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT);
        if ($name === '' || strlen($name) > 120 || $category === '' || strlen($category) > 60
            || strlen($model) > 100 || strlen($year) > 9
            || !is_numeric($rawPrice) || (float) $rawPrice < 0 || (float) $rawPrice > 999999.99
            || $qty === false || $qty < 0 || $qty > 1000000) {
            redirect_to('../estoque.php?status=dados_invalidos');
        }
        $statement = $pdo->prepare(
            'INSERT INTO pecas (nome, categoria, modelo, ano, preco, quantidade_estoque)
             VALUES (:nome, :categoria, :modelo, :ano, :preco, :quantidade)'
        );
        $statement->execute([
            'nome' => $name, 'categoria' => $category, 'modelo' => $model,
            'ano' => $year, 'preco' => number_format((float) $rawPrice, 2, '.', ''),
            'quantidade' => $qty,
        ]);
        redirect_to('../estoque.php?status=peca_criada');
    }
    if ($action === 'adjust') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $delta = filter_var($_POST['delta'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1 || $delta === false || $delta === 0
            || abs($delta) > 10000) {
            redirect_to('../estoque.php?status=dados_invalidos');
        }
        // Atualização atômica: duas operações concorrentes não podem gerar saldo negativo.
        $statement = $pdo->prepare(
            'UPDATE pecas SET quantidade_estoque = CAST(quantidade_estoque AS SIGNED) + :adicionar
             WHERE id = :id AND CAST(quantidade_estoque AS SIGNED) + :verificar >= 0'
        );
        $statement->execute([
            'adicionar' => $delta, 'id' => $id, 'verificar' => $delta,
        ]);
        redirect_to('../estoque.php?status=' . ($statement->rowCount() === 1 ? 'estoque_atualizado' : 'saldo_insuficiente'));
    }
    http_response_code(400);
    exit('Ação desconhecida.');
} catch (Throwable $error) {
    error_log('Erro ao gerenciar estoque: ' . $error->getMessage());
    redirect_to('../estoque.php?status=erro_servidor');
}
