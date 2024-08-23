<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

try {
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'all';
    
    $query = "SELECT Items.nombre_items AS nombre, 
                     Items.img_items AS img, 
                     Juegos.nombre_juegos AS categoria, 
                     Usuario.nombre_usuario AS usuario
              FROM Items
              INNER JOIN Juegos ON Items.id_juegos = Juegos.id_juegos
              INNER JOIN Usuario ON Items.id_usuario = Usuario.id_usuario";
    
    if ($categoria != 'all') {
        $query .= " WHERE Items.id_juegos = :categoria";
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($categoria != 'all') {
        $stmt->bindParam(':categoria', $categoria, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir la imagen a base64 para poder mostrarla en el frontend
    foreach ($items as &$item) {
        $item['img'] = 'data:image/png;base64,' . base64_encode($item['img']);
    }

    echo json_encode($items);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener los ítems: ' . $e->getMessage()]);
}
?>
