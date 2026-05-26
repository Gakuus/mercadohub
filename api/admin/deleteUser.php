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
check_rate_limit('admin_delete_user', 10, 60);

$input = get_json_body();
$id_usuario = (int)($input['id_usuario'] ?? 0);

if ($id_usuario <= 0) {
    echo json_encode(['error' => 'ID de usuario invalido.']);
    exit;
}

if ($id_usuario === (int)$_SESSION['user_id']) {
    echo json_encode(['error' => 'No puedes eliminarte a ti mismo.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    log_actividad('admin_delete_user', "Usuario ID $id_usuario eliminado");
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al eliminar usuario: ' . $e->getMessage()]);
}
?>
