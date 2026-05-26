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
    // Pending trades where user is the receptor
    $stmt = $pdo->prepare("
        SELECT i.id_intercambio, i.created_at,
               s.nombre_usuario AS solicitante_nombre,
               (SELECT COUNT(*) FROM intercambio_items WHERE id_intercambio = i.id_intercambio AND lado = 'ofrece') AS items_ofrecidos,
               (SELECT COUNT(*) FROM intercambio_items WHERE id_intercambio = i.id_intercambio AND lado = 'recibe') AS items_solicitados
        FROM intercambios i
        INNER JOIN usuario s ON i.id_solicitante = s.id_usuario
        WHERE i.id_receptor = ? AND i.estado = 'pendiente'
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$userId]);
    $pendingTrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pendingTrades as &$t) {
        $t['id_intercambio'] = (int)$t['id_intercambio'];
        $t['items_ofrecidos'] = (int)$t['items_ofrecidos'];
        $t['items_solicitados'] = (int)$t['items_solicitados'];
    }

    echo json_encode([
        'total' => count($pendingTrades),
        'pending_trades' => $pendingTrades,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al cargar notificaciones: ' . $e->getMessage()]);
}
?>