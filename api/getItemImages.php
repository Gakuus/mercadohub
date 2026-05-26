<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesion.']);
    exit;
}

$id_items = (int)($_GET['id'] ?? 0);
if ($id_items <= 0) {
    echo json_encode(['error' => 'ID de item invalido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, path, orden FROM item_imagenes WHERE id_items = ? ORDER BY orden ASC, id ASC");
    $stmt->execute([$id_items]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $images = [];
    foreach ($rows as $row) {
        $images[] = [
            'id' => (int)$row['id'],
            'url' => BASE_URL . '/' . $row['path'],
            'orden' => (int)$row['orden'],
        ];
    }

    echo json_encode($images);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al cargar imagenes: ' . $e->getMessage()]);
}
?>