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
            <div class="info-card">
                <div class="info-hero">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    <h1>Bienvenido a MercadoHub</h1>
                </div>
                <div class="info-body">
                    <p>Somos mas que una plataforma de intercambio de items; somos una comunidad comprometida con la colaboracion y el apoyo mutuo.</p>
                    <div class="info-grid">
                        <div class="info-grid-item">
                            <div class="info-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                            </div>
                            <h3>Intercambio seguro</h3>
                            <p>Facilitamos el intercambio de bienes entre personas de manera sencilla y segura.</p>
                        </div>
                        <div class="info-grid-item">
                            <div class="info-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <h3>Apoyo comunitario</h3>
                            <p>Fomentamos el apoyo mutuo dentro de la comunidad para ayudar a quienes mas lo necesitan.</p>
                        </div>
                        <div class="info-grid-item">
                            <div class="info-grid-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </div>
                            <h3>Solidaridad</h3>
                            <p>Creemos en el poder de la solidaridad y la cooperacion. Juntos podemos hacer una diferencia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="reglas" class="section">
            <div class="info-card">
                <div class="info-hero">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ff6b7a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <h1>Normas de la comunidad</h1>
                </div>
                <div class="info-body">
                    <div class="rules-list">
                        <div class="rule-item">
                            <div class="rule-icon rule-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </div>
                            <div>
                                <strong>Items Ilegales o Restringidos</strong>
                                <p>Prohibido intercambiar items ilegales, peligrosos o que infrinjan leyes. Incluye sustancias controladas, armas, productos falsificados y articulos robados.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon rule-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <strong>Apoyo Comunitario</strong>
                                <p>Fomentamos el apoyo mutuo. Si alguien necesita ayuda y esta en tu capacidad ofrecerla, te animamos a hacerlo de manera altruista.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon rule-warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div>
                                <strong>Transacciones Justas</strong>
                                <p>Todas las transacciones deben ser justas y transparentes. Cualquier intento de fraude resultara en la suspension de la cuenta.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon rule-warning">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <div>
                                <strong>Cumplimiento de Compromisos</strong>
                                <p>Si te comprometes a un intercambio, es tu responsabilidad cumplir con lo acordado. El incumplimiento puede llevar a sanciones.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon rule-danger">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M12 18v4"/><path d="M4.93 4.93l2.83 2.83"/><path d="M16.24 16.24l2.83 2.83"/><path d="M2 12h4"/><path d="M18 12h4"/><path d="M4.93 19.07l2.83-2.83"/><path d="M16.24 7.76l2.83-2.83"/></svg>
                            </div>
                            <div>
                                <strong>Prohibicion Comercial</strong>
                                <p>MercadoHub es una plataforma para el intercambio, no para actividades comerciales. La venta con fines de lucro no esta permitida.</p>
                            </div>
                        </div>
                        <div class="rule-item">
                            <div class="rule-icon rule-success">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            </div>
                            <div>
                                <strong>Veracidad en la Informacion</strong>
                                <p>Toda la informacion sobre los items debe ser precisa y veraz. No se permiten descripciones enganosas ni omisiones importantes.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ FORO ============ -->
        <section id="foro" class="section">
            <div id="foro-list-view">
                <div class="foro-header">
                    <h1>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Foro
                    </h1>
                    <button id="btn-nuevo-post" class="btn-foro-nuevo">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Nuevo Post
                    </button>
                </div>
                <div id="foro-loader" class="loader"><div class="spinner"></div></div>
                <div id="foro-empty" class="foro-empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <p>Todavia no hay posts. Se el primero en publicar.</p>
                </div>
                <div id="foro-posts-list"></div>
            </div>

            <div id="foro-create-view" style="display:none;">
                <div class="foro-create-card">
                    <div class="foro-create-header">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Crear nuevo post</span>
                    </div>
                    <form id="foro-create-form">
                        <div class="foro-field">
                            <label for="foro-titulo">Titulo</label>
                            <input type="text" id="foro-titulo" maxlength="200" placeholder="Titulo de tu post..." required>
                        </div>
                        <div class="foro-field">
                            <label for="foro-contenido">Contenido</label>
                            <textarea id="foro-contenido" rows="6" placeholder="Escribe tu mensaje aqui..." required></textarea>
                        </div>
                        <div class="foro-form-actions">
                            <button type="submit" class="btn-foro-submit">Publicar</button>
                            <button type="button" id="btn-cancelar-post" class="btn-foro-cancel">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="foro-detail-view" style="display:none;">
                <button id="btn-volver-foro" class="btn-foro-back">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Volver
                </button>
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
            <div id="items-skeleton" class="pinterest-grid skeleton-grid"></div>
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
            <div class="modal-content edit-modal-content">
                <button id="edit-item-close" class="modal-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="edit-modal-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Editar Item
                </div>
                <form id="edit-item-form">
                    <input type="hidden" id="edit-item-id">

                    <div class="edit-field">
                        <label for="edit-item-nombre">Nombre</label>
                        <input type="text" id="edit-item-nombre" placeholder="Nombre del item" required>
                    </div>

                    <div class="edit-field">
                        <label for="edit-item-descripcion">Descripcion</label>
                        <textarea id="edit-item-descripcion" rows="3" placeholder="Describe el estado, detalles..."></textarea>
                    </div>

                    <div class="edit-field-row">
                        <div class="edit-field" style="flex:1">
                            <label for="edit-item-precio">Precio</label>
                            <input type="number" id="edit-item-precio" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="edit-field" style="flex:1">
                            <label for="edit-item-categoria">Categoria</label>
                            <select id="edit-item-categoria" required></select>
                        </div>
                    </div>

                    <div class="edit-field">
                        <label for="edit-item-imagen">Nueva imagen (opcional)</label>
                        <div class="edit-file-wrap">
                            <input type="file" id="edit-item-imagen" accept="image/*">
                            <span class="edit-file-label">Seleccionar archivo</span>
                        </div>
                    </div>

                    <div class="edit-form-actions">
                        <button type="submit" class="btn-edit-save">Guardar cambios</button>
                        <button type="button" id="edit-item-cancel" class="btn-edit-cancel">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============ PUBLIC PROFILE MODAL ============ -->
        <div id="public-profile-modal" class="modal-overlay" style="display:none;">
            <div class="modal-content pp-modal-content">
                <button id="public-profile-close" class="modal-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div id="public-profile-body"></div>
            </div>
        </div>

        <!-- ============ ADMIN ============ -->
        <?php if ($isAdmin): ?>
        <section id="admin" class="section">
            <div class="admin-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <h1>Panel de Administracion</h1>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Usuarios
                </div>
                <div class="admin-card-body">
                    <div id="admin-users-loader" class="loader"><div class="spinner"></div></div>
                    <div id="admin-users-list"></div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    Categorias
                </div>
                <div class="admin-card-body">
                    <form id="admin-categoria-form" class="admin-inline-form">
                        <input type="text" id="admin-categoria-nombre" placeholder="Nueva categoria" required>
                        <button type="submit" class="btn-foro-submit">Agregar</button>
                    </form>
                    <div id="admin-categorias-list"></div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- ============ PERFIL ============ -->
        <section id="perfil" class="section">
            <div class="profile-card">
                <div class="profile-card-header">
                    <div class="profile-avatar-wrap">
                        <img id="profile-image" src="" alt="Imagen de Perfil">
                    </div>
                    <h1 id="profile-name"></h1>
                    <p id="profile-bio"></p>
                    <button id="btn-editar-perfil" class="btn-edit-profile">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar perfil
                    </button>
                </div>

                <div id="profile-edit-form" style="display:none;">
                    <h3>Editar perfil</h3>
                    <form id="profile-edit">
                        <label for="profile-bio-input">Biografia:</label>
                        <textarea id="profile-bio-input" rows="3" placeholder="Cuenta algo sobre ti..."></textarea>

                        <label for="profile-image-input">Foto de perfil:</label>
                        <input type="file" id="profile-image-input" accept="image/*">

                        <div class="profile-edit-actions">
                            <button type="submit" class="btn-save-profile">Guardar cambios</button>
                            <button type="button" id="btn-cancelar-editar-perfil" class="btn-cancel">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="profile-items-section">
                <h2 id="profile-items-title">Mis Items</h2>
                <div id="profile-items-list"></div>
            </div>
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
