<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado.']);
    exit;
}

$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$nombre = $input['nombre'] ?? '';
$url = $input['url'] ?? '';
$categoria = $input['categoria'] ?? '';

try {
    // Decodificar la imagen de la cadena base64
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $url));

    $stmt = $pdo->prepare("INSERT INTO Items (nombre_items, descripcion_items, img_items, id_juegos, id_usuario) VALUES (:nombre, '', :img, :id_juegos, :id_usuario)");
    $stmt->execute([
        ':nombre' => $nombre,
        ':img' => $imageData,
        ':id_juegos' => $categoria,
        ':id_usuario' => $user_id
    ]);

    echo json_encode(['message' => 'Item agregado exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al agregar el ítem: ' . $e->getMessage()]);
}
?>
