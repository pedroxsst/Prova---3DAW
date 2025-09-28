<?php
// Configurações básicas
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco";

// Conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
