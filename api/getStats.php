<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    $users = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
    $trades = $pdo->query("SELECT COUNT(*) FROM intercambios WHERE estado = 'aceptado'")->fetchColumn();
    echo json_encode([
        'items' => (int)$items,
        'users' => (int)$users,
        'trades' => (int)$trades,
    ]);
} catch (Exception $e) {
    echo json_encode(['items' => 0, 'users' => 0, 'trades' => 0]);
}
