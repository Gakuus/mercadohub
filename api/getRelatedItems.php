<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$id_items = (int)($_GET['id'] ?? 0);
$limit = min(20, max(1, (int)($_GET['limit'] ?? 8)));

if ($id_items <= 0) {
    echo json_encode(['error' => 'ID de item invalido.']);
    exit;
}

try {
    // Get the current item's category
    $stmt = $pdo->prepare("SELECT id_juegos FROM items WHERE id_items = ?");
    $stmt->execute([$id_items]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['error' => 'Item no encontrado.']);
        exit;
    }

    $categoryId = $item['id_juegos'];
    $userId = $_SESSION['user_id'];

    // Find related items: same category, exclude current item
    $stmt = $pdo->prepare("
        SELECT items.nombre_items AS nombre,
               items.img_path,
               items.img_items AS img,
               items.items_precio AS precio,
               items.id_items AS id,
               items.id_usuario,
               juegos.nombre_juegos AS categoria,
               usuario.nombre_usuario AS usuario
        FROM items
        INNER JOIN juegos ON items.id_juegos = juegos.id_juegos
        INNER JOIN usuario ON items.id_usuario = usuario.id_usuario
        WHERE items.id_juegos = ? AND items.id_items != ?
        ORDER BY items.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$categoryId, $id_items, $limit]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$i) {
        if ($i['img_path']) {
            $i['img'] = BASE_URL . '/' . $i['img_path'];
        } elseif ($i['img']) {
            $i['img'] = 'data:image/png;base64,' . base64_encode($i['img']);
        }
        unset($i['img_path']);
        $i['esPropio'] = (int)$i['id_usuario'] === (int)$userId;
    }

    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener recomendaciones: ' . $e->getMessage()]);
}
?>
