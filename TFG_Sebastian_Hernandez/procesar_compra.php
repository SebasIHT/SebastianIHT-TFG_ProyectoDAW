<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para realizar una compra.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$carrito = $input['carrito'] ?? [];

if (empty($carrito)) {
    echo json_encode([
        'success' => false,
        'message' => 'El carrito está vacío. Agrega productos antes de finalizar la compra.'
    ]);
    exit;
}

$host = 'localhost';
$dbname = 'tienda_ropa';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    $total = 0;
    $detalles = [];

    foreach ($carrito as $item) {
        $id_producto = (int)($item['id'] ?? 0);
        $talla = trim($item['talla'] ?? 'UNICA');
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($id_producto <= 0 || $cantidad <= 0) {
            continue;
        }

        $stmt = $pdo->prepare("SELECT precio FROM Producto WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch();

        if (!$producto) {
            throw new Exception("Uno de los productos ya no está disponible.");
        }

        $precio = (float)$producto['precio'];
        $subtotal = $precio * $cantidad;
        $total += $subtotal;

        $detalles[] = [
            'id_producto' => $id_producto,
            'talla' => $talla,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio
        ];
    }

    if (empty($detalles) || $total <= 0) {
        throw new Exception('No se encontraron productos válidos en el carrito.');
    }

    $id_usuario = (int)$_SESSION['usuario_id'];
    $metodo_pago = 'tarjeta';
    $estado = 'pendiente';

    $stmt = $pdo->prepare("
        INSERT INTO Compra (id_usuario, fecha_compra, total, metodo_pago, estado_compra)
        VALUES (?, NOW(), ?, ?, ?)
    ");
    $stmt->execute([$id_usuario, $total, $metodo_pago, $estado]);
    $id_compra = $pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO DetalleCompra (id_compra, id_producto, talla, cantidad, precio_unitario)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($detalles as $detalle) {
        $stmt->execute([
            $id_compra,
            $detalle['id_producto'],
            $detalle['talla'],
            $detalle['cantidad'],
            $detalle['precio_unitario']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id_compra' => $id_compra,
        'message' => '¡Gracias por tu compra! Tu pedido ha sido registrado correctamente.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error en procesar_compra.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Lo sentimos, hubo un error al procesar tu compra. Por favor, inténtalo más tarde.'
    ]);
}
?>