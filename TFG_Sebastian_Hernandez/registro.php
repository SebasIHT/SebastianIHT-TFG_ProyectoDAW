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
        $correo = $_POST['correo'] ?? '';
        $contrasena = $_POST['contraseña'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $pais = $_POST['pais'] ?? '';

        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            echo "<p style='color:red;'> El correo ya está registrado.</p>";
            exit;
        }
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña, direccion, telefono, pais) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $correo, $hash, $direccion, $telefono, $pais]);

        echo "<p style='color:green;'> Registro exitoso. <a href='inicio.html'>Inicio sesión</a></p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'> Error: " . $e->getMessage() . "</p>";
}
?>