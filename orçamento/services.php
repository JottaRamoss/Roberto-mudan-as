<?php 
$pageTitle = "Nossos Serviços";
include 'includes/header.php'; 
?>

<main class="services-page">
    <section class="services-intro">
        <div class="container">
            <h2>Nossos Serviços Completos</h2>
            <p>Oferecemos soluções personalizadas para todos os tipos de mudança</p>
        </div>
    </section>

    <section class="services-list">
        <div class="container">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3>Mudanças Residenciais</h3>
                <ul>
                    <li>Apartamentos e casas</li>
                    <li>Embalagem profissional</li>
                    <li>Montagem e desmontagem de móveis</li>
                    <li>Transporte seguro</li>
                </ul>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h3>Mudanças Comerciais</h3>
                <ul>
                    <li>Escritórios e lojas</li>
                    <li>Equipamentos sensíveis</li>
                    <li>Horários flexíveis</li>
                    <li>Planejamento logístico</li>
                </ul>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <h3>Armazenamento</h3>
                <ul>
                    <li>Armazéns climatizados</li>
                    <li>Seguro incluído</li>
                    <li>Acesso 24/7</li>
                    <li>Diversos tamanhos disponíveis</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="service-cta">
        <div class="container">
            <h3>Precisa de um serviço personalizado?</h3>
            <a href="contact.php" class="btn btn-secondary">Fale conosco</a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>