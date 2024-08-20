<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $nombre_usuario = $data['nombre_usuario'] ?? '';
        $contrasena = $data['contrasena'] ?? '';

        if ($email && $nombre_usuario && $contrasena) {
            $stmt = $pdo->prepare('SELECT * FROM usuario WHERE email = ? AND nombre_usuario = ?');
            $stmt->execute([$email, $nombre_usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($contrasena, $user['contrasena'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                echo json_encode(['success' => true, 'message' => 'Login exitoso', 'redirect' => '/public/index.php']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Email, nombre de usuario o contraseña incorrectos']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
