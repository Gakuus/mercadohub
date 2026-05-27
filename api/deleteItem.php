<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('delete_item', 20, 60);

$user_id = $_SESSION['user_id'];

$data = get_json_body();
$id_items = (int)($data['id_items'] ?? 0);

if ($id_items <= 0) {
    echo json_encode(['error' => 'ID del item es obligatorio.']);
}

try {
    // Verify ownership and get img_path
    $check = $pdo->prepare("SELECT id_usuario, img_path FROM items WHERE id_items = ?");
    $check->execute([$id_items]);
    $item = $check->fetch(PDO::FETCH_ASSOC);

    if (!$item || (int)$item['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No tienes permiso para eliminar este item.']);
    }

    // Delete disk file if exists
    if ($item['img_path']) {
        $filePath = __DIR__ . '/../' . $item['img_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM items WHERE id_items = ?");
    $stmt->execute([$id_items]);
    log_actividad('delete_item', "Item ID: $id_items");
    echo json_encode(['success' => true, 'message' => 'Item eliminado con exito.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>
