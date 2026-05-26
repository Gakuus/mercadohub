<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('add_item_images', 5, 60);

$user_id = $_SESSION['user_id'];
$itemId = (int)($_POST['id_items'] ?? 0);

if ($itemId <= 0) {
    echo json_encode(['error' => 'ID de item invalido.']);
    exit;
}

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT id_usuario FROM items WHERE id_items = ?");
    $check->execute([$itemId]);
    $item = $check->fetch(PDO::FETCH_ASSOC);
    if (!$item || (int)$item['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No tienes permiso para modificar este item.']);
        exit;
    }

    // Get current max orden
    $ordStmt = $pdo->prepare("SELECT COALESCE(MAX(orden), -1) AS max_orden FROM item_imagenes WHERE id_items = ?");
    $ordStmt->execute([$itemId]);
    $maxOrden = (int)$ordStmt->fetch(PDO::FETCH_ASSOC)['max_orden'];

    $uploadDir = __DIR__ . '/../public/uploads/items';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $extras = $_FILES['imagenes'] ?? null;
    if (!$extras || !is_array($extras['name'])) {
        echo json_encode(['error' => 'No se enviaron imagenes.']);
        exit;
    }

    $added = 0;
    foreach ($extras['name'] as $i => $name) {
        if ($extras['error'][$i] !== UPLOAD_ERR_OK) continue;
        $size = $extras['size'][$i];
        if ($size > 5 * 1024 * 1024) continue;

        $tmpPath = $extras['tmp_name'][$i];
        $imageData = file_get_contents($tmpPath);
        if (!$imageData) continue;

        $path = null;
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
                $path = 'public/uploads/items/' . $filename;
            }
        }
        if (!$path) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['jpg','jpeg','png','webp','gif'])) $ext = 'jpg';
            $filename = uniqid('item_') . '.' . $ext;
            $fullPath = $uploadDir . '/' . $filename;
            move_uploaded_file($tmpPath, $fullPath);
            $path = 'public/uploads/items/' . $filename;
        }

        $maxOrden++;
        $stmt3 = $pdo->prepare("INSERT INTO item_imagenes (id_items, path, orden) VALUES (?, ?, ?)");
        $stmt3->execute([$itemId, $path, $maxOrden]);
        $added++;
    }

    log_actividad('add_item_images', "Item ID: $itemId, Imagenes agregadas: $added");
    echo json_encode(['message' => "$added imagen(es) agregada(s) correctamente."]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar imagenes: ' . $e->getMessage()]);
}
?>