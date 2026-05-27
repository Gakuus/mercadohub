<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_rate_limit('register', 3, 900);

        $data = json_decode(file_get_contents('php://input'), true);

        // CSRF check
        $submittedToken = $data['csrf_token'] ?? '';
        if (!validate_csrf($submittedToken)) {
            echo json_encode(['success' => false, 'message' => 'Token de seguridad invalido. Recarga la pagina.']);
            exit;
        }

        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $nombre_usuario = preg_replace('/[^a-zA-Z0-9_]/', '', $data['nombre_usuario'] ?? '');
        $contrasena = $data['contrasena'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Correo electronico invalido.']);
            exit;
        }

        if (strlen($nombre_usuario) < 3 || strlen($nombre_usuario) > 30) {
            echo json_encode(['success' => false, 'message' => 'El usuario debe tener entre 3 y 30 caracteres.']);
            exit;
        }

        if (strlen($contrasena) < 8) {
            echo json_encode(['success' => false, 'message' => 'La contrasena debe tener al menos 8 caracteres.']);
            exit;
        }

        if (!preg_match('/[a-z]/', $contrasena) || !preg_match('/[A-Z]/', $contrasena) || !preg_match('/\d/', $contrasena)) {
            echo json_encode(['success' => false, 'message' => 'La contrasena debe incluir mayuscula, minuscula y numero.']);
            exit;
        }

        $hashed_password = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare('INSERT INTO usuario (nombre_usuario, contrasena, email) VALUES (?, ?, ?)');
        $stmt->execute([$nombre_usuario, $hashed_password, $email]);

        echo json_encode(['success' => true, 'message' => 'Cuenta creada exitosamente']);
    }
} catch (RuntimeException $e) {
    $msg = $e->getMessage();
    if (str_contains($msg, 'nombre_usuario')) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya esta en uso.']);
    } elseif (str_contains($msg, 'email')) {
        echo json_encode(['success' => false, 'message' => 'El correo electronico ya esta registrado.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor. Intenta de nuevo.']);
}
