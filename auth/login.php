<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Rate limiting: max 5 attempts per 15 minutes
$rateLimitKey = 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateLimitWindow = 900; // 15 minutes

if (isset($_SESSION[$rateLimitKey])) {
    $attempts = $_SESSION[$rateLimitKey];
    if ($attempts['count'] >= 5 && time() - $attempts['time'] < $rateLimitWindow) {
        echo json_encode(['success' => false, 'message' => 'Demasiados intentos. Intenta de nuevo en 15 minutos.']);
        exit;
    } elseif (time() - $attempts['time'] >= $rateLimitWindow) {
        unset($_SESSION[$rateLimitKey]);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $nombre_usuario = preg_replace('/[^a-zA-Z0-9_]/', '', $data['nombre_usuario'] ?? '');
        $contrasena = $data['contrasena'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($nombre_usuario) < 3 || empty($contrasena)) {
            echo json_encode(['success' => false, 'message' => 'Credenciales invalidas.']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id_usuario, contrasena, rol FROM usuario WHERE email = ? AND nombre_usuario = ?');
        $stmt->execute([$email, $nombre_usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($contrasena, $user['contrasena'])) {
            // Success: clear rate limit, regenerate session
            unset($_SESSION[$rateLimitKey]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            log_actividad('login', "Usuario {$nombre_usuario} inicio sesion");
            echo json_encode(['success' => true, 'message' => 'Login exitoso', 'redirect' => BASE_URL . '/public/index.php']);
        } else {
            // Track failed attempt
            $_SESSION[$rateLimitKey] = [
                'count' => ($_SESSION[$rateLimitKey]['count'] ?? 0) + 1,
                'time' => time(),
            ];
            echo json_encode(['success' => false, 'message' => 'Email, usuario o contrasena incorrectos.']);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor. Intenta de nuevo.']);
}
