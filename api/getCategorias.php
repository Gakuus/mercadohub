<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id_juegos AS id, nombre_juegos AS nombre FROM Juegos");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener las categorías: ' . $e->getMessage()]);
}
?>
