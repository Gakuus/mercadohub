<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}
require_csrf();
check_rate_limit('add_item', 10, 60);

$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$nombre = trim($input['nombre'] ?? '');
$descripcion = trim($input['descripcion'] ?? '');
$precio = $input['precio'] ?? '';
$url = $input['url'] ?? '';
$categoria = $input['categoria'] ?? '';

if (empty($nombre) || empty($url) || empty($categoria)) {
    echo json_encode(['error' => 'El nombre, la imagen y la categoria son obligatorios.']);
    exit;
}

// Validate image size (max 5MB base64 decoded)
$imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $url));
if (strlen($imageData) > 5 * 1024 * 1024) {
    echo json_encode(['error' => 'La imagen supera el maximo de 5MB.']);
    exit;
}

$precioVal = $precio !== '' ? (float)$precio : null;

try {
    // Try to save image to disk with GD optimization
    $imgPath = null;
    if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
        $img = @imagecreatefromstring($imageData);
        if ($img) {
            $uploadDir = __DIR__ . '/../public/uploads/items';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid('item_') . '.jpg';
            $fullPath = $uploadDir . '/' . $filename;

            // Resize if needed (max 1920px on longest side)
            $width = imagesx($img);
            $height = imagesy($img);
            $maxDim = 1920;
            if ($width > $maxDim || $height > $maxDim) {
                $ratio = min($maxDim / $width, $maxDim / $height);
                $newW = (int)($width * $ratio);
                $newH = (int)($height * $ratio);
                $resized = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $width, $height);
                imagedestroy($img);
                $img = $resized;
            }

            imagejpeg($img, $fullPath, 80);
            imagedestroy($img);

            $imgPath = 'public/uploads/items/' . $filename;
        }
    }

    if ($imgPath) {
        $stmt = $pdo->prepare("INSERT INTO items (nombre_items, descripcion_items, img_path, items_precio, id_juegos, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $imgPath, $precioVal, $categoria, $user_id]);
    } else {
        // Fallback to BLOB if GD failed
        $stmt = $pdo->prepare("INSERT INTO items (nombre_items, descripcion_items, img_items, items_precio, id_juegos, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $imageData, $precioVal, $categoria, $user_id]);
    }

    log_actividad('add_item', "Item: $nombre, Categoria: $categoria");
    echo json_encode(['message' => 'Item agregado exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar el item: ' . $e->getMessage()]);
}
?>
