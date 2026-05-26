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
check_rate_limit('admin_role', 20, 60);

$input = json_decode(file_get_contents('php://input'), true);
$id_usuario = (int)($input['id_usuario'] ?? 0);
$rol = $input['rol'] ?? '';

if ($id_usuario <= 0 || !in_array($rol, ['usuario', 'admin'])) {
    echo json_encode(['error' => 'Datos invalidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE usuario SET rol = ? WHERE id_usuario = ?");
    $stmt->execute([$rol, $id_usuario]);
    log_actividad('admin_update_role', "Usuario ID $id_usuario -> $rol");
    echo json_encode(['success' => true, 'message' => 'Rol actualizado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar rol: ' . $e->getMessage()]);
}
?>
