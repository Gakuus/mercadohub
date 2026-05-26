<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$profileId = (int)($_GET['id'] ?? 0);
if ($profileId <= 0) {
    echo json_encode(['error' => 'ID de usuario invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, bio, img_perfil, created_at FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$profileId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'Usuario no encontrado.']);
        exit;
    }

    if ($user['img_perfil']) {
        $user['img_perfil'] = base64_encode($user['img_perfil']);
    }

    $stmt = $pdo->prepare("SELECT id_items, nombre_items, img_path, items_precio, created_at,
                                  juegos.nombre_juegos AS categoria
                           FROM items
                           INNER JOIN juegos ON items.id_juegos = juegos.id_juegos
                           WHERE id_usuario = ?
                           ORDER BY created_at DESC
                           LIMIT 50");
    $stmt->execute([$profileId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        if ($item['img_path']) {
            $item['img'] = BASE_URL . '/' . $item['img_path'];
        } else {
            $item['img'] = null;
        }
        unset($item['img_path']);
    }

    echo json_encode([
        'user' => $user,
        'items' => $items,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener perfil: ' . $e->getMessage()]);
}
?>
