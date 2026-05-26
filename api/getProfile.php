<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesión.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT nombre_usuario, img_perfil, bio FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$userId]);
    $profileData = $stmt->fetch();

    if ($profileData) {
        if ($profileData['img_perfil']) {
            $profileData['img_perfil'] = base64_encode($profileData['img_perfil']);
        }
        echo json_encode($profileData);
    } else {
        echo json_encode(['error' => 'Perfil no encontrado.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener el perfil: ' . $e->getMessage()]);
}
?>
