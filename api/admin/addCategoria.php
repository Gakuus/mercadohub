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
$nombre = trim($input['nombre'] ?? '');

if (empty($nombre)) {
    echo json_encode(['error' => 'El nombre de la categoria es obligatorio.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO juegos (nombre_juegos) VALUES (?)");
    $stmt->execute([$nombre]);
    log_actividad('admin_add_categoria', "Categoria: $nombre");
    echo json_encode(['success' => true, 'message' => 'Categoria agregada.']);
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'UNIQUE')) {
        echo json_encode(['error' => 'La categoria ya existe.']);
    } else {
        echo json_encode(['error' => 'Error al agregar categoria: ' . $e->getMessage()]);
    }
}
?>
