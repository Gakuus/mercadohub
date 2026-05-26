<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('update_item', 20, 60);

$user_id = $_SESSION['user_id'];

$input = get_json_body();

$id_items = (int)($input['id_items'] ?? 0);
$nombre_items = trim($input['nombre_items'] ?? '');
$descripcion_items = trim($input['descripcion_items'] ?? '');
$precio = $input['precio'] ?? '';
$id_juegos = (int)($input['id_juegos'] ?? 0);
$imagenUrl = $input['imagen_url'] ?? '';

if ($id_items <= 0 || empty($nombre_items)) {
    echo json_encode(['error' => 'ID y nombre del item son obligatorios.']);
    exit;
}

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT id_usuario, img_path FROM items WHERE id_items = ?");
    $check->execute([$id_items]);
    $item = $check->fetch(PDO::FETCH_ASSOC);

    if (!$item || (int)$item['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No tienes permiso para modificar este item.']);
        exit;
    }

    // Handle image update
    $imgPath = null;
    $updateImg = false;
    if (!empty($imagenUrl)) {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagenUrl));
        if ($imageData && extension_loaded('gd')) {
            $img = @imagecreatefromstring($imageData);
            if ($img) {
                $uploadDir = __DIR__ . '/../public/uploads/items';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

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

                $imgPath = 'public/uploads/items/' . $filename;
                $updateImg = true;

                // Delete old disk file
                if ($item['img_path']) {
                    $oldFile = __DIR__ . '/../' . $item['img_path'];
                    if (file_exists($oldFile)) unlink($oldFile);
                }
            }
        }
    }

    $precioVal = $precio !== '' ? (float)$precio : null;

    if ($updateImg) {
        $stmt = $pdo->prepare("UPDATE items SET nombre_items = ?, descripcion_items = ?, items_precio = ?, id_juegos = ?, img_path = ?, img_items = NULL WHERE id_items = ?");
        $stmt->execute([$nombre_items, $descripcion_items, $precioVal, $id_juegos, $imgPath, $id_items]);
    } else {
        $stmt = $pdo->prepare("UPDATE items SET nombre_items = ?, descripcion_items = ?, items_precio = ?, id_juegos = ? WHERE id_items = ?");
        $stmt->execute([$nombre_items, $descripcion_items, $precioVal, $id_juegos, $id_items]);
    }

    log_actividad('update_item', "Item ID: $id_items, Nombre: $nombre_items");
    echo json_encode(['success' => true, 'message' => 'Item actualizado con exito.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
}
?>
