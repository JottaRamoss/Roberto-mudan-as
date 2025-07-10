<?php
require 'includes/config.php';
$query = "SHOW TABLES";
$result = $conn->query($query);
if ($result) {
    echo "✅ Conexão OK! Tables: " . $result->num_rows;
} else {
    echo "❌ Erro: " . $conn->error;
}