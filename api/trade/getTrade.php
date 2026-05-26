<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$tradeId = (int)($_GET['id'] ?? 0);

if ($tradeId <= 0) {
    echo json_encode(['error' => 'ID de intercambio invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT i.id_intercambio, i.estado, i.mensaje, i.created_at, i.updated_at,
               s.id_usuario AS solicitante_id, s.nombre_usuario AS solicitante_nombre,
               r.id_usuario AS receptor_id, r.nombre_usuario AS receptor_nombre
        FROM intercambios i
        INNER JOIN usuario s ON i.id_solicitante = s.id_usuario
        INNER JOIN usuario r ON i.id_receptor = r.id_usuario
        WHERE i.id_intercambio = ?
    ");
    $stmt->execute([$tradeId]);
    $trade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trade) {
        echo json_encode(['error' => 'Intercambio no encontrado.']);
        exit;
    }

    // Check the user is part of this trade
    if ((int)$trade['solicitante_id'] !== $user_id && (int)$trade['receptor_id'] !== $user_id) {
        echo json_encode(['error' => 'No tienes permiso para ver este intercambio.']);
        exit;
    }

    // Get items
    $itStmt = $pdo->prepare("
        SELECT ii.id, ii.id_items, ii.lado, items.nombre_items, items.img_path, items.img_items
        FROM intercambio_items ii
        INNER JOIN items ON ii.id_items = items.id_items
        WHERE ii.id_intercambio = ?
    ");
    $itStmt->execute([$tradeId]);
    $items = $itStmt->fetchAll(PDO::FETCH_ASSOC);

    $trade['items_ofrecidos'] = [];
    $trade['items_solicitados'] = [];
    foreach ($items as $it) {
        $img = null;
        if ($it['img_path']) {
            $img = BASE_URL . '/' . $it['img_path'];
        } elseif ($it['img_items']) {
            $img = 'data:image/png;base64,' . base64_encode($it['img_items']);
        }
        $entry = ['id' => (int)$it['id_items'], 'nombre' => $it['nombre_items'], 'img' => $img];
        if ($it['lado'] === 'ofrece') {
            $trade['items_ofrecidos'][] = $entry;
        } else {
            $trade['items_solicitados'][] = $entry;
        }
    }

    $trade['soy_solicitante'] = (int)$trade['solicitante_id'] === $user_id;
    unset($trade['solicitante_id']);
    unset($trade['receptor_id']);

    echo json_encode($trade);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al cargar intercambio: ' . $e->getMessage()]);
}
?>