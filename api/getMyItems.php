<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT items.id_items AS id, items.nombre_items AS nombre,
               items.img_path, items.img_items AS img,
               items.items_precio AS precio,
               juegos.nombre_juegos AS categoria
        FROM items
        INNER JOIN juegos ON items.id_juegos = juegos.id_juegos
        WHERE items.id_usuario = ?
        ORDER BY items.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        if ($item['img_path']) {
            $item['img'] = BASE_URL . '/' . $item['img_path'];
        } elseif ($item['img']) {
            $item['img'] = 'data:image/png;base64,' . base64_encode($item['img']);
        }
        unset($item['img_path']);
        if ($item['precio']) {
            $item['precio'] = (float)$item['precio'];
        } else {
            $item['precio'] = null;
        }
    }

    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al cargar tus items: ' . $e->getMessage()]);
}
?>