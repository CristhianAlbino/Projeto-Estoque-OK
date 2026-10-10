<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
if (!is_staff()) {
    http_response_code(403);
    exit('Acesso restrito a funcionários. <a href="login.php">Entrar</a>');
}
$active = 'stock';
$products = [];
$databaseError = false;
try {
    $products = db()->query('SELECT id, nome, categoria, modelo, ano, preco, quantidade_estoque FROM pecas ORDER BY nome LIMIT 500')->fetchAll();
} catch (Throwable $error) {
    error_log('Erro ao carregar estoque: ' . $error->getMessage());
    $databaseError = true;
}
$messages = [
    'peca_criada' => 'Peça cadastrada com sucesso.',
    'estoque_atualizado' => 'Quantidade atualizada com sucesso.',
    'saldo_insuficiente' => 'Não foi possível ajustar a quantidade. Confira o saldo e o código.',
    'dados_invalidos' => 'Dados inválidos. Revise os campos e tente novamente.',
    'erro_servidor' => 'Falha ao salvar. Confira a configuração do banco.',
];
$status = (string) ($_GET['status'] ?? '');
$totalUnits = array_sum(array_map(static fn(array $p): int => (int) $p['quantidade_estoque'], $products));
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gerenciar estoque | AutoPeças</title><link rel="stylesheet" href="assets/css/app.css"></head>
<body>
  <?php require __DIR__ . '/includes/nav.php'; ?>
  <main class="container page-main">
    <div class="page-title-row"><div><span class="eyebrow">ÁREA RESTRITA · FUNCIONÁRIOS</span><h1>Controle de estoque</h1><p>Cadastre peças e registre entradas ou saídas.</p></div></div>
    <?php if (isset($messages[$status])): ?><div class="notice" role="status"><?= e($messages[$status]) ?></div><?php endif; ?>
    <?php if ($databaseError): ?><div class="notice" role="alert">Conexão com banco indisponível. Confira o README.</div><?php endif; ?>
    <div class="stats-grid"><div class="stat-card"><span>Peças cadastradas</span><strong><?= count($products) ?></strong></div><div class="stat-card"><span>Unidades disponíveis</span><strong><?= $totalUnits ?></strong></div><div class="stat-card"><span>Itens sem estoque</span><strong><?= count(array_filter($products, static fn(array $p): bool => (int) $p['quantidade_estoque'] === 0)) ?></strong></div></div>
    <div class="stock-layout">
      <section class="form-card" aria-labelledby="new-part"><span class="eyebrow">CADASTRO</span><h2 id="new-part">Nova peça</h2><p>Inclua um item no catálogo e informe o saldo inicial.</p>
        <form action="api/products.php" method="post">
          <input type="hidden" name="action" value="create"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <label for="name">Nome da peça</label><input id="name" name="nome" required maxlength="120" placeholder="Ex.: Filtro de óleo">
          <label for="category">Categoria</label><select id="category" name="categoria"><option>Motor</option><option>Rodas</option><option>Funilaria</option><option>Pintura</option><option>Elétrica</option><option>Outros</option></select>
          <label for="model">Modelo / Compatibilidade</label><input id="model" name="modelo" maxlength="100" placeholder="Ex.: Fiat Uno">
          <label for="year">Ano</label><input id="year" name="ano" maxlength="9" placeholder="Ex.: 2010-2014">
          <div class="input-grid"><div><label for="price">Preço (R$)</label><input id="price" name="preco" inputmode="decimal" required placeholder="120,50"></div><div><label for="qty">Quantidade</label><input id="qty" name="quantidade" type="number" min="0" max="1000000" value="0" required></div></div>
          <button class="button button-primary button-full" type="submit">Cadastrar peça →</button>
        </form>
      </section>
      <section class="stock-panel" aria-labelledby="stock-list"><div class="panel-heading"><div><span class="eyebrow">INVENTÁRIO</span><h2 id="stock-list">Peças cadastradas</h2></div><span class="stock-count"><?= count($products) ?> registros</span></div>
      <?php if (!$products): ?><div class="empty-state"><h3>Sem peças cadastradas</h3><p>Use o formulário ao lado para começar.</p></div><?php else: ?>
        <div class="table-scroll"><table class="stock-table"><thead><tr><th>Peça</th><th>Categoria</th><th>Preço</th><th>Saldo</th><th>Ajustar</th></tr></thead><tbody>
          <?php foreach ($products as $item): ?><tr>
            <td><strong><?= e($item['nome']) ?></strong><small><?= e(trim($item['modelo'] . ' ' . $item['ano'])) ?></small></td>
            <td><?= e($item['categoria']) ?></td><td>R$ <?= number_format((float) $item['preco'], 2, ',', '.') ?></td><td><span class="stock-pill <?= (int) $item['quantidade_estoque'] > 0 ? 'in-stock' : 'out-stock' ?>"><?= (int) $item['quantidade_estoque'] ?></span></td>
            <td><form class="adjust-form" action="api/products.php" method="post"><input type="hidden" name="action" value="adjust"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><label class="visually-hidden" for="delta-<?= (int) $item['id'] ?>">Ajuste de quantidade de <?= e($item['nome']) ?></label><input id="delta-<?= (int) $item['id'] ?>" type="number" name="delta" step="1" min="-10000" max="10000" required placeholder="+10 / -2"><button class="button button-outline button-small" type="submit">Salvar</button></form></td>
          </tr><?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
      </section>
    </div>
    <p class="catalog-disclaimer">Use valores positivos para entradas e negativos para saídas. Saldos abaixo de zero são bloqueados.</p>
  </main>
  <?php require __DIR__ . '/includes/footer.php'; ?>
</body></html>
