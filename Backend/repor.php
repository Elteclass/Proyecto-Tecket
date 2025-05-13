<?php
session_start();

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "teckets";

$conn = new mysqli($servername, $username, $password, $database);

header("Location: ../Frontend/ticket.html")
?>