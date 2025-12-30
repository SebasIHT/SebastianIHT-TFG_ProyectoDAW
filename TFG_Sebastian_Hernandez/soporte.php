<?php
session_start();

$host = 'localhost';
$dbname = 'tienda_ropa';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $problema = $_POST['problema'] ?? '';
        $motivo = $_POST['motivo'] ?? '';
        $nombre = htmlspecialchars(trim($nombre));
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        $problema = htmlspecialchars(trim($problema));
        $motivo = htmlspecialchars(trim($motivo));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<p style='color:red;'> Correo electrónico no válido.</p>";
            exit;
        }
        $id_usuario = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        $stmt = $pdo->prepare("
            INSERT INTO Soporte (id_usuario, mensaje, estado, fecha) 
            VALUES (?, ?, 'pendiente', NOW())
        ");
        $mensaje_completo = "Título: $problema\nDe: $nombre <$email>\n\n$motivo";
        $stmt->execute([$id_usuario, $mensaje_completo]);

        echo "<p style='color:green;'> Mensaje enviado correctamente. ¡Gracias por contactarnos!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'> Error al enviar el mensaje. Inténtalo más tarde.</p>";
}
?>