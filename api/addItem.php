<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('add_item', 10, 60);

$user_id = $_SESSION['user_id'];

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = $_POST['precio'] ?? '';
$categoria = $_POST['categoria'] ?? '';

if (empty($nombre) || empty($categoria)) {
    echo json_encode(['error' => 'El nombre y la categoria son obligatorios.']);
}

if (mb_strlen($nombre) > 100) {
    echo json_encode(['error' => 'El nombre no puede superar los 100 caracteres.']);
}
if (mb_strlen($descripcion) > 2000) {
    echo json_encode(['error' => 'La descripcion no puede superar los 2000 caracteres.']);
}
if ($precio !== '' && (!is_numeric($precio) || (float)$precio < 0 || (float)$precio > 999999)) {
    echo json_encode(['error' => 'Precio invalido.']);
}

$precioVal = $precio !== '' ? (float)$precio : null;

// Helper: save uploaded file to disk
function saveItemImage($fileData, $uploadDir) {
    if (!$fileData || $fileData['error'] !== UPLOAD_ERR_OK) return null;
    $maxSize = 5 * 1024 * 1024;
    if ($fileData['size'] > $maxSize) return null;

    $tmpPath = $fileData['tmp_name'];
    $imageData = file_get_contents($tmpPath);
    if (!$imageData) return null;

    if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
        $img = @imagecreatefromstring($imageData);
        if ($img) {
            $filename = uniqid('item_') . '.jpg';
            $fullPath = $uploadDir . '/' . $filename;

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
            return 'public/uploads/items/' . $filename;
        }
    }
    // fallback: upload raw
    $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';
    $filename = uniqid('item_') . '.' . $ext;
    $fullPath = $uploadDir . '/' . $filename;
    move_uploaded_file($tmpPath, $fullPath);
    return 'public/uploads/items/' . $filename;
}

try {
    $uploadDir = __DIR__ . '/../public/uploads/items';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Process cover image
    $cover = $_FILES['imagen'] ?? null;
    $coverPath = saveItemImage($cover, $uploadDir);

    if (!$coverPath) {
        echo json_encode(['error' => 'La imagen principal es obligatoria y debe ser menor a 5MB.']);
    }

    // Insert item
    $stmt = $pdo->prepare("INSERT INTO items (nombre_items, descripcion_items, img_path, items_precio, id_juegos, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $coverPath, $precioVal, $categoria, $user_id]);
    $itemId = $pdo->lastInsertId();

    // Insert cover into gallery (orden 0)
    $stmt2 = $pdo->prepare("INSERT INTO item_imagenes (id_items, path, orden) VALUES (?, ?, 0)");
    $stmt2->execute([$itemId, $coverPath]);

    // Process extra images
    $extras = $_FILES['imagenes_extra'] ?? null;
    if ($extras && is_array($extras['name'])) {
        $orden = 1;
        foreach ($extras['name'] as $i => $name) {
            $filePiece = [
                'name' => $extras['name'][$i],
                'type' => $extras['type'][$i],
                'tmp_name' => $extras['tmp_name'][$i],
                'error' => $extras['error'][$i],
                'size' => $extras['size'][$i],
            ];
            $path = saveItemImage($filePiece, $uploadDir);
            if ($path) {
                $stmt3 = $pdo->prepare("INSERT INTO item_imagenes (id_items, path, orden) VALUES (?, ?, ?)");
                $stmt3->execute([$itemId, $path, $orden++]);
            }
        }
    }

    log_actividad('add_item', "Item: $nombre, Categoria: $categoria, Imagenes: " . (1 + ($extras ? count(array_filter($extras['name'])) : 0)));
    echo json_encode(['message' => 'Item agregado exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar el item: ' . $e->getMessage()]);
}
?>
