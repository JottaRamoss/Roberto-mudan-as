<?php
session_start();
require_once __DIR__ . '/../includes/config.php';  // Caminho relativo CORRETO

// VERIFICA SE O USUÁRIO ESTÁ LOGADO (senha padrão: "admin123")
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    if ($_POST['senha'] === "admin123") { // <<< MUDE PARA UMA SENHA SEGURA!
        $_SESSION['logado'] = true;
    } else {
        die("Acesso negado. <a href='login.php'>Login</a>");
    }
}

// Busca orçamentos e mensagens
$budgets = mysqli_query($conn, "SELECT * FROM budgets ORDER BY created_at DESC");
$contacts = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Mudanças Express</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Painel Administrativo</h1>
    
    <h2>Orçamentos</h2>
    <table>
        <tr>
            <th>Data</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Origem</th>
            <th>Destino</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($budgets)): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['origin']) ?></td>
            <td><?= htmlspecialchars($row['destination']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <h2>Mensagens de Contato</h2>
    <table>
        <tr>
            <th>Data</th>
            <th>Nome</th>
            <th>Assunto</th>
            <th>Mensagem</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($contacts)): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['subject']) ?></td>
            <td><?= htmlspecialchars($row['message']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>