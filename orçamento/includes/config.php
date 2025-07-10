<?php
$host = "localhost";
$user = "root";      // Usuário padrão do XAMPP
$password = "";      // Senha vazia por padrão no XAMPP
$database = "mudancas_express"; // Nome exato do seu banco

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>