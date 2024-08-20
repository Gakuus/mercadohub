<?php
session_start(); // Inicia la sesión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Destruir todas las variables de sesión
    $_SESSION = [];

    // Destruir la cookie de la sesión, si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();

    // Redirigir al usuario a la página de inicio de sesión
    header('Location: /proyecto/Login/index.html');
    exit;
} else {
    // Redirigir al usuario a la página de inicio si intentan acceder directamente
    header('Location: /proyecto/Register/index.html');
    exit;
}
?>
