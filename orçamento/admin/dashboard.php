<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

include '../includes/config.php';

// Contar orçamentos
$sql_budgets = "SELECT COUNT(*) as total FROM budgets";
$result_budgets = mysqli_query($conn, $sql_budgets);
$total_budgets = mysqli_fetch_assoc($result_budgets)['total'];

// Contar mensagens de contato
$sql_contacts = "SELECT COUNT(*) as total FROM contacts";
$result_contacts = mysqli_query($conn, $sql_contacts);
$total_contacts = mysqli_fetch_assoc($result_contacts)['total'];

// Últimos orçamentos
$sql_last_budgets = "SELECT * FROM budgets ORDER BY created_at DESC LIMIT 5";
$result_last_budgets = mysqli_query($conn, $sql_last_budgets);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        
        body {
            background-color: #f5f6fa;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem 0;
            position: fixed;
            height: 100%;
        }
        
        .sidebar-header {
            padding: 0 1rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            margin-top: 1rem;
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
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .card-icon.bg-primary {
            background-color: rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
        }
        
        .card-icon.bg-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .card-icon.bg-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
        }
        
        .card h3 {
            font-size: 1.5rem;
            color: var(--dark-color);
        }
        
        .card p {
            color: #7f8c8d;
        }
        
        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        table th, table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        table th {
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .badge-warning {
            background-color: rgba(241, 196, 15, 0.2);
            color: #f39c12;
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_requests.php"><i class="fas fa-file-invoice-dollar"></i> Orçamentos</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> Mensagens</a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h3>Dashboard</h3>
                <div class="user-info">
                    <img src="../assets/images/user-avatar.jpg" alt="User">
                    <span><?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
            
            <!-- Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <p>Orçamentos</p>
                            <h3><?php echo $total_budgets; ?></h3>
                        </div>
                        <div class="card-icon bg-primary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div>
                            <p>Mensagens</p>
                            <h3><?php echo $total_contacts; ?></h3>
                        </div>
                        <div class="card-icon bg-success">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div>
                            <p>Clientes</p>
                            <h3>128</h3>
                        </div>
                        <div class="card-icon bg-danger">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Últimos Orçamentos -->
            <div class="card">
                <h3>Últimos Orçamentos</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Telefone</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_last_budgets)): ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td><?php echo substr($row['origin'], 0, 15) . '...'; ?></td>
                                <td><?php echo substr($row['destination'], 0, 15) . '...'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td><span class="badge badge-warning">Pendente</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>