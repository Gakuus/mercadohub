<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/Login/index.html');
    exit;
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$isAdmin = is_admin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MercadoHub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="nav-inner">
                <a href="#" class="nav-brand" data-section="items-disponibles">Mercado<span>Hub</span></a>
                <button class="nav-toggle" aria-label="Menu" id="nav-toggle">
                    <span></span><span></span><span></span>
                </button>
                <div class="nav-menu" id="nav-menu">
                    <a class="nav-link active" href="#" data-section="items-disponibles">Home</a>
                    <a class="nav-link" href="#" data-section="intercambio">Agregar Item</a>
                    <a class="nav-link" href="#" data-section="inicio">Sobre nosotros</a>
                    <a class="nav-link" href="#" data-section="reglas">Reglas</a>
                    <a class="nav-link" href="#" data-section="foro">Foro</a>
                    <a class="nav-link" href="#" data-section="perfil">Perfil</a>
                    <?php if ($isAdmin): ?>
                    <a class="nav-link" href="#" data-section="admin">Admin</a>
                    <?php endif; ?>
                    <form action="<?= BASE_URL ?>/auth/logout.php" method="post" class="nav-logout-form">
                        <button type="submit" class="nav-logout-btn">Cerrar Sesion</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <div id="toast-container" class="toast-container"></div>

    <main>
        <section id="inicio" class="section">
            <h1>Bienvenido a MercadoHub</h1>
            <p>En MercadoHub Deluxe, somos mas que una plataforma de intercambio de items; somos una comunidad comprometida con la colaboracion y el apoyo mutuo. Nuestra mision es facilitar el intercambio de bienes entre personas de manera sencilla y segura, ofreciendo un espacio donde todos puedan encontrar lo que necesitan o dar nueva vida a lo que ya no usan.
Entendemos que cada item tiene un valor, ya sea funcional o sentimental, y por eso nos esforzamos en crear un entorno donde ese valor se pueda compartir con otros. Pero nuestro compromiso va mas alla del comercio: en MercadoHub Deluxe, tambien estamos dedicados a brindar apoyo a quienes mas lo necesitan, ofreciendo recursos y asistencia para ayudar a nuestra comunidad a prosperar.
Ya sea que estes buscando un item en particular, o quieras ayudar a alguien con una donacion, en MercadoHub Deluxe creemos en el poder de la solidaridad y la cooperacion. Juntos, podemos hacer una diferencia.</p>
        </section>

        <section id="reglas" class="section">
            <h1>Normas de la comunidad</h1>
            <p>Prohibicion de Items Ilegales o Restringidos: Esta estrictamente prohibido intercambiar items que sean ilegales, peligrosos o que infrinjan las leyes locales e internacionales. Esto incluye, pero no se limita a, sustancias controladas, armas, productos falsificados, y articulos robados.
            Apoyo Comunitario: Fomentamos el apoyo mutuo dentro de la comunidad. Si alguien necesita ayuda, ya sea dentro o fuera de la plataforma, y esta en tu capacidad ofrecerla, te animamos a hacerlo de manera altruista.
Transacciones Justas y Transparentes: Todas las transacciones deben ser justas y transparentes. Asegurate de que ambas partes esten de acuerdo con los terminos del intercambio antes de proceder. Cualquier intento de fraude o manipulacion resultara en la suspension de la cuenta.
Cumplimiento de Compromisos: Si te comprometes a un intercambio, es tu responsabilidad cumplir con lo acordado. No cumplir con los compromisos sin una razon valida puede llevar a la perdida de confianza dentro de la comunidad y a posibles sanciones.
Prohibicion de Actividades Comerciales: MercadoHub Deluxe es una plataforma para el intercambio de items, no para actividades comerciales. La venta de items con fines de lucro no esta permitida y si quieren intercambiar serian de usuarios MercadoHub Deluxe no saca algun tipo de lucro.
Veracidad en la Informacion: Es fundamental que toda la informacion proporcionada sobre los items que se intercambian sea precisa y veraz. No se permiten descripciones enganosas ni omisiones importantes.</p>
        </section>

        <!-- ============ FORO ============ -->
        <section id="foro" class="section">
            <div id="foro-list-view">
                <div class="foro-header">
                    <h1>Foro</h1>
                    <button id="btn-nuevo-post" class="btn-foro-nuevo">+ Nuevo Post</button>
                </div>
                <div id="foro-loader" class="loader"><div class="spinner"></div></div>
                <div id="foro-empty" class="loader">Todavia no hay posts. Se el primero en publicar.</div>
                <div id="foro-posts-list"></div>
            </div>

            <div id="foro-create-view" style="display:none;">
                <h1>Crear nuevo post</h1>
                <form id="foro-create-form">
                    <label for="foro-titulo">Titulo:</label>
                    <input type="text" id="foro-titulo" maxlength="200" required>

                    <label for="foro-contenido">Contenido:</label>
                    <textarea id="foro-contenido" rows="6" required></textarea>

                    <div class="foro-form-actions">
                        <button type="submit" class="btn-foro-submit">Publicar</button>
                        <button type="button" id="btn-cancelar-post" class="btn-foro-cancel">Cancelar</button>
                    </div>
                </form>
            </div>

            <div id="foro-detail-view" style="display:none;">
                <button id="btn-volver-foro" class="btn-foro-back">&larr; Volver al foro</button>
                <div id="foro-detail-content"></div>
            </div>
        </section>

        <!-- ============ AGREGAR ITEM ============ -->
        <section id="intercambio" class="section">
            <h1>Agregar Item</h1>
            <form id="intercambio-form">
                <label for="item-nombre">Nombre del Item:</label>
                <input type="text" id="item-nombre" name="item-nombre" required>

                <label for="item-descripcion">Descripcion:</label>
                <textarea id="item-descripcion" rows="3" placeholder="Describe el estado, detalles, etc."></textarea>

                <label for="item-precio">Precio (opcional):</label>
                <input type="number" id="item-precio" step="0.01" min="0" placeholder="0.00">

                <label for="item-imagen">Imagen del Item:</label>
                <input type="file" id="item-imagen" name="item-imagen" accept="image/*" required>
                <img id="image-preview" alt="Vista previa">

                <label for="item-categoria">Categoria:</label>
                <select id="item-categoria" name="item-categoria" required>
                    <option value="" disabled selected>Cargando...</option>
                </select>

                <button type="submit" id="btn-submit-item">Agregar Item</button>
            </form>
        </section>

        <!-- ============ ITEMS ============ -->
        <section id="items-disponibles" class="section active">
            <div id="items-top-bar">
                <select id="categoria-filter">
                    <option value="all">Todas las categorias</option>
                </select>
                <input type="text" id="search-input" placeholder="Buscar items..." autocomplete="off">
            </div>

            <div id="items-loader" class="loader"><div class="spinner"></div></div>
            <div id="items-empty" class="loader">No hay items disponibles.</div>
            <div id="items-disponibles-lista" class="pinterest-grid"></div>
            <div id="scroll-sentinel" class="scroll-sentinel"><div class="spinner"></div></div>
        </section>

        <!-- ============ ITEM DETAIL MODAL ============ -->
        <div id="item-modal" class="modal-overlay" style="display:none;">
            <div class="modal-content item-detail-modal">
                <button id="item-modal-close" class="modal-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="modal-scroll">
                    <div id="item-modal-layout">
                        <div id="item-modal-left">
                            <div id="item-modal-image"></div>
                        </div>
                        <div id="item-modal-right">
                            <div id="item-modal-info"></div>
                            <div id="item-modal-divider"></div>
                            <div id="item-modal-comments">
                                <div id="item-comments-list"></div>
                                <form id="item-comment-form">
                                    <input type="text" id="item-comment-input" placeholder="Agrega un comentario..." required autocomplete="off">
                                    <button type="submit" class="btn-comment-submit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="item-modal-related">
                        <h3 class="section-title">Tambien te puede interesar</h3>
                        <div id="related-items-grid"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ EDIT ITEM MODAL ============ -->
        <div id="edit-item-modal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <button id="edit-item-close" class="modal-close">&times;</button>
                <h2>Editar Item</h2>
                <form id="edit-item-form">
                    <input type="hidden" id="edit-item-id">

                    <label for="edit-item-nombre">Nombre:</label>
                    <input type="text" id="edit-item-nombre" required>

                    <label for="edit-item-descripcion">Descripcion:</label>
                    <textarea id="edit-item-descripcion" rows="3"></textarea>

                    <label for="edit-item-precio">Precio (opcional):</label>
                    <input type="number" id="edit-item-precio" step="0.01" min="0">

                    <label for="edit-item-categoria">Categoria:</label>
                    <select id="edit-item-categoria" required></select>

                    <label for="edit-item-imagen">Nueva imagen (opcional):</label>
                    <input type="file" id="edit-item-imagen" accept="image/*">

                    <div class="modal-form-actions">
                        <button type="submit" class="btn-foro-submit">Guardar cambios</button>
                        <button type="button" id="edit-item-cancel" class="btn-foro-cancel">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============ PUBLIC PROFILE MODAL ============ -->
        <div id="public-profile-modal" class="modal-overlay" style="display:none;">
            <div class="modal-content">
                <button id="public-profile-close" class="modal-close">&times;</button>
                <div id="public-profile-body"></div>
            </div>
        </div>

        <!-- ============ ADMIN ============ -->
        <?php if ($isAdmin): ?>
        <section id="admin" class="section">
            <h1>Panel de Administracion</h1>

            <div class="admin-section">
                <h2>Usuarios</h2>
                <div id="admin-users-loader" class="loader"><div class="spinner"></div></div>
                <div id="admin-users-list"></div>
            </div>

            <div class="admin-section">
                <h2>Categorias</h2>
                <form id="admin-categoria-form" class="admin-inline-form">
                    <input type="text" id="admin-categoria-nombre" placeholder="Nueva categoria" required>
                    <button type="submit" class="btn-foro-submit">Agregar</button>
                </form>
                <div id="admin-categorias-list"></div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============ PERFIL ============ -->
        <section id="perfil" class="section">
            <h1>Mi Perfil</h1>
            <img id="profile-image" src="" alt="Imagen de Perfil">
            <p id="profile-name"></p>
            <p id="profile-bio"></p>

            <button id="btn-editar-perfil" class="btn-edit-profile">Editar perfil</button>

            <div id="profile-edit-form" style="display:none;">
                <h3>Editar perfil</h3>
                <form id="profile-edit">
                    <label for="profile-bio-input">Biografia:</label>
                    <textarea id="profile-bio-input" rows="3"></textarea>

                    <label for="profile-image-input">Foto de perfil:</label>
                    <input type="file" id="profile-image-input" accept="image/*">

                    <div class="profile-edit-actions">
                        <button type="submit" class="btn-save-profile">Guardar</button>
                        <button type="button" id="btn-cancelar-editar-perfil" class="btn-cancel">Cancelar</button>
                    </div>
                </form>
            </div>

            <h2 id="profile-items-title">Mis Items</h2>
            <div id="profile-items-list"></div>
        </section>
    </main>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.CSRF_TOKEN = '<?= $csrfToken ?>';
        window.IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
        window.CURRENT_USER_ID = <?= json_encode($_SESSION['user_id'] ?? 0) ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
