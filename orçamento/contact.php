<?php 
$pageTitle = "Contato";
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'includes/config.php';
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $sql = "INSERT INTO contacts (name, email, phone, subject, message, created_at) 
            VALUES ('$name', '$email', '$phone', '$subject', '$message', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Mensagem enviada com sucesso! Retornaremos em breve.";
    } else {
        $error = "Erro ao enviar mensagem: " . mysqli_error($conn);
    }
    
    mysqli_close($conn);
}
?>

<main class="contact-page">
    <div class="container">
        <h2>Entre em Contato</h2>
        
        <div class="contact-container">
            <div class="contact-info">
                <h3>Informações de Contato</h3>
                <p><i class="fas fa-map-marker-alt"></i> Rua das Mudanças, 123 - Benfica-Rj</p>
                <p><i class="fas fa-phone"></i> (21) 9999-9999</p>
                <p><i class="fas fa-envelope"></i> contato@robertomudanças.com.br</p>
                <p><i class="fas fa-clock"></i> Seg-Sex: 8h-18h | Sáb: 8h-12h</p>
                
                <div class="social-media">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="contact-form">
                <h3>Envie uma Mensagem</h3>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Seu nome" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Seu e-mail" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Seu telefone" required>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Assunto" required>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="message" placeholder="Sua mensagem" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>