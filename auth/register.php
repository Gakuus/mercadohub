<?php
require 'config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        // Sanitize and validate input
        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $nombre_usuario = filter_var($data['nombre_usuario'] ?? '', FILTER_SANITIZE_STRING);
        $contrasena = $data['contrasena'] ?? '';

        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($nombre_usuario) && !empty($contrasena)) {
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare('INSERT INTO usuario (nombre_usuario, contrasena, email) VALUES (?, ?, ?)');
                $stmt->execute([$nombre_usuario, $hashed_password, $email]);

                echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos correctamente']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
