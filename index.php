<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$active = 'home';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Explore peças automotivas e gerencie seu estoque em um só lugar.">
  <title>AutoPeças | Início</title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <?php require __DIR__ . '/includes/nav.php'; ?>
  <main>
    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-copy">
          <span class="eyebrow"><span class="pulse"></span> PEÇAS & ESTOQUE, EM UM SÓ LUGAR</span>
          <h1>Sua próxima peça está <span>mais perto.</span></h1>
          <p>Encontre componentes para seu veículo e acompanhe a disponibilidade no catálogo. Uma experiência direta, organizada e sem complicação.</p>
          <div class="hero-actions">
            <a class="button button-primary" href="shopping.php">Explorar catálogo <span aria-hidden="true">↗</span></a>
            <?php if (is_staff()): ?><a class="button button-outline" href="estoque.php">Abrir estoque</a><?php endif; ?>
          </div>
          <div class="hero-meta"><span>✓ Catálogo atualizado pelo estoque</span><span>✓ Controle de acesso</span></div>
        </div>
        <div class="hero-visual">
          <div class="hero-glow"></div>
          <img src="assets/img/carro.png" alt="Carro esportivo ilustrando o catálogo de autopeças">
          <div class="hero-caption"><span class="caption-dot"></span> Encontre a peça certa</div>
        </div>
      </div>
    </section>
    <section class="container section-space">
      <div class="section-heading"><div><span class="eyebrow">O QUE VOCÊ ENCONTRA</span><h2>Peças para cada necessidade</h2></div><a class="text-link" href="shopping.php">Ver todo o catálogo →</a></div>
      <div class="feature-grid">
        <a class="feature-card" href="shopping.php?categoria=Motor"><div class="feature-icon">⚙️</div><h3>Motor</h3><p>Componentes, filtros e peças mecânicas.</p><span>Explorar →</span></a>
        <a class="feature-card" href="shopping.php?categoria=Rodas"><div class="feature-icon">◉</div><h3>Rodas e pneus</h3><p>Peças para desempenho e segurança.</p><span>Explorar →</span></a>
        <a class="feature-card" href="shopping.php?categoria=Funilaria"><div class="feature-icon">▱</div><h3>Funilaria</h3><p>Acabamento e peças externas.</p><span>Explorar →</span></a>
      </div>
    </section>
    <section class="container section-space compact-section"><div class="info-strip"><div><span class="eyebrow">CONTROLE CENTRALIZADO</span><h2>Organização que acompanha sua operação.</h2><p>Usuários consultam o catálogo; funcionários autorizados cadastram peças e ajustam quantidades com validação no servidor.</p></div><a class="button button-primary" href="<?= is_staff() ? 'estoque.php' : 'shopping.php' ?>">Começar agora ↗</a></div></section>
  </main>
  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
