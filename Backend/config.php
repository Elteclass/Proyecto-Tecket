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
?>
