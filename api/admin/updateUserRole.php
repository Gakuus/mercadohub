<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

require_login();
require_admin();
require_csrf();
check_rate_limit('admin_role', 20, 60);

$input = get_json_body();
$id_usuario = (int)($input['id_usuario'] ?? 0);
$rol = $input['rol'] ?? '';

if ($id_usuario <= 0 || !in_array($rol, ['usuario', 'admin'])) {
    echo json_encode(['error' => 'Datos invalidos.']);
}

// Prevent admin self-demotion
if ($id_usuario === (int)$_SESSION['user_id'] && $rol !== 'admin') {
    echo json_encode(['error' => 'No puedes cambiarte el rol a ti mismo.']);
}

try {
    $stmt = $pdo->prepare("UPDATE usuario SET rol = ? WHERE id_usuario = ?");
    $stmt->execute([$rol, $id_usuario]);
    // If the updated user is the current admin, refresh session role
    if ($id_usuario === (int)$_SESSION['user_id']) {
        $_SESSION['user_rol'] = $rol;
    }
    log_actividad('admin_update_role', "Usuario ID $id_usuario -> $rol");
    echo json_encode(['success' => true, 'message' => 'Rol actualizado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar rol: ' . $e->getMessage()]);
}
?>
