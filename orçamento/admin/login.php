<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
// Verifica se o usuário já está logado
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: admin.php");
    exit;
}

// Processa o login quando o formulário é enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = $_POST['senha'] ?? '';
    $senha_correta = "admin123"; // <<< Substitua por uma senha segura!

    if ($senha_digitada === $senha_correta) {
        $_SESSION['logado'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $erro = "Senha incorreta!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Painel Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .login-box { max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .erro { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Login Administrativo</h1>
        <?php if (isset($erro)): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="senha" placeholder="Digite sua senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>