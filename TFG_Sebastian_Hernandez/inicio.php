<?php
session_start();

$host = 'localhost';
$dbname = 'tienda_ropa';
$user = 'root';
$pass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contraseña'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        header("Location: inicio.html?error=invalid");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id_usuario, nombre, contraseña FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($contrasena, $usuario['contraseña'])) {
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8');
            
            header("Location: menu.html");
            exit;
        } else {
            header("Location: inicio.html?error=invalid");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error de login: " . $e->getMessage());
        header("Location: inicio.html?error=invalid");
        exit;
    }
} else {
    header("Location: inicio.html");
    exit;
}
?>