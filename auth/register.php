<?php
require 'config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $nombre_usuario = $data['nombre_usuario'] ?? '';
        $contrasena = $data['contrasena'] ?? '';

        if ($email && $nombre_usuario && $contrasena) {
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE nombre_usuario = ? OR email = ?');
            $stmt->execute([$nombre_usuario, $email]);
            $userExists = $stmt->fetchColumn();

            if ($userExists > 0) {
                echo json_encode(['success' => false, 'message' => 'El usuario o email ya están registrados']);
            } else {
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare('INSERT INTO usuario (nombre_usuario, contrasena, email) VALUES (?, ?, ?)');
                    $stmt->execute([$nombre_usuario, $hashed_password, $email]);

                    echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
                } catch (PDOException $e) {
                    echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
