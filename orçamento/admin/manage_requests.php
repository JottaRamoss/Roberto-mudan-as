<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require '../includes/functions.php';

// Conexão com o banco de dados
try {
    $conn = db_connect();
} catch (Exception $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Processar ações (excluir, marcar como respondido, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die('Token CSRF inválido!');
    }

    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    switch ($action) {
        case 'delete_budget':
            $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = display_alert('Orçamento excluído com sucesso!', 'success');
            } else {
                $_SESSION['message'] = display_alert('Erro ao excluir orçamento.', 'error');
            }
            break;

        case 'delete_contact':
            $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = display_alert('Mensagem excluída com sucesso!', 'success');
            } else {
                $_SESSION['message'] = display_alert('Erro ao excluir mensagem.', 'error');
            }
            break;

        case 'mark_contacted':
            $stmt = $conn->prepare("UPDATE budgets SET status = 'contacted' WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = display_alert('Orçamento marcado como respondido!', 'success');
            }
            break;
    }

    // Redirecionar para evitar reenvio do formulário
    header('Location: manage_requests.php');
    exit;
}

// Obter orçamentos e mensagens
$budgets = [];
$contacts = [];

// Orçamentos
$budget_query = "SELECT * FROM budgets ORDER BY created_at DESC";
$budget_result = $conn->query($budget_query);
if ($budget_result) {
    $budgets = $budget_result->fetch_all(MYSQLI_ASSOC);
}

// Mensagens de contato
$contact_query = "SELECT * FROM contacts ORDER BY created_at DESC";
$contact_result = $conn->query($contact_query);
if ($contact_result) {
    $contacts = $contact_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Solicitações | Mudanças Express</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos base do admin */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar (reutilizando do dashboard.php) */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 1rem 0;
            position: fixed;
            height: 100%;
        }

        .sidebar-header {
            padding: 0 1rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li a {
            display: block;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar-menu li a i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 1rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1rem;
        }

        .tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            margin-right: 0.5rem;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
        }

        .tab.active {
            background-color: white;
            border-color: #ddd;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
            font-weight: bold;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        table tr:hover {
            background-color: #f8f9fa;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .badge-pending {
            background-color: rgba(241, 196, 15, 0.2);
            color: #f39c12;
        }

        .badge-contacted {
            background-color: rgba(46, 204, 113, 0.2);
            color: #27ae60;
        }

        .badge-completed {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 0.3rem 0.6rem;
            font-size: 0.75rem;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-success {
            background-color: #2ecc71;
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Mudanças Express</h2>
                <p>Painel Administrativo</p>
            </div>
            
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_requests.php" class="active"><i class="fas fa-inbox"></i> Solicitações</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Clientes</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h3>Gerenciar Solicitações</h3>
                <div class="user-info">
                    <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
            
            <!-- Mensagens -->
            <?php if (isset($_SESSION['message'])): ?>
                <div style="margin-bottom: 1rem;">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Tabs -->
            <div class="tabs">
                <div class="tab active" onclick="openTab(event, 'budgets')">Orçamentos</div>
                <div class="tab" onclick="openTab(event, 'contacts')">Mensagens</div>
            </div>
            
            <!-- Tab Content - Orçamentos -->
            <div id="budgets" class="tab-content active">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Contato</th>
                                <th>Origem/Destino</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget): ?>
                            <tr>
                                <td><?php echo $budget['id']; ?></td>
                                <td><?php echo htmlspecialchars($budget['name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($budget['email']); ?><br>
                                    <?php echo format_phone($budget['phone']); ?>
                                </td>
                                <td>
                                    <strong>De:</strong> <?php echo htmlspecialchars($budget['origin']); ?><br>
                                    <strong>Para:</strong> <?php echo htmlspecialchars($budget['destination']); ?>
                                </td>
                                <td>
                                    <?php echo format_date($budget['move_date']); ?><br>
                                    <small><?php echo format_date($budget['created_at'], 'd/m/Y H:i'); ?></small>
                                </td>
                                <td>
                                    <?php if ($budget['status'] === 'pending'): ?>
                                        <span class="badge badge-pending">Pendente</span>
                                    <?php elseif ($budget['status'] === 'contacted'): ?>
                                        <span class="badge badge-contacted">Respondido</span>
                                    <?php else: ?>
                                        <span class="badge badge-completed">Concluído</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="id" value="<?php echo $budget['id']; ?>">
                                        
                                        <?php if ($budget['status'] === 'pending'): ?>
                                            <button type="submit" name="action" value="mark_contacted" class="btn btn-success btn-sm" title="Marcar como respondido">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="submit" name="action" value="delete_budget" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este orçamento?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($budgets)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Nenhum orçamento encontrado.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Tab Content - Mensagens -->
            <div id="contacts" class="tab-content">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Contato</th>
                                <th>Assunto</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                            <tr>
                                <td><?php echo $contact['id']; ?></td>
                                <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($contact['email']); ?><br>
                                    <?php echo format_phone($contact['phone']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                                <td><?php echo format_date($contact['created_at'], 'd/m/Y H:i'); ?></td>
                                <td>
                                    <?php echo $contact['is_read'] ? '<span class="badge badge-contacted">Lida</span>' : '<span class="badge badge-pending">Não lida</span>'; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                                        <button type="submit" name="action" value="delete_contact" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta mensagem?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($contacts)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Nenhuma mensagem encontrada.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Alternar entre tabs
        function openTab(evt, tabName) {
            const tabContents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }

            const tabs = document.getElementsByClassName("tab");
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }

            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // Confirmar antes de excluir
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('button[value^="delete"]')) {
                    if (!confirm('Tem certeza que deseja excluir este item?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>