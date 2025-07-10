<?php
/**
 * Arquivo de funções úteis para o site de mudanças
 * 
 * @package MudancasExpress
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Conecta ao banco de dados
 * 
 * @return mysqli Retorna a conexão com o banco de dados
 * @throws Exception Se a conexão falhar
 */
function db_connect() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = mysqli_connect(
            getenv('DB_HOST') ?: 'localhost',
            getenv('DB_USER') ?: 'mudancas_user',
            getenv('DB_PASS') ?: 'senha_segura_123',
            getenv('DB_NAME') ?: 'mudancas_express'
        );
        
        if (!$conn) {
            throw new Exception('Erro ao conectar ao banco de dados: ' . mysqli_connect_error());
        }
        
        mysqli_set_charset($conn, 'utf8mb4');
    }
    
    return $conn;
}

/**
 * Sanitiza dados para evitar XSS e SQL injection
 * 
 * @param mixed $data Dados a serem sanitizados
 * @param string $type Tipo de sanitização (text|email|int|float|url)
 * @return mixed Dados sanitizados
 */
function sanitize_input($data, $type = 'text') {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    
    switch ($type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            break;
        default:
            $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Valida um número de telefone brasileiro
 * 
 * @param string $phone Número de telefone
 * @return bool Retorna true se for válido
 */
function validate_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(?:[1-9]{2}|0[1-9]{2})?(?:[2-8]|9[1-9])[0-9]{7,8}$/', $phone);
}

/**
 * Formata um número de telefone para exibição
 * 
 * @param string $phone Número de telefone
 * @return string Telefone formatado
 */
function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
    } elseif (strlen($phone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
    }
    
    return $phone;
}

/**
 * Redireciona para uma URL
 * 
 * @param string $url URL para redirecionar
 * @param int $status_code Código de status HTTP
 */
function redirect($url, $status_code = 302) {
    header("Location: $url", true, $status_code);
    exit;
}

/**
 * Gera um token CSRF
 * 
 * @return string Token gerado
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica um token CSRF
 * 
 * @param string $token Token a ser verificado
 * @return bool Retorna true se for válido
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token'], $token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formata uma data para exibição
 * 
 * @param string $date Data no formato YYYY-MM-DD
 * @param string $format Formato de saída (padrão: d/m/Y)
 * @return string Data formatada
 */
function format_date($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }
    
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Envia e-mail de contato
 * 
 * @param array $data Dados do formulário
 * @return bool Retorna true se o e-mail for enviado
 */
function send_contact_email($data) {
    $to = getenv('CONTACT_EMAIL') ?: 'contato@mudancasexpress.com.br';
    $subject = 'Novo contato do site: ' . $data['subject'];
    
    $message = "Você recebeu uma nova mensagem do site:\n\n";
    $message .= "Nome: " . $data['name'] . "\n";
    $message .= "E-mail: " . $data['email'] . "\n";
    $message .= "Telefone: " . $data['phone'] . "\n\n";
    $message .= "Mensagem:\n" . $data['message'] . "\n";
    
    $headers = "From: " . $data['email'] . "\r\n" .
               "Reply-To: " . $data['email'] . "\r\n" .
               "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Obtém a URL base do site
 * 
 * @return string URL base
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * Verifica se o usuário está logado (para área admin)
 * 
 * @return bool Retorna true se estiver logado
 */
function is_logged_in() {
    return isset($_SESSION['user_id'], $_SESSION['user_email']);
}

/**
 * Gera HTML para exibição de alertas
 * 
 * @param string $message Mensagem a ser exibida
 * @param string $type Tipo de alerta (success|error|warning|info)
 * @return string HTML do alerta
 */
function display_alert($message, $type = 'success') {
    $icons = [
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ];
    
    $icon = $icons[$type] ?? 'info-circle';
    
    return sprintf(
        '<div class="alert alert-%s"><i class="fas fa-%s"></i> %s</div>',
        $type,
        $icon,
        htmlspecialchars($message)
    );
}

/**
 * Obtém os últimos orçamentos cadastrados
 * 
 * @param int $limit Quantidade de registros
 * @return array Lista de orçamentos
 */
function get_recent_budgets($limit = 5) {
    $conn = db_connect();
    $budgets = [];
    
    $sql = "SELECT id, name, email, phone, origin, destination, move_date, created_at 
            FROM budgets 
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $limit);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $budgets[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $budgets;
}

/**
 * Calcula a diferença de dias entre duas datas
 * 
 * @param string $start_date Data inicial
 * @param string $end_date Data final
 * @return int Diferença em dias
 */
function date_diff_days($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days;
}

/**
 * Gera um slug a partir de um texto
 * 
 * @param string $text Texto para converter
 * @return string Slug gerado
 */
function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return $text ?: 'n-a';
}

/**
 * Verifica se uma string contém HTML
 * 
 * @param string $string Texto para verificar
 * @return bool Retorna true se contiver HTML
 */
function contains_html($string) {
    return $string !== strip_tags($string);
}

/**
 * Limita o número de palavras em um texto
 * 
 * @param string $text Texto original
 * @param int $limit Número máximo de palavras
 * @param string $ellipsis Reticências ao final (opcional)
 * @return string Texto limitado
 */
function limit_words($text, $limit, $ellipsis = '...') {
    $words = preg_split('/\s+/', $text);
    
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . $ellipsis;
    }
    
    return $text;
}

/**
 * Obtém o valor de uma variável de ambiente
 * 
 * @param string $key Chave da variável
 * @param mixed $default Valor padrão
 * @return mixed Valor da variável ou padrão
 */
function env($key, $default = null) {
    static $env = null;
    
    if ($env === null) {
        $env_file = __DIR__ . '/../.env';
        
        if (file_exists($env_file)) {
            $env = parse_ini_file($env_file);
        } else {
            $env = [];
        }
    }
    
    return $env[$key] ?? getenv($key) ?: $default;
}