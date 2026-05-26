<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$id_items = (int)($_GET['id'] ?? 0);
if ($id_items <= 0) {
    echo json_encode(['error' => 'ID de item invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.id_comentario, c.contenido, c.created_at,
               u.nombre_usuario AS autor, c.id_usuario
        FROM item_comentarios c
        INNER JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_items = ?
        ORDER BY c.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$id_items]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comentarios);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener comentarios: ' . $e->getMessage()]);
}
?>
