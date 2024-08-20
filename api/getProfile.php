<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No has iniciado sesión.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener la información del perfil
$sql = "SELECT nombre_usuario, FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profileData = $result->fetch_assoc();
    // Asegurarse de que la imagen se devuelva en base64
    $profileData['img_perfil'] = base64_encode($profileData['img_perfil']);
    echo json_encode($profileData);
} else {
    echo json_encode(['error' => 'Perfil no encontrado.']);
}

$stmt->close();
$conn->close();
?>
