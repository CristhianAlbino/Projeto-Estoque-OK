<?php
/** @var string $active */
$user = current_user();
?>
<header class="site-header">
  <div class="container navbar">
    <a class="brand" href="index.php" aria-label="Auto Peças - início"><span class="brand-mark">A</span><span>AUTO<span class="brand-accent">PEÇAS</span><small>CATÁLOGO & ESTOQUE</small></span></a>
    <nav class="nav-links" aria-label="Navegação principal">
      <a href="index.php" <?= ($active ?? '') === 'home' ? 'aria-current="page"' : '' ?>>Início</a>
      <a href="shopping.php" <?= ($active ?? '') === 'shop' ? 'aria-current="page"' : '' ?>>Peças</a>
      <?php if (is_staff()): ?><a href="estoque.php" <?= ($active ?? '') === 'stock' ? 'aria-current="page"' : '' ?>>Gerenciar estoque</a><?php endif; ?>
    </nav>
    <div class="nav-actions">
      <?php if ($user): ?>
        <span class="user-greeting">Olá, <?= e(explode(' ', trim($user['nome']))[0]) ?></span>
        <form action="api/logout.php" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <button class="button button-ghost button-small" type="submit">Sair</button>
        </form>
      <?php else: ?>
        <a class="button button-outline button-small" href="login.php">Entrar / Cadastrar</a>
      <?php endif; ?>
    </div>
  </div>
</header>
