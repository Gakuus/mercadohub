<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.id_post, p.titulo, p.created_at,
               u.nombre_usuario AS autor,
               (SELECT COUNT(*) FROM foro_comentarios c WHERE c.id_post = p.id_post) AS comentarios
        FROM foro_posts p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($posts);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener los posts: ' . $e->getMessage()]);
}
?>
