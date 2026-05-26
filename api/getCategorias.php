<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesión.']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id_juegos AS id, nombre_juegos AS nombre FROM juegos ORDER BY nombre_juegos");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener las categorías: ' . $e->getMessage()]);
}
?>
