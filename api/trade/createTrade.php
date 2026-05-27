<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

require_login();
require_csrf();
check_rate_limit('create_trade', 5, 60);

$user_id = (int)$_SESSION['user_id'];
$input = get_json_body();

$receptor_id = (int)($input['id_receptor'] ?? 0);
$items_ofrecidos = $input['items_ofrecidos'] ?? []; // array of item IDs
$items_solicitados = $input['items_solicitados'] ?? [];
$mensaje = trim($input['mensaje'] ?? '');

if ($receptor_id <= 0 || $receptor_id === $user_id) {
    echo json_encode(['error' => 'Receptor invalido.']);
    exit;
}
if (empty($items_ofrecidos) || empty($items_solicitados)) {
    echo json_encode(['error' => 'Debes ofrecer y solicitar al menos un item cada uno.']);
    exit;
}

try {
    // Verify offered items belong to current user
    $placeholders = implode(',', array_fill(0, count($items_ofrecidos), '?'));
    $stmt = $pdo->prepare("SELECT id_items, id_usuario FROM items WHERE id_items IN ($placeholders)");
    $stmt->execute($items_ofrecidos);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        if ((int)$r['id_usuario'] !== $user_id) {
            echo json_encode(['error' => 'No puedes ofrecer items que no te pertenecen.']);
            exit;
        }
    }

    // Verify requested items belong to the receptor
    $placeholders2 = implode(',', array_fill(0, count($items_solicitados), '?'));
    $stmt2 = $pdo->prepare("SELECT id_items, id_usuario FROM items WHERE id_items IN ($placeholders2)");
    $stmt2->execute($items_solicitados);
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $r) {
        if ((int)$r['id_usuario'] !== $receptor_id) {
            echo json_encode(['error' => 'Solo puedes solicitar items del usuario destino.']);
            exit;
        }
    }

    // Check no active pending trade between these users for the same items
    $check = $pdo->prepare("
        SELECT id_intercambio FROM intercambios
        WHERE ((id_solicitante = ? AND id_receptor = ?) OR (id_solicitante = ? AND id_receptor = ?))
        AND estado = 'pendiente'
        LIMIT 1
    ");
    $check->execute([$user_id, $receptor_id, $receptor_id, $user_id]);
    if ($check->fetch()) {
        echo json_encode(['error' => 'Ya tienes un intercambio pendiente con este usuario.']);
        exit;
    }

    $pdo->beginTransaction();

    $stmt3 = $pdo->prepare("INSERT INTO intercambios (id_solicitante, id_receptor, mensaje) VALUES (?, ?, ?)");
    $stmt3->execute([$user_id, $receptor_id, $mensaje]);
    $tradeId = $pdo->lastInsertId();

    $stmt4 = $pdo->prepare("INSERT INTO intercambio_items (id_intercambio, id_items, lado) VALUES (?, ?, 'ofrece')");
    foreach ($items_ofrecidos as $oid) {
        $stmt4->execute([$tradeId, (int)$oid]);
    }

    $stmt5 = $pdo->prepare("INSERT INTO intercambio_items (id_intercambio, id_items, lado) VALUES (?, ?, 'recibe')");
    foreach ($items_solicitados as $sid) {
        $stmt5->execute([$tradeId, (int)$sid]);
    }

    $pdo->commit();

    log_actividad('create_trade', "Intercambio ID: $tradeId, Receptor ID: $receptor_id");
    echo json_encode(['message' => 'Intercambio creado exitosamente.', 'id_intercambio' => $tradeId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error al crear intercambio: ' . $e->getMessage()]);
}
?>