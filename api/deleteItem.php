<?php
require 'config.php';

header('Content-Type: application/json');

// Obtén datos del cuerpo de la solicitud POST
$data = json_decode(file_get_contents('php://input'), true);

$id_items = $data['id_items'];

// Validación básica
if (empty($id_items)) {
    echo json_encode(['error' => 'ID del ítem es obligatorio.']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM Items WHERE id_items = ?");
    $stmt->execute([$id_items]);
    echo json_encode(['success' => 'Ítem eliminado con éxito.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
