<?php
// Script para descargar PHPMailer automáticamente
echo "Descargando PHPMailer...\n";

// Crear directorio PHPMailer si no existe
if (!file_exists('PHPMailer')) {
    mkdir('PHPMailer', 0755, true);
    mkdir('PHPMailer/src', 0755, true);
}

// URLs de los archivos de PHPMailer
$files = [
    'src/Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'src/PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'src/SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
];

foreach ($files as $filename => $url) {
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents("PHPMailer/$filename", $content);
        echo "Descargado: $filename\n";
    } else {
        echo "Error descargando: $filename\n";
    }
}

echo "PHPMailer descargado correctamente.\n";
?>
