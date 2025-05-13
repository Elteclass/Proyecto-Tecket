<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "teckets";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el usuario
$email = "jair@gmail.com"; // Reemplaza con el correo que estás usando
$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    echo "<h2>Información del usuario:</h2>";
    echo "<p>ID: " . $user["id"] . "</p>";
    echo "<p>Nombre: " . $user["nombre"] . "</p>";
    echo "<p>Correo: " . $user["correo"] . "</p>";
    echo "<p>Contraseña: '" . $user["contrasena"] . "'</p>";
    echo "<p>Longitud de la contraseña: " . strlen($user["contrasena"]) . " caracteres</p>";
    
    // Mostrar cada carácter y su código ASCII
    echo "<h3>Análisis de caracteres de la contraseña:</h3>";
    echo "<table border='1'><tr><th>Posición</th><th>Carácter</th><th>Código ASCII</th></tr>";
    for ($i = 0; $i < strlen($user["contrasena"]); $i++) {
        $char = substr($user["contrasena"], $i, 1);
        echo "<tr><td>$i</td><td>$char</td><td>" . ord($char) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Usuario no encontrado";
}

$conn->close();
?>