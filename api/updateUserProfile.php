<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $imagen = $_FILES['imagen'] ?? null;
        $imagenPath = '';

        if ($imagen && $imagen['tmp_name']) {
            $imagenPath = 'uploads/' . uniqid() . '-' . $imagen['name'];
            move_uploaded_file($imagen['tmp_name'], __DIR__ . '/../public/' . $imagenPath);

            $stmt = $pdo->prepare('UPDATE perfil SET imagen = ? WHERE usuario_id = ?');
            $stmt->execute([$imagenPath, $user_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Perfil actualizado exitosamente']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al actualizar el perfil: ' . $e->getMessage()]);
}
?>
