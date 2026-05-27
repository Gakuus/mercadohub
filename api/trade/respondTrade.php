<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('respond_trade', 10, 60);

$user_id = (int)$_SESSION['user_id'];
$input = get_json_body();

$tradeId = (int)($input['id_intercambio'] ?? 0);
$accion = $input['accion'] ?? ''; // aceptar or rechazar

if ($tradeId <= 0 || !in_array($accion, ['aceptar', 'rechazar'])) {
    echo json_encode(['error' => 'Parametros invalidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_intercambio, id_solicitante, id_receptor, estado FROM intercambios WHERE id_intercambio = ?");
    $stmt->execute([$tradeId]);
    $trade = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trade) {
        echo json_encode(['error' => 'Intercambio no encontrado.']);
        exit;
    }

    if ((int)$trade['id_receptor'] !== $user_id) {
        echo json_encode(['error' => 'Solo el receptor puede responder a este intercambio.']);
        exit;
    }

    if ($trade['estado'] !== 'pendiente') {
        echo json_encode(['error' => 'Este intercambio ya fue respondido.']);
        exit;
    }

    $nuevoEstado = $accion === 'aceptar' ? 'aceptado' : 'rechazado';

    if ($accion === 'aceptar') {
        // Transfer ownership of items
        $itemsStmt = $pdo->prepare("SELECT id_items, lado FROM intercambio_items WHERE id_intercambio = ?");
        $itemsStmt->execute([$tradeId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo->beginTransaction();

        foreach ($items as $item) {
            // Offered items (by solicitante) -> go to receptor
            // Requested items (from receptor) -> go to solicitante
            $newOwnerId = $item['lado'] === 'ofrece'
                ? (int)$trade['id_receptor']
                : (int)$trade['id_solicitante'];

            $updateStmt = $pdo->prepare("UPDATE items SET id_usuario = ? WHERE id_items = ?");
            $updateStmt->execute([$newOwnerId, (int)$item['id_items']]);
        }

    }

    $stmt2 = $pdo->prepare("UPDATE intercambios SET estado = ? WHERE id_intercambio = ?");
    $stmt2->execute([$nuevoEstado, $tradeId]);

    if ($accion === 'aceptar') {
        $pdo->commit();
    }

    log_actividad('respond_trade', "Intercambio ID: $tradeId, Accion: $accion");
    echo json_encode(['message' => 'Intercambio ' . ($nuevoEstado) . ' correctamente.']);
} catch (Exception $e) {
    if (isset($pdo) && $accion === 'aceptar') {
        try { $pdo->rollBack(); } catch (Exception $ignored) {}
    }
    echo json_encode(['error' => 'Error al responder intercambio: ' . $e->getMessage()]);
}
?>