# Plan de trabajo - MercadoHub

## Sprints completados

### Sprint 1: Bugfixes y estabilidad ✅
- Fix `api/getProfile.php` - error SQL (coma extra) + migrar de mysqli a PDO
- Fix `api/updateItem.php` - agregar `session_start()` y validacion de dueno
- Fix `api/deleteItem.php` - agregar autenticacion y validacion de dueno
- Reemplazar 3 configs duplicadas por una centralizada con soporte `.env`
- Reemplazar todas las rutas hardcodeadas `/proyecto/` por constante `BASE_URL`
- Agregar validacion de tipo de archivo en subida de imagenes
- Agregar `.gitignore`, `.env.example`, `database/schema.sql`, `README.md`, `SPRINTS.md`

### Sprint 2: Mejoras de funcionalidad ✅
- Busqueda en tiempo real (keyup) en el dashboard
- Loader/indicador de carga en solicitudes AJAX
- Toast notifications en lugar de `alert()`
- Vista previa de imagen en formulario de agregar item
- Mostrar items del usuario en su perfil
- Eliminar items desde el frontend (solo dueno)

### Sprint 3: Login y Register rediseno ✅
- Diseno moderno con gradiente oscuro y glassmorphism
- CSS compartido entre Login y Register (`auth/assets/auth.css`)
- Validacion inline en frontend (blur + submit)
- Medidor de fuerza de contrasena en registro
- Toggle mostrar/ocultar contrasena
- Rate limiting en login (5 intentos / 15 min)
- Session regeneration post-login
- Password policy (8+ chars, mayuscula, minuscula, numero)
- Sanitizacion estricta de username

---

## Proximos sprints

### Sprint 4: Foro comunitario + formulario de item ✅

**Objetivo:** Implementar foro funcional y mejorar el formulario de alta de items.

**Foro:**
- [x] Crear tabla `foro_posts` y `foro_comentarios` en schema.sql
- [x] API: crear post (`api/foro/createPost.php`)
- [x] API: listar posts (`api/foro/getPosts.php`)
- [x] API: ver post con comentarios (`api/foro/getPost.php`)
- [x] API: crear comentario (`api/foro/createComment.php`)
- [x] API: eliminar post propio (`api/foro/deletePost.php`)
- [x] Frontend: seccion foro en dashboard con lista de posts
- [x] Frontend: pagina de detalle de post con comentarios
- [x] Frontend: formulario para crear nuevo post

**Formulario de item:**
- [x] Agregar campo `descripcion_items` al formulario de alta
- [x] Agregar campo `items_precio` (opcional) al formulario de alta
- [x] Enviar descripcion y precio en `addItem.php`
- [x] Mostrar descripcion y precio en tarjetas de item y modal de detalle

**Perfil:**
- [x] Edicion de bio desde el perfil (sin recargar)
- [x] Edicion de foto de perfil

**Bugs corregidos:**
- [x] `getProfile.php` - faltaba columna `bio` en el SELECT
- [x] `updateUserProfile.php` - escribia a tabla `perfil` en vez de `usuario.img_perfil`

### Sprint 5: UX avanzada ✅

**Objetivo:** Mejorar experiencia de usuario, detalle de item, imagenes en disco.

- [x] Modal/detalle de item al hacer click (descripcion, precio, usuario)
- [x] Paginacion + infinite scroll en el listado de items
- [x] Migrar imagenes de BLOB a almacenamiento en disco (`public/uploads/items/`) con fallback a BLOB
- [x] Compactar y optimizar imagenes al subir (GD: JPEG quality 80, max 1920px)
- [x] Preloader/boton spinner en "Agregar Item"
- [x] Boton "Editar item" para el dueno (modal con formulario precargado: nombre, descripcion, precio, categoria, imagen)
- [x] Vista de perfil de otros usuarios al clickear su nombre (modal con items del usuario)
- [x] Debounce en busqueda (300ms, server-side via endpoint `search` param)

### Sprint 6: Pinterest-style + comentarios + recomendaciones ✅

**Objetivo:** Experiencia tipo Pinterest en items: grid, detalle con comentarios, recomendaciones.

**Pinterest grid:**
- [x] Grid masonry 4 columnas con esquinas redondeadas (16px), sombras suaves, hover effect
- [x] Tarjetas con imagen cover fluida, info abajo, botones Editar/Eliminar para dueno
- [x] Responsive: 4 cols desktop, 3 tablet grande, 2 tablet chica, 1 mobile

**Detalle de item (modal Pinterest):**
- [x] Layout dos columnas: izquierda imagen grande, derecha info + comentarios
- [x] Abajo del detalle: seccion "Tambien te puede interesar" con items relacionados
- [x] Scroll infinito separado de la pagina principal
- [x] Cerrar modal con fondo oscuro overlay + boton X circular

**Comentarios en items:**
- [x] DB: tabla `item_comentarios` con FK a items y usuario
- [x] API: `getItemComments.php` (listar), `createItemComment.php` (crear)
- [x] Frontend: formulario inline en modal de detalle, lista de comentarios con scroll
- [x] Input estilo pill (border-radius 20px), boton comentar pill tambien

**Recomendaciones:**
- [x] API: `getRelatedItems.php?id=X` - items de la misma categoria, excluyendo el actual
- [x] Frontend: grid horizontal de items relacionados debajo del detalle
- [x] Click en relacionado abre su propio modal de detalle

### Sprint 7: Seguridad y administracion

**Objetivo:** Hardening, CSRF, panel admin.

- [ ] CSRF tokens en todos los formularios y endpoints POST
- [ ] Roles de usuario (admin, usuario) en DB
- [ ] Panel de administracion (gestionar usuarios, categorias, items reportados)
- [ ] Rate limiting en todos los endpoints de escritura
- [ ] Logging de actividades en DB
- [ ] Sanitizar output en PHP (escapar HTML en nombres)
- [ ] Limitar tamano de imagen subida (max 5MB)

### Sprint 7: Infraestructura

**Objetivo:** Profesionalizar el proyecto.

- [ ] Docker + docker-compose (PHP 8.4 + MariaDB + phpMyAdmin)
- [ ] CI/CD basico con GitHub Actions (lint + syntax check)
- [ ] Pruebas unitarias con PHPUnit (logica de auth, validaciones)
- [ ] Migracion a PSR-4 con Composer autoload
- [ ] URL hash-based routing para secciones (browser back/forward)

---

## Backlog (ideas a futuro)
- Sistema de mensajeria entre usuarios
- Sistema de reputacion/resenas
- Subida multiple de imagenes por item
- Geolocalizacion de items
- API REST documentada con Swagger/OpenAPI
- Aplicacion movil (React Native / Flutter)
- Integracion con pasarela de pago (donaciones)
- Internacionalizacion (i18n)
- Notificaciones por email
- Recuperacion de contrasena
- Modo oscuro
