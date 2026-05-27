<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('forum_post', 5, 60);

$user_id = $_SESSION['user_id'];
$input = get_json_body();
$titulo = trim($input['titulo'] ?? '');
$contenido = trim($input['contenido'] ?? '');

if (empty($titulo) || empty($contenido)) {
    echo json_encode(['error' => 'El titulo y el contenido son obligatorios.']);
}

if (strlen($titulo) > 200) {
    echo json_encode(['error' => 'El titulo no puede superar los 200 caracteres.']);
}

try {
    $stmt = $pdo->prepare("INSERT INTO foro_posts (titulo, contenido, id_usuario) VALUES (?, ?, ?)");
    $stmt->execute([$titulo, $contenido, $user_id]);
    log_actividad('forum_create_post', "Titulo: $titulo");
    echo json_encode(['success' => true, 'message' => 'Post creado exitosamente.', 'id_post' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al crear el post: ' . $e->getMessage()]);
}
?>
