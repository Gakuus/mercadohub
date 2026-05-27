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
                <a href="#" class="nav-brand" data-section="inicio">Mercado<span>Hub</span></a>
                <button class="nav-toggle" aria-label="Menu" id="nav-toggle">
                    <span></span><span></span><span></span>
                </button>
                <div class="nav-menu" id="nav-menu">
                    <a class="nav-link active" href="#" data-section="inicio">Inicio</a>
                    <a class="nav-link" href="#" data-section="items-disponibles">Items</a>
                    <a class="nav-link" href="#" data-section="intercambio">Agregar Item</a>
                    <a class="nav-link" href="#" data-section="reglas">Reglas</a>
                    <a class="nav-link" href="#" data-section="foro">Foro</a>
                    <a class="nav-link" href="#" data-section="perfil">Perfil</a>
                    <a class="nav-link" href="#" data-section="intercambios">Intercambios</a>
                    <?php if ($isAdmin): ?>
                    <a class="nav-link" href="#" data-section="admin">Admin</a>
                    <?php endif; ?>
                    <button id="theme-toggle" class="nav-icon-btn" title="Cambiar tema">
                        <svg class="theme-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                        <svg class="theme-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <div class="nav-notif-wrap">
                        <button id="notif-bell" class="nav-notif-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <span id="notif-badge" class="nav-notif-badge" style="display:none">0</span>
                        </button>
                        <div id="notif-dropdown" class="notif-dropdown" style="display:none">
                            <div class="notif-dropdown-header">Notificaciones</div>
                            <div id="notif-list" class="notif-list"></div>
                        </div>
                    </div>
                    <form action="<?= BASE_URL ?>/auth/logout.php" method="post" class="nav-logout-form">
                        <button type="submit" class="nav-logout-btn">Cerrar Sesion</button>
                    </form>
                </div>
            </div>
        </nav>
    </header>

    <div id="toast-container" class="toast-container"></div>

    <main>
        <section id="inicio" class="section active">
            <div class="hero-wrap">
                <div class="hero-bg-shapes">
                    <div class="hero-shape hero-shape-1"></div>
                    <div class="hero-shape hero-shape-2"></div>
                    <div class="hero-shape hero-shape-3"></div>
                </div>
                <div class="hero-content">
                    <div class="hero-badge">Comunidad de intercambio</div>
                    <h1 class="hero-title">Mercado<span class="hero-accent">Hub</span></h1>
                    <p class="hero-subtitle">El lugar donde los gamers intercambian sus items de forma segura y sencilla. Publica, busca y truequea con toda la comunidad.</p>
                    <div class="hero-actions">
                        <button class="hero-btn hero-btn-primary" data-section="items-disponibles">Ver items disponibles</button>
                        <button class="hero-btn hero-btn-secondary" data-section="intercambio">Publicar item</button>
                    </div>
                </div>
            </div>
            <div class="how-it-works">
                <h2 class="hiw-title">Como funciona</h2>
                <div class="hiw-steps">
                    <div class="hiw-step">
                        <div class="hiw-step-num">1</div>
                        <div class="hiw-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        </div>
                        <h3>Publica tu item</h3>
                        <p>Subi fotos, conta tu item y ponele un precio estimado. Cuanto mas detalle, mejor.</p>
                    </div>
                    <div class="hiw-step-arrow">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                    <div class="hiw-step">
                        <div class="hiw-step-num">2</div>
                        <div class="hiw-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <h3>Encontra lo que buscas</h3>
                        <p>Explora items de otros usuarios, filtra por categoria y encontre el trueque perfecto.</p>
                    </div>
                    <div class="hiw-step-arrow">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                    <div class="hiw-step">
                        <div class="hiw-step-num">3</div>
                        <div class="hiw-step-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                        </div>
                        <h3>Intercambia</h3>
                        <p>Envía propuestas de trueque, negocia y concretá el intercambio con toda confianza.</p>
                    </div>
                </div>
            </div>
            <div class="hero-features">
                <div class="hero-feature-card">
                    <div class="hfc-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                    </div>
                    <h3>Intercambio seguro</h3>
                    <p>Facilitamos el intercambio de bienes entre personas de manera sencilla y segura.</p>
                </div>
                <div class="hero-feature-card">
                    <div class="hfc-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Apoyo comunitario</h3>
                    <p>Fomentamos el apoyo mutuo dentro de la comunidad para ayudar a quienes mas lo necesitan.</p>
                </div>
                <div class="hero-feature-card">
                    <div class="hfc-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <h3>Solidaridad</h3>
                    <p>Creemos en el poder de la solidaridad y la cooperacion. Juntos podemos hacer la diferencia.</p>
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
            <div class="add-item-card">
                <div class="add-item-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    <span>Agregar Item</span>
                </div>
                <form id="intercambio-form" enctype="multipart/form-data">
                    <div class="add-item-body">
                        <div class="add-item-main">
                            <div class="add-field">
                                <label for="item-nombre">Nombre del Item</label>
                                <input type="text" id="item-nombre" placeholder="Ej: Playstation 4, Bicicleta, Libro..." required>
                            </div>

                            <div class="add-field">
                                <label for="item-descripcion">Descripcion</label>
                                <textarea id="item-descripcion" rows="3" placeholder="Describe el estado, detalles, etc."></textarea>
                            </div>

                            <div class="add-field-row">
                                <div class="add-field" style="flex:1">
                                    <label for="item-precio">Precio</label>
                                    <input type="number" id="item-precio" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="add-field" style="flex:1">
                                    <label for="item-categoria">Categoria</label>
                                    <select id="item-categoria" required>
                                        <option value="" disabled selected>Cargando...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="add-item-image">
                            <div class="dropzone" id="dropzone">
                                <input type="file" id="item-imagen" name="imagen" accept="image/*" required>
                                <div class="dropzone-content">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <p>Arrastra o <span>selecciona</span></p>
                                    <span class="dropzone-hint">JPG, PNG, WEBP - Max 5MB</span>
                                </div>
                                <img id="image-preview" alt="Vista previa">
                            </div>
                            <div class="add-extra-images" id="add-extra-images">
                                <label class="extra-images-label">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Agregar mas fotos
                                    <input type="file" id="item-imagenes-extra" name="imagenes_extra[]" accept="image/*" multiple>
                                </label>
                                <div class="extra-previews" id="extra-previews"></div>
                            </div>
                        </div>
                    </div>

                    <div class="add-item-footer">
                        <button type="submit" id="btn-submit-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Publicar Item
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ============ ITEMS ============ -->
        <section id="items-disponibles" class="section">
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
                            <div id="item-modal-image">
                                <div class="item-gallery-main" id="item-gallery-main">
                                    <img id="item-gallery-main-img" src="" alt="">
                                </div>
                                <div class="item-gallery-thumbs" id="item-gallery-thumbs"></div>
                            </div>
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
                        <label>Imagenes</label>
                        <div class="edit-images-grid" id="edit-images-grid"></div>
                    </div>

                    <div class="edit-field">
                        <label for="edit-item-imagen">Nueva imagen (opcional)</label>
                        <div class="edit-file-wrap">
                            <input type="file" id="edit-item-imagen" accept="image/*">
                            <span class="edit-file-label">Seleccionar archivo</span>
                        </div>
                    </div>

                    <div class="edit-add-images">
                        <label class="extra-images-label">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Agregar mas fotos
                            <input type="file" id="edit-item-imagenes-extra" accept="image/*" multiple>
                        </label>
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

        <!-- ============ INTERCAMBIOS ============ -->
        <section id="intercambios" class="section">
            <div class="trades-header">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                <h1>Mis Intercambios</h1>
            </div>
            <div class="trade-filters">
                <button class="trade-filter active" data-filter="all">Todos</button>
                <button class="trade-filter" data-filter="pendiente">Pendientes</button>
                <button class="trade-filter" data-filter="aceptado">Aceptados</button>
                <button class="trade-filter" data-filter="rechazado">Rechazados</button>
            </div>
            <div id="trades-loader" class="loader"><div class="spinner"></div></div>
            <div id="trades-empty" class="trades-empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                <p>No tienes intercambios aun.</p>
            </div>
            <div id="trades-list"></div>
        </section>

        <!-- ============ TRADE MODAL ============ -->
        <div id="trade-modal" class="modal-overlay" style="display:none;">
            <div class="modal-content trade-modal-content">
                <button id="trade-modal-close" class="modal-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <div class="trade-modal-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#77ff00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Nuevo Intercambio
                </div>
                <div class="trade-modal-body">
                    <div class="trade-section">
                        <h3 class="trade-section-title">Items que solicito</h3>
                        <div id="trade-items-solicitados" class="trade-items-mini"></div>
                    </div>
                    <div class="trade-section">
                        <h3 class="trade-section-title">Tus items para ofrecer</h3>
                        <div id="trade-my-items" class="trade-items-grid"></div>
                        <p id="trade-my-empty" class="trade-empty-msg" style="display:none">No tienes items para ofrecer. <a href="#" data-section="intercambio">Agrega uno</a>.</p>
                    </div>
                    <div class="trade-section">
                        <label for="trade-mensaje">Mensaje (opcional)</label>
                        <textarea id="trade-mensaje" rows="2" placeholder="Escribe un mensaje para el usuario..."></textarea>
                    </div>
                    <p id="trade-error" class="trade-error-msg" style="display:none"></p>
                </div>
                <div class="trade-modal-footer">
                    <button id="btn-send-trade" class="btn-trade-send">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Enviar propuesta
                    </button>
                    <button id="trade-modal-cancel" class="btn-trade-cancel">Cancelar</button>
                </div>
            </div>
        </div>
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
