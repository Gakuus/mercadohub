<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesión.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT items.id_items, items.nombre_items, items.img_items
        FROM items
        WHERE items.id_usuario = ?
        ORDER BY items.id_items DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        if ($item['img_items']) {
            $item['img'] = base64_encode($item['img_items']);
        } else {
            $item['img'] = null;
        }
        unset($item['img_items']);
    }

    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener tus items: ' . $e->getMessage()]);
}
