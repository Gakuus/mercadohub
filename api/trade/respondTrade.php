<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('respond_trade', 10, 60);

$user_id = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

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
    $stmt2 = $pdo->prepare("UPDATE intercambios SET estado = ? WHERE id_intercambio = ?");
    $stmt2->execute([$nuevoEstado, $tradeId]);

    log_actividad('respond_trade', "Intercambio ID: $tradeId, Accion: $accion");
    echo json_encode(['message' => 'Intercambio ' . ($nuevoEstado) . ' correctamente.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al responder intercambio: ' . $e->getMessage()]);
}
?>