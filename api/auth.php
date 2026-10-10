<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}
require_valid_csrf();
$action = (string) ($_POST['action'] ?? '');
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || $password === '') {
    redirect_to('../login.php?status=dados_invalidos');
}

try {
    $pdo = db();
    if ($action === 'login') {
        $statement = $pdo->prepare('SELECT id, nome, email, senha_hash, tipo FROM usuarios WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['senha_hash'])) {
            redirect_to('../login.php?status=credenciais_invalidas');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'tipo' => $user['tipo'],
        ];
        redirect_to('../shopping.php');
    }
    if ($action === 'register') {
        $name = trim((string) ($_POST['nome'] ?? ''));
        $phone = trim((string) ($_POST['telefone'] ?? ''));
        if (strlen($name) < 2 || strlen($name) > 120
            || strlen($phone) > 20 || strlen($password) < 8 || strlen($password) > 72) {
            redirect_to('../login.php?status=dados_invalidos');
        }
        $statement = $pdo->prepare(
            "INSERT INTO usuarios (nome, telefone, email, senha_hash, tipo)
             VALUES (:nome, :telefone, :email, :senha_hash, 'usuario')"
        );
        try {
            $statement->execute([
                'nome' => $name,
                'telefone' => $phone,
                'email' => $email,
                'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                redirect_to('../login.php?status=email_em_uso');
            }
            throw $error;
        }
        redirect_to('../login.php?status=cadastro_concluido');
    }
    http_response_code(400);
    exit('Ação desconhecida.');
} catch (Throwable $error) {
    error_log('Falha na autenticação: ' . $error->getMessage());
    redirect_to('../login.php?status=erro_servidor');
}
