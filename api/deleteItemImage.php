<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('delete_item_image', 10, 60);

$user_id = $_SESSION['user_id'];

$input = get_json_body();
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['error' => 'ID de imagen invalido.']);
}

try {
    // Get image info and check ownership
    $stmt = $pdo->prepare("
        SELECT im.id, im.path, im.orden, im.id_items, i.id_usuario
        FROM item_imagenes im
        INNER JOIN items i ON im.id_items = i.id_items
        WHERE im.id = ?
    ");
    $stmt->execute([$id]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$img) {
        echo json_encode(['error' => 'Imagen no encontrada.']);
    }

    if ((int)$img['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No tienes permiso para eliminar esta imagen.']);
    }

    if ((int)$img['orden'] === 0) {
        echo json_encode(['error' => 'No se puede eliminar la imagen principal. Cambia la portada primero.']);
    }

    // Delete file
    $filePath = __DIR__ . '/../' . $img['path'];
    if (file_exists($filePath)) unlink($filePath);

    $stmt2 = $pdo->prepare("DELETE FROM item_imagenes WHERE id = ?");
    $stmt2->execute([$id]);

    log_actividad('delete_item_image', "Imagen ID: $id, Item ID: " . $img['id_items']);
    echo json_encode(['message' => 'Imagen eliminada correctamente.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al eliminar imagen: ' . $e->getMessage()]);
}
?>