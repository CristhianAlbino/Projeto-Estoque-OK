<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$active = 'login';
$messages = [
    'cadastro_concluido' => 'Conta criada. Entre com seu e-mail e senha.',
    'email_em_uso' => 'Este e-mail já está cadastrado.',
    'credenciais_invalidas' => 'E-mail ou senha inválidos.',
    'dados_invalidos' => 'Confira os campos e tente novamente. A senha do cadastro precisa ter de 8 a 72 caracteres.',
    'erro_servidor' => 'Não foi possível concluir a solicitação. Verifique a configuração do servidor.',
];
$status = (string) ($_GET['status'] ?? '');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entrar e cadastrar | AutoPeças</title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <?php require __DIR__ . '/includes/nav.php'; ?>
  <main class="container auth-main">
    <div class="auth-heading"><span class="eyebrow">ÁREA DO CLIENTE</span><h1>Bem-vindo de volta.</h1><p>Acesse sua conta ou faça um cadastro para começar.</p></div>
    <?php if (isset($messages[$status])): ?><div class="notice" role="status"><?= e($messages[$status]) ?></div><?php endif; ?>
    <div class="auth-grid">
      <section class="form-card" aria-labelledby="login-title">
        <div class="form-icon">↗</div><h2 id="login-title">Entrar</h2><p>Já tem conta? Use suas credenciais.</p>
        <form action="api/auth.php" method="post">
          <input type="hidden" name="action" value="login"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <label for="login-email">E-mail</label><input id="login-email" name="email" type="email" autocomplete="email" required maxlength="190" placeholder="voce@exemplo.com">
          <label for="login-password">Senha</label><input id="login-password" name="senha" type="password" autocomplete="current-password" required placeholder="Sua senha">
          <button class="button button-primary button-full" type="submit">Entrar na minha conta →</button>
        </form>
      </section>
      <section class="form-card" aria-labelledby="register-title">
        <div class="form-icon">＋</div><h2 id="register-title">Criar conta</h2><p>Comece com seus dados básicos.</p>
        <form action="api/auth.php" method="post">
          <input type="hidden" name="action" value="register"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <label for="register-name">Nome completo</label><input id="register-name" name="nome" type="text" autocomplete="name" required minlength="2" maxlength="120" placeholder="Seu nome">
          <label for="register-phone">Telefone (opcional)</label><input id="register-phone" name="telefone" type="tel" autocomplete="tel" maxlength="20" placeholder="(15) 99999-9999">
          <label for="register-email">E-mail</label><input id="register-email" name="email" type="email" autocomplete="email" required maxlength="190" placeholder="voce@exemplo.com">
          <label for="register-password">Senha (mínimo de 8 caracteres)</label><input id="register-password" name="senha" type="password" autocomplete="new-password" required minlength="8" maxlength="72" placeholder="Crie uma senha">
          <button class="button button-primary button-full" type="submit">Criar minha conta →</button>
        </form>
      </section>
    </div>
  </main>
  <?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
