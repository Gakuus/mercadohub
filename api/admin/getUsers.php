<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_admin();

try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, email, rol, created_at FROM usuario ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
}
?>
