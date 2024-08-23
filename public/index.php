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
                    <a class="nav-link" href="#" data-section="intercambio">Agregar Item <span class="sr-only">(current)</span></a>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Secciones
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#" data-section="inicio">Sobre nosotros</a>
                            <a class="dropdown-item" href="#" data-section="reglas">Reglas</a>
                            <a class="dropdown-item" href="#" data-section="foro">Foro</a>
                            <a class="dropdown-item" href="#" data-section="perfil">Perfil</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <form action="/proyecto/auth/logout.php" method="post">
                            <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
                <form class="form-inline my-2 my-lg-0">
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
            <p>Trade de items de videojuegos</p>
        </section>

        <!-- aca van las reglas proximamente tanto de la publicacion de items como el foro-->
        <section id="reglas" class="section">
            <h1>Reglas del Sitio</h1>
            <p>Aquí van las reglas del sitio.</p>
        </section>

        <!-- aca va ir un foro q se tiene q programar proximamente-->
        <section id="foro" class="section">
            <h1>Foro</h1>
            <p>Aquí va el foro funcional.</p>
        </section>


<!-- apartado para agregar items-->
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
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Agregar Item</button>
    </form>
</section>

<!-- aca aparecen todos los items subidos en la BD -->
<section id="items-disponibles" class="section active">   
    <select id="categoria-filter">
    </select>

    <div id="items-disponibles-lista" class="pinterest-grid"></div>
</section>


        <!-- apartado de perfil proximamente -->
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
