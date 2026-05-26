<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('item_comment', 10, 60);

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$contenido = trim($input['contenido'] ?? '');
$id_items = (int)($input['id_items'] ?? 0);

if (empty($contenido) || $id_items <= 0) {
    echo json_encode(['error' => 'El contenido y el ID del item son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO item_comentarios (contenido, id_items, id_usuario) VALUES (?, ?, ?)");
    $stmt->execute([$contenido, $id_items, $user_id]);
    log_actividad('item_comment', "Item ID: $id_items");
    echo json_encode(['success' => true, 'message' => 'Comentario agregado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar comentario: ' . $e->getMessage()]);
}
?>
