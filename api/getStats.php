<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $items = (int)$pdo->query("SELECT COUNT(*) AS c FROM items")->fetch()['c'];
    $users = (int)$pdo->query("SELECT COUNT(*) AS c FROM usuario")->fetch()['c'];
    $trades = (int)$pdo->query("SELECT COUNT(*) AS c FROM intercambios WHERE estado = 'aceptado'")->fetch()['c'];
    echo json_encode([
        'items' => $items,
        'users' => $users,
        'trades' => $trades,
    ]);
} catch (Exception $e) {
    echo json_encode(['items' => 0, 'users' => 0, 'trades' => 0]);
}
