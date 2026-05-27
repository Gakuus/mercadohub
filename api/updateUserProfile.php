<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('update_profile', 5, 60);

$user_id = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $bio = trim($_POST['bio'] ?? '');
        $imagen = $_FILES['imagen'] ?? null;

        if ($imagen && $imagen['tmp_name']) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($imagen['type'], $allowedTypes)) {
                echo json_encode(['error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF y WebP.']);
            }

            $imageData = file_get_contents($imagen['tmp_name']);

            $stmt = $pdo->prepare('UPDATE usuario SET img_perfil = ?, bio = ? WHERE id_usuario = ?');
            $stmt->execute([$imageData, $bio, $user_id]);
        } else {
            $stmt = $pdo->prepare('UPDATE usuario SET bio = ? WHERE id_usuario = ?');
            $stmt->execute([$bio, $user_id]);
        }

        log_actividad('update_profile', 'Bio/imagen actualizada');
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado exitosamente']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
}
?>
