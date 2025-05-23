<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "teckets";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexión fallida: ' . $conn->connect_error]));
}

// Establecer el conjunto de caracteres
$conn->set_charset("utf8");

// Verificar si se recibieron los datos
if (!isset($_POST['id']) || !isset($_POST['nucontrol']) || !isset($_POST['nombre']) || !isset($_POST['correo']) || 
    !isset($_POST['area']) || !isset($_POST['failure']) || !isset($_POST['descripcion']) || !isset($_POST['estado'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Obtener los datos del formulario
$id = $_POST['id'];
$nucontrol = $_POST['nucontrol'];
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$area = $_POST['area'];
$failure = $_POST['failure'];
$descripcion = $_POST['descripcion'];
$estado = $_POST['estado'];
$removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

// Verificar si el ticket existe
$stmt = $conn->prepare("SELECT imagen FROM tickets WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'El ticket no existe']);
    $stmt->close();
    $conn->close();
    exit;
}

// Obtener la imagen actual
$row = $result->fetch_assoc();
$currentImage = $row['imagen'];

// Manejar la imagen
$newImage = $currentImage;

// Si se solicita eliminar la imagen
if ($removeImage) {
    // Eliminar el archivo físico si existe
    if ($currentImage && file_exists("../uploads/" . $currentImage)) {
        unlink("../uploads/" . $currentImage);
    }
    $newImage = null;
}
// Si se sube una nueva imagen
elseif (isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
    $targetDir = "../uploads/";
    $fileName = basename($_FILES["imagen"]["name"]);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generar un nombre único para el archivo
    $newFileName = uniqid() . '.' . $fileType;
    $targetFile = $targetDir . $newFileName;
    
    // Verificar el tipo de archivo
    if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg") {
        echo json_encode(['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG y PNG']);
        exit;
    }
    
    // Verificar el tamaño del archivo (500KB = 500 * 1024 bytes)
    if ($_FILES["imagen"]["size"] > 500 * 1024) {
        echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 500KB']);
        exit;
    }
    
    // Intentar subir el archivo
    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $targetFile)) {
        // Eliminar la imagen anterior si existe
        if ($currentImage && file_exists("../uploads/" . $currentImage)) {
            unlink("../uploads/" . $currentImage);
        }
        $newImage = $newFileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
        exit;
    }
}

// Actualizar el ticket en la base de datos
$stmt = $conn->prepare("UPDATE tickets SET nucontrol = ?, nombre = ?, correo = ?, area = ?, failure = ?, descripcion = ?, estado = ?, imagen = ? WHERE id = ?");
$stmt->bind_param("ssssssssi", $nucontrol, $nombre, $correo, $area, $failure, $descripcion, $estado, $newImage, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Ticket actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el ticket: ' . $stmt->error]);
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>