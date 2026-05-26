<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_admin();
require_csrf();
check_rate_limit('admin_categoria', 10, 60);

$input = get_json_body();
$id_juegos = (int)($input['id_juegos'] ?? 0);

if ($id_juegos <= 0) {
    echo json_encode(['error' => 'ID de categoria invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM juegos WHERE id_juegos = ?");
    $stmt->execute([$id_juegos]);
    log_actividad('admin_delete_categoria', "Categoria ID: $id_juegos");
    echo json_encode(['success' => true, 'message' => 'Categoria eliminada.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al eliminar categoria: ' . $e->getMessage()]);
}
?>
