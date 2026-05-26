<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('forum_delete', 10, 60);

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$id_post = (int)($input['id_post'] ?? 0);

if ($id_post <= 0) {
    echo json_encode(['error' => 'ID de post invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM foro_posts WHERE id_post = ?");
    $stmt->execute([$id_post]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['error' => 'Post no encontrado.']);
        exit;
    }

    if ((int)$post['id_usuario'] !== (int)$user_id) {
        echo json_encode(['error' => 'No puedes eliminar un post que no te pertenece.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM foro_posts WHERE id_post = ?");
    $stmt->execute([$id_post]);
    log_actividad('forum_delete_post', "Post ID: $id_post");
    echo json_encode(['success' => true, 'message' => 'Post eliminado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al eliminar el post: ' . $e->getMessage()]);
}
?>
