<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "teckets";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres a utf8
$conn->set_charset("utf8");
?>
