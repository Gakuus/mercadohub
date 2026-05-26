<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}
require_csrf();
check_rate_limit('forum_comment', 10, 60);

$user_id = $_SESSION['user_id'];
$input = get_json_body();
$contenido = trim($input['contenido'] ?? '');
$id_post = (int)($input['id_post'] ?? 0);

if (empty($contenido) || $id_post <= 0) {
    echo json_encode(['error' => 'El contenido y el ID del post son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO foro_comentarios (contenido, id_post, id_usuario) VALUES (?, ?, ?)");
    $stmt->execute([$contenido, $id_post, $user_id]);
    echo json_encode(['success' => true, 'message' => 'Comentario agregado.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar comentario: ' . $e->getMessage()]);
}
?>
