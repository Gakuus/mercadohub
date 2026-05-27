<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('set_item_cover', 10, 60);

$user_id = $_SESSION['user_id'];

$input = get_json_body();
$imageId = (int)($input['id'] ?? 0);

if ($imageId <= 0) {
    echo json_encode(['error' => 'ID de imagen invalido.']);
}

try {
    // Get image info and check ownership
    $stmt = $pdo->prepare("
        SELECT im.id, im.path, im.id_items, i.id_usuario
        FROM item_imagenes im
        INNER JOIN items i ON im.id_items = i.id_items
        WHERE im.id = ?
    ");
    $stmt->execute([$imageId]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$img) {
        echo json_encode(['error' => 'Imagen no encontrada.']);
    }

    if ((int)$img['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No tienes permiso para modificar este item.']);
    }

    $itemId = $img['id_items'];

    // Set all images of this item to orden >= 1, then set this one to 0
    $pdo->prepare("UPDATE item_imagenes SET orden = orden + 1 WHERE id_items = ? AND orden = 0")->execute([$itemId]);
    // Actually, simpler: just set the current cover (orden=0) to some other number, and set this one to 0
    $pdo->prepare("UPDATE item_imagenes SET orden = 999 WHERE id_items = ? AND orden = 0")->execute([$itemId]);
    $pdo->prepare("UPDATE item_imagenes SET orden = 0 WHERE id = ?")->execute([$imageId]);
    // Re-sequence
    $pdo->prepare("UPDATE item_imagenes SET orden = 0 WHERE id_items = ? AND orden = 999")->execute([$itemId]);

    // Also update items.img_path
    $pdo->prepare("UPDATE items SET img_path = ? WHERE id_items = ?")->execute([$img['path'], $itemId]);

    log_actividad('set_item_cover', "Item ID: $itemId, Imagen ID: $imageId");
    echo json_encode(['message' => 'Imagen de portada actualizada.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar portada: ' . $e->getMessage()]);
}
?>