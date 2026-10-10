<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$active = 'shop';
$q = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['categoria'] ?? ''));
$q = substr($q, 0, 100);
$category = substr($category, 0, 60);
$products = [];
$categories = [];
$databaseError = false;
try {
    $pdo = db();
    $categories = $pdo->query("SELECT DISTINCT categoria FROM pecas WHERE ativo = 1 ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
    $where = ['ativo = 1'];
    $params = [];
    if ($q !== '') {
        $where[] = '(nome LIKE :termo OR modelo LIKE :modelo)';
        $params['termo'] = '%' . $q . '%';
        $params['modelo'] = '%' . $q . '%';
    }
    if ($category !== '') {
        $where[] = 'categoria = :categoria';
        $params['categoria'] = $category;
    }
    $statement = $pdo->prepare('SELECT id, nome, categoria, modelo, ano, preco, quantidade_estoque FROM pecas WHERE ' . implode(' AND ', $where) . ' ORDER BY nome LIMIT 100');
    $statement->execute($params);
    $products = $statement->fetchAll();
} catch (Throwable $error) {
    error_log('Catálogo indisponível: ' . $error->getMessage());
    $databaseError = true;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogo de peças | AutoPeças</title>
  <link rel="stylesheet" href="assets/css/app.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body>
  <?php require __DIR__ . '/includes/nav.php'; ?>
  <main class="container page-main">
    <div class="page-title-row">
      <div><span class="eyebrow">CATÁLOGO DE AUTOPEÇAS</span><h1>Encontre a peça ideal.</h1><p>Consulte disponibilidade e organize sua seleção.</p></div>
      <button class="button button-outline cart-toggle" type="button" id="open-cart">Seleção <span class="cart-count" id="cart-count">0</span></button>
    </div>
    <form class="catalog-filters" action="shopping.php" method="get" role="search">
      <label class="visually-hidden" for="search">Buscar peças</label>
      <input type="search" id="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por peça ou modelo...">
      <label class="visually-hidden" for="category">Categoria</label>
      <select id="category" name="categoria"><option value="">Todas as categorias</option>
        <?php foreach ($categories as $item): ?><option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option><?php endforeach; ?>
      </select>
      <button class="button button-primary" type="submit">Buscar</button>
    </form>
    <?php if ($databaseError): ?>
      <div class="notice" role="alert">Catálogo indisponível. Configure a conexão MySQL conforme o README.</div>
    <?php elseif (!$products): ?>
      <div class="empty-state"><div class="empty-symbol">⌕</div><h2>Nenhuma peça encontrada</h2><p>Experimente outro filtro ou aguarde o cadastro de produtos.</p><a href="shopping.php" class="button button-outline">Limpar filtros</a></div>
    <?php else: ?>
      <div class="catalog-summary"><span><?= count($products) ?> peça(s) encontrada(s)</span><small>Disponibilidade sujeita a alterações.</small></div>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <article class="product-card">
            <div class="product-illustration"><span aria-hidden="true">⚙</span><small><?= e($product['categoria']) ?></small></div>
            <div class="product-details">
              <h2><?= e($product['nome']) ?></h2>
              <?php if ($product['modelo'] !== '' || $product['ano'] !== ''): ?><p><?= e(trim($product['modelo'] . ' ' . $product['ano'])) ?></p><?php endif; ?>
              <div class="product-bottom">
                <strong>R$ <?= number_format((float) $product['preco'], 2, ',', '.') ?></strong>
                <span class="stock-pill <?= (int) $product['quantidade_estoque'] > 0 ? 'in-stock' : 'out-stock' ?>"><?= (int) $product['quantidade_estoque'] ?> em estoque</span>
              </div>
              <button type="button" class="button button-primary button-full add-to-selection"
                data-id="<?= (int) $product['id'] ?>" data-name="<?= e($product['nome']) ?>"
                data-price="<?= e((string) $product['preco']) ?>" data-stock="<?= (int) $product['quantidade_estoque'] ?>"
                <?= (int) $product['quantidade_estoque'] === 0 ? 'disabled' : '' ?>>Adicionar à seleção +</button>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <p class="catalog-disclaimer">A seleção é uma simulação local no navegador. O sistema ainda não realiza compras nem pagamentos.</p>
  </main>
  <div class="cart-backdrop" id="cart-backdrop" hidden></div>
  <aside class="cart-drawer" id="cart-drawer" aria-label="Sua seleção" aria-hidden="true">
    <div class="drawer-top"><h2>Sua seleção</h2><button class="icon-button" id="close-cart" type="button" aria-label="Fechar seleção">✕</button></div>
    <p class="drawer-helper">Itens guardados somente neste navegador.</p>
    <div id="cart-items" class="cart-items"></div>
    <div class="drawer-bottom"><div class="total-row"><span>Total estimado</span><strong id="cart-total">R$ 0,00</strong></div><p>Não é um pedido confirmado.</p><button id="clear-cart" class="button button-outline button-full" type="button">Limpar seleção</button></div>
  </aside>
  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
