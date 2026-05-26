<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

try {
    $categoria = $_GET['categoria'] ?? 'all';
    $search = trim($_GET['search'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 12)));
    $offset = ($page - 1) * $limit;
    $userId = $_SESSION['user_id'];

    $where = [];
    $params = [];

    if ($categoria !== 'all') {
        $where[] = 'items.id_juegos = ?';
        $params[] = $categoria;
    }

    if ($search !== '') {
        $where[] = '(items.nombre_items LIKE ? OR usuario.nombre_usuario LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Get total count
    $countQuery = "SELECT COUNT(*) AS total FROM items INNER JOIN juegos ON items.id_juegos = juegos.id_juegos INNER JOIN usuario ON items.id_usuario = usuario.id_usuario $whereClause";
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get items
    $query = "SELECT items.nombre_items AS nombre,
                     items.descripcion_items AS descripcion,
                     items.items_precio AS precio,
                     items.img_items AS img,
                     items.img_path,
                     items.id_items AS id,
                     items.id_usuario,
                     items.created_at,
                     juegos.nombre_juegos AS categoria,
                     usuario.nombre_usuario AS usuario
              FROM items
              INNER JOIN juegos ON items.id_juegos = juegos.id_juegos
              INNER JOIN usuario ON items.id_usuario = usuario.id_usuario
              $whereClause
              ORDER BY items.id_items DESC
              LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        if ($item['img_path']) {
            $item['img'] = BASE_URL . '/' . $item['img_path'];
        } elseif ($item['img']) {
            $item['img'] = 'data:image/png;base64,' . base64_encode($item['img']);
        }
        unset($item['img_path']);
        $item['esPropio'] = (int)$item['id_usuario'] === (int)$userId;
    }

    echo json_encode(['items' => $items, 'total' => $total, 'page' => $page, 'limit' => $limit]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener los items: ' . $e->getMessage()]);
}
?>
