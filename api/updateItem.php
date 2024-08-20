<?php
require 'config.php';

header('Content-Type: application/json');

// Obtén datos del cuerpo de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

$id_items = $data['id_items'];
$nombre_items = $data['nombre_items'];
$descripcion_items = $data['descripcion_items'];
$items_precio = $data['items_precio'];
$id_juegos = $data['id_juegos'];
$id_usuario = $data['id_usuario'];

// Validación básica
if (empty($id_items) || empty($nombre_items) || empty($descripcion_items) || empty($items_precio) || empty($id_juegos) || empty($id_usuario)) {
    echo json_encode(['error' => 'Todos los campos son obligatorios.']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE Items SET nombre_items = ?, descripcion_items = ?, items_precio = ?, id_juegos = ?, id_usuario = ? WHERE id_items = ?");
    $stmt->execute([$nombre_items, $descripcion_items, $items_precio, $id_juegos, $id_usuario, $id_items]);
    echo json_encode(['success' => 'Ítem actualizado con éxito.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
