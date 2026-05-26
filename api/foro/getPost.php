<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$postId = (int)($_GET['id'] ?? 0);
if ($postId <= 0) {
    echo json_encode(['error' => 'ID de post invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.id_post, p.titulo, p.contenido, p.created_at,
               u.nombre_usuario AS autor, p.id_usuario
        FROM foro_posts p
        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE p.id_post = ?
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['error' => 'Post no encontrado.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT c.id_comentario, c.contenido, c.created_at,
               u.nombre_usuario AS autor, c.id_usuario
        FROM foro_comentarios c
        INNER JOIN usuario u ON c.id_usuario = u.id_usuario
        WHERE c.id_post = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$postId]);
    $post['comentarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currentUserId = $_SESSION['user_id'];
    $post['esPropio'] = (int)$post['id_usuario'] === (int)$currentUserId;

    echo json_encode($post);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener el post: ' . $e->getMessage()]);
}
?>
