<?php
session_start();

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

// Verificar que se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Usar los nombres de los campos como están en el formulario HTML
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Buscar el usuario en la base de datos usando el campo 'correo'
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Depuración: Mostrar la contraseña almacenada (quitar en producción)
        // echo "Contraseña en BD: '" . $user["contraseña"] . "' | Contraseña ingresada: '" . $password . "'";
        
        // Limpiar posibles espacios en blanco
        $stored_password = trim($user["contrasena"]);
        $input_password = trim($password);
        
        // Verificar la contraseña con el campo 'contraseña' usando comparación simple
        if ($input_password === $stored_password) {
            // Iniciar sesión y redirigir
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["nombre"];
            // Como no hay campo 'rol', asignamos un rol por defecto
            $_SESSION["user_role"] = "admin";
            header("Location: ../Frontend/ticket.html");
            exit();
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Contraseña incorrecta. Intenta de nuevo.\\nVerifica que no haya espacios adicionales.'); window.location.href = '../Frontend/index.html';</script>";
            exit();
        }
    } else {
        // Usuario no encontrado
        echo "<script>alert('El correo no está registrado. Intenta de nuevo.'); window.location.href = '../Frontend/index.html';</script>";
        exit();
    }
}

$conn->close();
?>