// Menu mobile
document.querySelector('.burger').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});

// Validação de formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    if (!/^[\d\s()-]+$/.test(phone)) {
        e.preventDefault();
        alert('Por favor, insira um número de telefone válido.');
    }
});

// Animação de scroll
window.addEventListener('scroll', function() {
    if (window.scrollY > 100) {
        document.querySelector('header').classList.add('scrolled');
    } else {
        document.querySelector('header').classList.remove('scrolled');
    }
});
// Validação do Formulário de Orçamento
document.addEventListener('DOMContentLoaded', function() {
    const budgetForm = document.getElementById('budgetForm');
    
    if (budgetForm) {
        budgetForm.addEventListener('submit', function(e) {
            // Validação do telefone
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^[\d\s\-\(\)]{10,}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Por favor, insira um número de telefone válido com pelo menos 10 dígitos.');
                return;
            }
            
            // Validação da data
            const moveDate = new Date(document.getElementById('date').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (moveDate < today) {
                e.preventDefault();
                alert('A data da mudança não pode ser no passado.');
                return;
            }
            
            // Validação de e-mail
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, insira um endereço de e-mail válido.');
                return;
            }
        });
        
        // Máscara para telefone
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length > 2) {
                    value = `(${value.substring(0, 2)}) ${value.substring(2)}`;
                }
                
                if (value.length > 10) {
                    value = `${value.substring(0, 10)}-${value.substring(10, 14)}`;
                }
                
                e.target.value = value;
            });
        }
    }
});
