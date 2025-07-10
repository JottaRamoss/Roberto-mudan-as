<?php 
$pageTitle = "Solicitar Orçamento";
include 'includes/header.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/config.php';
    
    
    // Sanitizar e validar dados
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $origin = mysqli_real_escape_string($conn, trim($_POST['origin']));
    $destination = mysqli_real_escape_string($conn, trim($_POST['destination']));
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $details = mysqli_real_escape_string($conn, trim($_POST['details']));
    
    // Validar e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, insira um e-mail válido.";
    } 
    // Validar telefone (formato simples)
    elseif (!preg_match('/^[\d\s\-\(\)]{10,}$/', $phone)) {
        $error = "Por favor, insira um telefone válido.";
    } 
    // Validar data (não pode ser no passado)
    elseif (strtotime($date) < strtotime('today')) {
        $error = "A data da mudança não pode ser no passado.";
    } else {
        // Inserir no banco de dados
        $sql = "INSERT INTO budgets (name, email, phone, origin, destination, move_date, details, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $phone, $origin, $destination, $date, $details);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Orçamento solicitado com sucesso! Entraremos em contato em até 24 horas.";
            
            // Limpar campos do formulário
            $name = $email = $phone = $origin = $destination = $date = $details = '';
        } else {
            $error = "Erro ao enviar solicitação. Por favor, tente novamente mais tarde.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}
?>

<main class="budget-page">
    <section class="budget-hero">
        <div class="container">
            
            
            <h1>Solicite Seu Orçamento</h1>
            <p>Preencha o formulário e receba um orçamento sem compromisso</p>
        </div>
    </section>

    <section class="budget-form-section">
        <div class="container">
            <div class="budget-form-container">
                <div class="budget-info">
                    <h3>Como Funciona?</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Orçamento rápido e sem compromisso</li>
                        <li><i class="fas fa-check-circle"></i> Atendimento personalizado</li>
                        <li><i class="fas fa-check-circle"></i> Sem custos ocultos</li>
                        <li><i class="fas fa-check-circle"></i> Cobertura em toda a região</li>
                    </ul>
                    
                    <div class="contact-info">
                        <h3>Dúvidas?</h3>
                        <p><i class="fas fa-phone-alt"></i> (21) 9999-9999</p>
                        <p><i class="fas fa-whatsapp"></i> (21) 98765-4321</p>
                        <p><i class="fas fa-envelope"></i> orcamentos@robertomudanças.com.br</p>
                    </div>
                </div>
                
                <div class="budget-form">
                    <h2>Formulário de Orçamento</h2>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php elseif (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="budget.php" method="POST" id="budgetForm">
                        <div class="form-group">
                            <label for="name">Nome Completo*</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">E-mail*</label>
                                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Telefone*</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="origin">Origem*</label>
                                <input type="text" id="origin" name="origin" value="<?php echo isset($origin) ? htmlspecialchars($origin) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="destination">Destino*</label>
                                <input type="text" id="destination" name="destination" value="<?php echo isset($destination) ? htmlspecialchars($destination) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Data Prevista para Mudança*</label>
                            <input type="date" id="date" name="date" value="<?php echo isset($date) ? htmlspecialchars($date) : ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="details">Detalhes Adicionais</label>
                            <textarea id="details" name="details" rows="4"><?php echo isset($details) ? htmlspecialchars($details) : ''; ?></textarea>
                            <p class="hint">Informe itens especiais, quantidade de cômodos, móveis grandes, etc.</p>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Solicitar Orçamento
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>