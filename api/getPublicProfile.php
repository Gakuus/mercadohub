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

    // Fetch completed trades
    $stmt = $pdo->prepare("
        SELECT i.id_intercambio, i.estado, i.created_at,
               s.id_usuario AS solicitante_id, s.nombre_usuario AS solicitante_nombre,
               r.id_usuario AS receptor_id, r.nombre_usuario AS receptor_nombre
        FROM intercambios i
        INNER JOIN usuario s ON i.id_solicitante = s.id_usuario
        INNER JOIN usuario r ON i.id_receptor = r.id_usuario
        WHERE (i.id_solicitante = ? OR i.id_receptor = ?) AND i.estado = 'aceptado'
        ORDER BY i.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$profileId, $profileId]);
    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($trades as &$trade) {
        $otherName = ((int)$trade['solicitante_id'] === $profileId) ? $trade['receptor_nombre'] : $trade['solicitante_nombre'];
        $otherId = ((int)$trade['solicitante_id'] === $profileId) ? $trade['receptor_id'] : $trade['solicitante_id'];
        $trade['contraparte_nombre'] = $otherName;
        $trade['contraparte_id'] = (int)$otherId;
        $trade['rol'] = ((int)$trade['solicitante_id'] === $profileId) ? 'solicitante' : 'receptor';
        unset($trade['solicitante_id'], $trade['solicitante_nombre'], $trade['receptor_id'], $trade['receptor_nombre']);

        // Get items for this trade (limit to 4 items per side for summary)
        $itStmt = $pdo->prepare("
            SELECT ii.lado, items.nombre_items
            FROM intercambio_items ii
            INNER JOIN items ON ii.id_items = items.id_items
            WHERE ii.id_intercambio = ?
        ");
        $itStmt->execute([$trade['id_intercambio']]);
        $tradeItems = $itStmt->fetchAll(PDO::FETCH_ASSOC);
        $trade['items_ofrecidos'] = [];
        $trade['items_solicitados'] = [];
        foreach ($tradeItems as $ti) {
            if ($ti['lado'] === 'ofrece') {
                $trade['items_ofrecidos'][] = $ti['nombre_items'];
            } else {
                $trade['items_solicitados'][] = $ti['nombre_items'];
            }
        }
    }

    echo json_encode([
        'user' => $user,
        'items' => $items,
        'trades' => $trades,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener perfil: ' . $e->getMessage()]);
}
?>
