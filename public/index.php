<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /proyecto/Login/index.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MercadoHub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand" href="#" data-section="items-disponibles">MercadoHub</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="#" data-section="items-disponibles">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="intercambio">Agregar Item <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="inicio">Sobre nosotros<span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="reglas">Reglas</a><span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="foro">Foro<span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="perfil">Perfil<span class="sr-only">(current)</span></a>
                    </li>
                  
                    <li class="nav-item">
                        <form action="/proyecto/auth/logout.php" method="post">
                            <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
                <form id="navbar-search-form" class="form-inline my-2 my-lg-0">
                    <input class="form-control mr-sm-2" id="navbar-search" type="search" placeholder="Buscar" aria-label="Search">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Buscar</button>
                </form>
            </div>
        </nav>
    </header>

    <main>
        <!-- apartado sobre nosotros-->
        <section id="inicio" class="section">
            <h1>Bienvenido a MercadoHub</h1>
            <p>En MercadoHub Deluxe, somos más que una plataforma de intercambio de ítems; somos una comunidad comprometida con la colaboración y el apoyo mutuo. Nuestra misión es facilitar el intercambio de bienes entre personas de manera sencilla y segura, ofreciendo un espacio donde todos puedan encontrar lo que necesitan o dar nueva vida a lo que ya no usan.
Entendemos que cada ítem tiene un valor, ya sea funcional o sentimental, y por eso nos esforzamos en crear un entorno donde ese valor se pueda compartir con otros. Pero nuestro compromiso va más allá del comercio: en MercadoHub Deluxe, también estamos dedicados a brindar apoyo a quienes más lo necesitan, ofreciendo recursos y asistencia para ayudar a nuestra comunidad a prosperar.
Ya sea que estés buscando un ítem en particular, o quieras ayudar a alguien con una donación, en MercadoHub Deluxe creemos en el poder de la solidaridad y la cooperación. Juntos, podemos hacer una diferencia.</p>
        </section>

        <!-- Reglas -->
        <section id="reglas" class="section">
            <h1>Normas de la comunidad</h1>
            <p>Prohibición de Ítems Ilegales o Restringidos: Está estrictamente prohibido intercambiar ítems que sean ilegales, peligrosos o que infrinjan las leyes locales e internacionales. Esto incluye, pero no se limita a, sustancias controladas, armas, productos falsificados, y artículos robados.
            Apoyo Comunitario: Fomentamos el apoyo mutuo dentro de la comunidad. Si alguien necesita ayuda, ya sea dentro o fuera de la plataforma, y está en tu capacidad ofrecerla, te animamos a hacerlo de manera altruista.
Transacciones Justas y Transparentes: Todas las transacciones deben ser justas y transparentes. Asegúrate de que ambas partes estén de acuerdo con los términos del intercambio antes de proceder. Cualquier intento de fraude o manipulación resultará en la suspensión de la cuenta.
Cumplimiento de Compromisos: Si te comprometes a un intercambio, es tu responsabilidad cumplir con lo acordado. No cumplir con los compromisos sin una razón válida puede llevar a la pérdida de confianza dentro de la comunidad y a posibles sanciones.
Prohibición de Actividades Comerciales: MercadoHub Deluxe es una plataforma para el intercambio de ítems, no para actividades comerciales. La venta de ítems con fines de lucro no está permitida y si quieren intercambiar serian de usuarios MercadoHub Deluxe no saca algun tipo de lucro.
Veracidad en la Información: Es fundamental que toda la información proporcionada sobre los ítems que se intercambian sea precisa y veraz. No se permiten descripciones engañosas ni omisiones importantes.</p>
        </section>

        <!-- Foro -->
        <section id="foro" class="section">
            <h1>Foro</h1>
            <p>Aquí va el foro funcional.</p>
        </section>

        <!-- Agregar Items -->
        <section id="intercambio" class="section">
            <h1>Agregar Items</h1>
            <form id="intercambio-form">
                <div class="form-group">
                    <label for="item-nombre">Nombre del Item:</label>
                    <input type="text" id="item-nombre" name="item-nombre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="item-imagen">Imagen del Item:</label>
                    <input type="file" id="item-imagen" name="item-imagen" class="form-control" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="item-categoria">Categoría del Juego:</label>
                    <select id="item-categoria" name="item-categoria" class="form-control" required>
                        <!-- Opciones serán cargadas dinámicamente -->
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Agregar Item</button>
            </form>
        </section>

        <!-- Mostrar Items Disponibles -->
        <section id="items-disponibles" class="section active">   
            <select id="categoria-filter">
            </select>

            <div id="items-disponibles-lista" class="pinterest-grid">
            </div>
        </section>

        <section id="perfil" class="section">
            <h1>Perfil</h1>
            <img id="profile-image" src="" alt="Imagen de Perfil">
            <p id="profile-name"></p>
            <p id="profile-bio"></p>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
