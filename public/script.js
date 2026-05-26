document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('.nav-link');
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    const itemList = document.getElementById('items-disponibles-lista');
    const categoriaFilter = document.getElementById('categoria-filter');
    const categoriasSelectForm = document.getElementById('item-categoria');
    const intercambioForm = document.getElementById('intercambio-form');
    const searchInput = document.getElementById('search-input');
    const itemsLoader = document.getElementById('items-loader');
    const itemsEmpty = document.getElementById('items-empty');
    const toastContainer = document.getElementById('toast-container');
    const imageInput = document.getElementById('item-imagen');
    const imagePreview = document.getElementById('image-preview');
    const profileItemsList = document.getElementById('profile-items-list');
    const itemModal = document.getElementById('item-modal');
    const itemModalClose = document.getElementById('item-modal-close');
    const scrollSentinel = document.getElementById('scroll-sentinel');
    const editItemModal = document.getElementById('edit-item-modal');
    const editItemForm = document.getElementById('edit-item-form');
    const editItemClose = document.getElementById('edit-item-close');
    const editItemCancel = document.getElementById('edit-item-cancel');
    const publicProfileModal = document.getElementById('public-profile-modal');
    const publicProfileBody = document.getElementById('public-profile-body');
    const publicProfileClose = document.getElementById('public-profile-close');

    const BASE_URL = window.BASE_URL !== undefined ? window.BASE_URL : '/proyecto';
    const CSRF_TOKEN = window.CSRF_TOKEN || '';
    const IS_ADMIN = window.IS_ADMIN === true;
    const CURRENT_USER_ID = parseInt(window.CURRENT_USER_ID) || 0;
    let allItems = [];
    let currentPage = 1;
    let totalItems = 0;
    const PAGE_LIMIT = 12;
    let isLoadingMore = false;
    let hasMoreItems = true;
    let currentSearch = '';
    let currentCategoria = 'all';
    let currentPostId = null;
    let currentModalItemId = null;

    // === Toast ===
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function setLoading(show) {
        itemsLoader.classList.toggle('active', show);
    }

    function setForoLoading(show) {
        const loader = document.getElementById('foro-loader');
        if (loader) loader.classList.toggle('active', show);
    }

    async function apiFetch(url, options = {}) {
        const method = options.method || 'GET';
        const headers = { 'Content-Type': 'application/json', ...options.headers };

        if (method !== 'GET' && CSRF_TOKEN) {
            // Inject CSRF token into body
            if (options.body) {
                const body = JSON.parse(options.body);
                body.csrf_token = CSRF_TOKEN;
                options.body = JSON.stringify(body);
            } else {
                options.body = JSON.stringify({ csrf_token: CSRF_TOKEN });
            }
        }

        const res = await fetch(url, { method, headers, ...options });
        return res.json();
    }

    const fetchJSON = (url) => fetch(url).then(res => res.json());

    const showSection = (sectionId) => {
        sections.forEach(s => s.style.display = s.id === sectionId ? 'block' : 'none');
        navLinks.forEach(l => l.classList.toggle('active', l.getAttribute('data-section') === sectionId));
    };

    const updateOptions = (data, selectElement, defaultOption) => {
        selectElement.innerHTML = defaultOption;
        data.forEach(({ id, nombre }) => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = nombre;
            selectElement.appendChild(option);
        });
    };

    // === Items ===
    const userColors = {};
    const getUserColor = (name) => {
        if (userColors[name]) return userColors[name];
        const colors = ['#77ff00', '#ff6b7a', '#ffa502', '#3498db', '#a855f7', '#ec4899', '#14b8a6', '#f97316'];
        let hash = 0;
        for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
        userColors[name] = colors[Math.abs(hash) % colors.length];
        return userColors[name];
    };
    const getInitials = (name) => name.charAt(0).toUpperCase();

    const createItemElement = ({ img, nombre, descripcion, precio, usuario, categoria, id, esPropio, id_usuario }) => {
        const div = document.createElement('div');
        div.classList.add('item');
        div.dataset.id = id;

        const imgHtml = img
            ? `<div class="item-img-wrap"><img src="${img}" alt="${nombre}" loading="lazy"></div>`
            : `<div class="item-img-placeholder">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
               </div>`;
        const precioHtml = precio
            ? `<span class="item-precio-badge">$${parseFloat(precio).toFixed(2)}</span>`
            : '';
        const userColor = getUserColor(usuario);
        let actionsHtml = '';
        if (esPropio) {
            actionsHtml = `<div class="item-actions">
                <button class="btn-edit" data-id="${id}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="btn-delete" data-id="${id}">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>`;
        }

        div.innerHTML = `
            ${imgHtml}
            <div class="item-info">
                <div class="item-info-top">
                    <span class="item-name">${nombre}</span>
                    ${precioHtml}
                </div>
                <span class="item-meta">
                    <span class="item-cat-badge">${categoria}</span>
                </span>
                <div class="item-user-row">
                    <span class="item-user-avatar" style="background:${userColor}">${getInitials(usuario)}</span>
                    <a href="#" class="item-user-link" data-userid="${id_usuario}">${usuario}</a>
                </div>
            </div>
            ${actionsHtml}
        `;

        div.addEventListener('click', (e) => {
            if (e.target.closest('.btn-delete') || e.target.closest('.btn-edit') || e.target.closest('.item-user-link')) return;
            openItemModal(id);
        });

        const deleteBtn = div.querySelector('.btn-delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteItem(id, div);
            });
        }

        const editBtn = div.querySelector('.btn-edit');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openEditItemModal(id);
            });
        }

        const userLink = div.querySelector('.item-user-link');
        if (userLink) {
            userLink.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openPublicProfile(userLink.dataset.userid);
            });
        }

        return div;
    };

    const displayItems = (items, append = false) => {
        if (!append) itemList.innerHTML = '';
        if (items.length === 0 && !append) {
            itemsEmpty.classList.add('active');
            scrollSentinel.style.display = 'none';
        } else {
            itemsEmpty.classList.remove('active');
            items.forEach(item => itemList.appendChild(item));
            scrollSentinel.style.display = hasMoreItems ? 'block' : 'none';
        }
    };

    const loadItems = async (reset = true) => {
        if (reset) {
            currentPage = 1;
            hasMoreItems = true;
            allItems = [];
            displayItems([]);
        }
        if (!hasMoreItems) return;
        setLoading(reset);
        try {
            const params = new URLSearchParams({ categoria: currentCategoria, page: currentPage, limit: PAGE_LIMIT });
            if (currentSearch) params.set('search', currentSearch);
            const data = await fetchJSON(`${BASE_URL}/api/getitems.php?${params}`);
            if (data.error) throw new Error(data.error);
            totalItems = data.total;
            hasMoreItems = currentPage < Math.ceil(totalItems / PAGE_LIMIT);
            const elements = data.items.map(item =>
                createItemElement({ ...item, esPropio: item.esPropio, id_usuario: item.id_usuario })
            );
            if (reset) {
                allItems = data.items;
                displayItems(elements);
            } else {
                allItems = allItems.concat(data.items);
                displayItems(elements, true);
            }
        } catch (err) {
            showToast('Error al cargar items: ' + err.message, 'error');
        } finally {
            setLoading(false);
            isLoadingMore = false;
        }
    };

    // === Infinite Scroll ===
    const scrollObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMoreItems && !isLoadingMore) {
            isLoadingMore = true;
            currentPage++;
            loadItems(false);
        }
    }, { rootMargin: '200px' });
    scrollObserver.observe(scrollSentinel);

    // === Item Detail Modal ===
    async function openItemModal(id) {
        currentModalItemId = id;
        const imgContainer = document.getElementById('item-modal-image');
        const infoContainer = document.getElementById('item-modal-info');
        const commentsList = document.getElementById('item-comments-list');
        const relatedGrid = document.getElementById('related-items-grid');
        const divider = document.getElementById('item-modal-divider');

        imgContainer.innerHTML = '<div class="spinner"></div>';
        infoContainer.innerHTML = '';
        commentsList.innerHTML = '';
        relatedGrid.innerHTML = '';
        if (divider) divider.style.display = 'none';
        itemModal.style.display = 'flex';
        requestAnimationFrame(() => itemModal.classList.add('open'));

        try {
            const itemData = allItems.find(i => i.id == id);
            if (!itemData) throw new Error('Item no encontrado');

            const imgHtml = itemData.img
                ? `<img src="${itemData.img}" alt="${itemData.nombre}" class="detail-img" loading="lazy">`
                : `<div class="detail-img-placeholder">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                   </div>`;
            const precioHtml = itemData.precio
                ? `<div class="detail-precio-tag">$${parseFloat(itemData.precio).toFixed(2)}</div>`
                : '';
            const descHtml = itemData.descripcion
                ? `<p class="detail-desc">${itemData.descripcion}</p>`
                : '';
            const userLink = itemData.esPropio
                ? `<span class="detail-user-self">${itemData.usuario}</span>`
                : `<a href="#" class="detail-user-link" data-userid="${itemData.id_usuario}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    ${itemData.usuario}
                   </a>`;

            imgContainer.innerHTML = imgHtml;
            infoContainer.innerHTML = `
                <div class="detail-header">
                    <h2 class="detail-title">${itemData.nombre}</h2>
                    ${precioHtml}
                </div>
                <div class="detail-meta-row">
                    <span class="detail-cat">${itemData.categoria}</span>
                    <span class="detail-user">${userLink}</span>
                </div>
                ${descHtml}
            `;

            const userLinkEl = infoContainer.querySelector('.detail-user-link');
            if (userLinkEl) {
                userLinkEl.addEventListener('click', (e) => {
                    e.preventDefault();
                    itemModal.style.display = 'none';
                    itemModal.classList.remove('open');
                    openPublicProfile(userLinkEl.dataset.userid);
                });
            }

            if (divider) divider.style.display = 'block';

            // Load comments
            loadItemComments(id);

            // Load related items
            loadRelatedItems(id);
        } catch (err) {
            infoContainer.innerHTML = `<p class="error-msg">${err.message}</p>`;
        }
    }

    // Modal close with animation
    function closeItemModal() {
        itemModal.classList.remove('open');
        setTimeout(() => { itemModal.style.display = 'none'; }, 200);
    }

    async function loadItemComments(itemId) {
        const commentsList = document.getElementById('item-comments-list');
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getItemComments.php?id=${itemId}`);
            if (data.error) throw new Error(data.error);
            commentsList.innerHTML = '';
            if (data.length === 0) {
                commentsList.innerHTML = '<p class="no-comments">Sin comentarios aun. Se el primero en comentar.</p>';
            } else {
                data.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'item-comment';
                    const fecha = new Date(c.created_at).toLocaleDateString('es-AR', {
                        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    div.innerHTML = `
                        <div class="item-comment-header">
                            <strong>${c.autor}</strong>
                            <span class="comment-date">${fecha}</span>
                        </div>
                        <p>${c.contenido}</p>
                    `;
                    commentsList.appendChild(div);
                });
            }
        } catch (err) {
            commentsList.innerHTML = '<p class="error-msg">Error al cargar comentarios.</p>';
        }
    }

    async function loadRelatedItems(itemId) {
        const relatedGrid = document.getElementById('related-items-grid');
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getRelatedItems.php?id=${itemId}&limit=8`);
            if (data.error) throw new Error(data.error);
            relatedGrid.innerHTML = '';
            if (data.length === 0) {
                relatedGrid.innerHTML = '<p class="no-comments">No hay items relacionados.</p>';
                return;
            }
            data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'related-item';
                const imgHtml = item.img ? `<img src="${item.img}" alt="${item.nombre}" loading="lazy">` : '';
                const precioHtml = item.precio ? `<span class="item-precio">$${parseFloat(item.precio).toFixed(2)}</span>` : '';
                div.innerHTML = `
                    ${imgHtml}
                    <div class="related-item-info">
                        <span class="related-item-name">${item.nombre}</span>
                        ${precioHtml}
                    </div>
                `;
                div.addEventListener('click', () => {
                    openItemModal(item.id);
                });
                relatedGrid.appendChild(div);
            });
        } catch (err) {
            relatedGrid.innerHTML = '<p class="error-msg">Error al cargar recomendaciones.</p>';
        }
    }

    // Comment form
    document.getElementById('item-comment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentModalItemId) return;
        const input = document.getElementById('item-comment-input');
        const contenido = input.value.trim();
        if (!contenido) return;

        try {
            const data = await apiFetch(`${BASE_URL}/api/createItemComment.php`, {
                method: 'POST',
                body: JSON.stringify({ id_items: currentModalItemId, contenido }),
            });
            if (data.error) throw new Error(data.error);
            input.value = '';
            showToast('Comentario agregado', 'success');
            loadItemComments(currentModalItemId);
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        }
    });

    itemModalClose.addEventListener('click', () => { closeItemModal(); currentModalItemId = null; });
    itemModal.addEventListener('click', (e) => {
        if (e.target === itemModal) { closeItemModal(); currentModalItemId = null; }
    });

    // === Edit Item Modal ===
    async function openEditItemModal(id) {
        const itemData = allItems.find(i => i.id == id);
        if (!itemData) { showToast('Item no encontrado', 'error'); return; }

        document.getElementById('edit-item-id').value = id;
        document.getElementById('edit-item-nombre').value = itemData.nombre || '';
        document.getElementById('edit-item-descripcion').value = itemData.descripcion || '';
        document.getElementById('edit-item-precio').value = itemData.precio || '';

        const editCatSelect = document.getElementById('edit-item-categoria');
        const catOpts = categoriasSelectForm.querySelectorAll('option');
        editCatSelect.innerHTML = '';
        catOpts.forEach(opt => {
            if (opt.value) {
                const clone = opt.cloneNode(true);
                if (opt.textContent === itemData.categoria) clone.selected = true;
                editCatSelect.appendChild(clone);
            }
        });
        editItemModal.style.display = 'flex';
    }

    editItemForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-item-id').value;
        const nombre = document.getElementById('edit-item-nombre').value.trim();
        const descripcion = document.getElementById('edit-item-descripcion').value.trim();
        const precio = document.getElementById('edit-item-precio').value.trim();
        const categoria = document.getElementById('edit-item-categoria').value;
        const imageFile = document.getElementById('edit-item-imagen').files[0];

        if (!nombre || !categoria) { showToast('Nombre y categoria son obligatorios.', 'error'); return; }

        if (imageFile) {
            const reader = new FileReader();
            reader.onload = async (ev) => {
                await submitEdit(id, nombre, descripcion, precio, categoria, ev.target.result);
            };
            reader.readAsDataURL(imageFile);
        } else {
            await submitEdit(id, nombre, descripcion, precio, categoria, '');
        }
    });

    async function submitEdit(id, nombre, descripcion, precio, categoria, imagenUrl) {
        try {
            const catId = document.getElementById('edit-item-categoria').value;
            const data = await apiFetch(`${BASE_URL}/api/updateItem.php`, {
                method: 'POST',
                body: JSON.stringify({
                    id_items: parseInt(id), nombre_items: nombre, descripcion_items: descripcion,
                    precio: precio || null, id_juegos: parseInt(catId), imagen_url: imagenUrl,
                }),
            });
            if (data.error) throw new Error(data.error);
            showToast('Item actualizado', 'success');
            editItemModal.style.display = 'none';
            document.getElementById('edit-item-imagen').value = '';
            loadItems(true);
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    }

    editItemClose.addEventListener('click', () => editItemModal.style.display = 'none');
    editItemCancel.addEventListener('click', () => editItemModal.style.display = 'none');
    editItemModal.addEventListener('click', (e) => { if (e.target === editItemModal) editItemModal.style.display = 'none'; });

    // === Public Profile Modal ===
    async function openPublicProfile(userId) {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getPublicProfile.php?id=${userId}`);
            if (data.error) throw new Error(data.error);
            const user = data.user;
            const imgHtml = user.img_perfil
                ? `<img src="data:image/png;base64,${user.img_perfil}" alt="${user.nombre_usuario}" class="public-profile-img">` : '';

            let itemsHtml = '';
            if (data.items.length > 0) {
                itemsHtml = '<div class="public-profile-items">';
                data.items.forEach(item => {
                    const priceHtml = item.items_precio
                        ? `<span class="item-precio">$${parseFloat(item.items_precio).toFixed(2)}</span>` : '';
                    const itemImg = item.img ? `<img src="${item.img}" alt="${item.nombre_items}" loading="lazy">` : '';
                    itemsHtml += `<div class="public-profile-item">${itemImg}<span class="profile-item-name">${item.nombre_items}</span>${priceHtml}</div>`;
                });
                itemsHtml += '</div>';
            } else {
                itemsHtml = '<p class="no-comments">Este usuario no ha publicado items aun.</p>';
            }

            const fecha = new Date(user.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric' });
            publicProfileBody.innerHTML = `
                ${imgHtml}<h2>${user.nombre_usuario}</h2>
                <p class="public-profile-bio">${user.bio || 'Sin biografia aun.'}</p>
                <p class="public-profile-meta">Miembro desde ${fecha} &middot; ${data.items.length} items</p>
                <h3>Items de ${user.nombre_usuario}</h3>${itemsHtml}`;
            publicProfileModal.style.display = 'flex';
        } catch (err) { showToast('Error al cargar perfil: ' + err.message, 'error'); }
    }

    publicProfileClose.addEventListener('click', () => publicProfileModal.style.display = 'none');
    publicProfileModal.addEventListener('click', (e) => { if (e.target === publicProfileModal) publicProfileModal.style.display = 'none'; });

    // === Delete ===
    async function deleteItem(id, element) {
        if (!confirm('Eliminar este item?')) return;
        try {
            const data = await apiFetch(`${BASE_URL}/api/deleteItem.php`, {
                method: 'POST', body: JSON.stringify({ id_items: id }),
            });
            if (data.error) throw new Error(data.error);
            element.remove();
            showToast('Item eliminado correctamente', 'success');
            allItems = allItems.filter(i => i.id !== id);
            totalItems--;
        } catch (err) { showToast('Error al eliminar: ' + err.message, 'error'); }
    }

    // === Search ===
    let searchTimeout = null;
    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        searchTimeout = setTimeout(() => { currentSearch = query; loadItems(true); }, 300);
    });

    // === Profile ===
    const loadProfile = async () => {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getProfile.php`);
            if (data.error) throw new Error(data.error);
            document.getElementById('profile-image').src = data.img_perfil
                ? `data:image/png;base64,${data.img_perfil}`
                : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="%23ccc"%3E%3Ccircle cx="12" cy="8" r="4"/%3E%3Cpath d="M12 14c-5 0-8 2-8 5v1h16v-1c0-3-3-5-8-5z"/%3E%3C/svg%3E';
            document.getElementById('profile-name').textContent = data.nombre_usuario || 'Usuario';
            document.getElementById('profile-bio').textContent = data.bio || 'Sin biografia aun.';
            document.getElementById('profile-bio-input').value = data.bio || '';
        } catch (err) { showToast('Error al cargar perfil: ' + err.message, 'error'); }
    };

    document.getElementById('btn-editar-perfil').addEventListener('click', () => {
        document.getElementById('profile-edit-form').style.display = 'block';
    });
    document.getElementById('btn-cancelar-editar-perfil').addEventListener('click', () => {
        document.getElementById('profile-edit-form').style.display = 'none';
    });


    const loadProfileItems = async () => {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getProfileItems.php`);
            if (data.error) throw new Error(data.error);
            profileItemsList.innerHTML = '';
            if (data.length === 0) {
                profileItemsList.innerHTML = '<p style="text-align:center;color:#888">No has publicado items aun.</p>';
                return;
            }
            data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'profile-item';
                const imgHtml = item.img ? `<img src="data:image/png;base64,${item.img}" alt="${item.nombre_items}" loading="lazy">` : '';
                div.innerHTML = `${imgHtml}<span class="profile-item-name">${item.nombre_items}</span>`;
                profileItemsList.appendChild(div);
            });
        } catch (err) { showToast('Error al cargar tus items: ' + err.message, 'error'); }
    };

    // === Categories ===
    const loadCategorias = async () => {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getCategorias.php`);
            if (data.error) throw new Error(data.error);
            updateOptions(data, categoriasSelectForm, '<option value="" disabled selected>Selecciona una categoria</option>');
            categoriaFilter.innerHTML = '<option value="all">Todas las categorias</option>';
            data.forEach(({ id, nombre }) => {
                const opt1 = document.createElement('option');
                opt1.value = id; opt1.textContent = nombre;
                categoriaFilter.appendChild(opt1);
                const opt2 = document.createElement('option');
                opt2.value = id; opt2.textContent = nombre;
                categoriasSelectForm.appendChild(opt2);
            });
        } catch (err) { showToast('Error al cargar categorias: ' + err.message, 'error'); }
    };

    // === Add Item ===
    const submitBtn = document.getElementById('btn-submit-item');
    intercambioForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const nombre = document.getElementById('item-nombre').value.trim();
        const descripcion = document.getElementById('item-descripcion').value.trim();
        const precio = document.getElementById('item-precio').value.trim();
        const imagen = document.getElementById('item-imagen').files[0];
        const categoria = document.getElementById('item-categoria').value;
        if (!nombre || !imagen || !categoria) { showToast('Completa todos los campos obligatorios.', 'error'); return; }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Agregando...';
        const reader = new FileReader();
        reader.onload = async (ev) => {
            try {
                const data = await apiFetch(`${BASE_URL}/api/addItem.php`, {
                    method: 'POST',
                    body: JSON.stringify({ nombre, descripcion, precio: precio || null, url: ev.target.result, categoria }),
                });
                if (data.error) throw new Error(data.error);
                showToast('Item agregado exitosamente', 'success');
                intercambioForm.reset();
                imagePreview.classList.remove('visible');
                imagePreview.src = '';
                loadItems(true);
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
            finally { submitBtn.disabled = false; submitBtn.textContent = 'Agregar Item'; }
        };
        reader.readAsDataURL(imagen);
    });

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => { imagePreview.src = e.target.result; imagePreview.classList.add('visible'); };
            reader.readAsDataURL(file);
        }
    });

    categoriaFilter.addEventListener('change', (e) => {
        currentCategoria = e.target.value;
        searchInput.value = '';
        currentSearch = '';
        loadItems(true);
    });

    // === FORO ===
    function showForoListView() {
        document.getElementById('foro-list-view').style.display = 'block';
        document.getElementById('foro-create-view').style.display = 'none';
        document.getElementById('foro-detail-view').style.display = 'none';
    }
    function showForoCreateView() {
        document.getElementById('foro-list-view').style.display = 'none';
        document.getElementById('foro-create-view').style.display = 'block';
        document.getElementById('foro-detail-view').style.display = 'none';
    }
    function showForoDetailView() {
        document.getElementById('foro-list-view').style.display = 'none';
        document.getElementById('foro-create-view').style.display = 'none';
        document.getElementById('foro-detail-view').style.display = 'block';
    }

    async function loadForoPosts() {
        setForoLoading(true);
        try {
            const data = await fetchJSON(`${BASE_URL}/api/foro/getPosts.php`);
            if (data.error) throw new Error(data.error);
            const list = document.getElementById('foro-posts-list');
            const empty = document.getElementById('foro-empty');
            list.innerHTML = '';
            if (data.length === 0) { empty.classList.add('active'); return; }
            empty.classList.remove('active');
            data.forEach(post => {
                const div = document.createElement('div');
                div.className = 'foro-post-item';
                const fecha = new Date(post.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                const avatarColor = getUserColor(post.autor);
                const initial = getInitials(post.autor);
                div.innerHTML = `
                    <div class="foro-post-top">
                        <h3 class="foro-post-titulo">${post.titulo}</h3>
                    </div>
                    <div class="foro-post-bottom">
                        <span class="foro-post-avatar" style="background:${avatarColor}">${initial}</span>
                        <span class="foro-post-author">${post.autor}</span>
                        <span class="foro-post-sep">&middot;</span>
                        <span class="foro-post-date">${fecha}</span>
                        <span class="foro-post-comments">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            ${post.comentarios}
                        </span>
                    </div>`;
                div.addEventListener('click', () => loadForoDetail(post.id_post));
                list.appendChild(div);
            });
        } catch (err) { showToast('Error al cargar posts: ' + err.message, 'error'); }
        finally { setForoLoading(false); }
    }

    async function loadForoDetail(postId) {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/foro/getPost.php?id=${postId}`);
            if (data.error) throw new Error(data.error);
            currentPostId = postId;
            const content = document.getElementById('foro-detail-content');
            const fecha = new Date(data.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            const avatarColor = getUserColor(data.autor);
            const initial = getInitials(data.autor);

            let commentsHtml = '';
            if (data.comentarios && data.comentarios.length > 0) {
                data.comentarios.forEach(c => {
                    const cf = new Date(c.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                    const cColor = getUserColor(c.autor);
                    const cInit = getInitials(c.autor);
                    commentsHtml += `
                        <div class="foro-comment">
                            <div class="foro-comment-header">
                                <span class="foro-comment-avatar" style="background:${cColor}">${cInit}</span>
                                <strong>${c.autor}</strong>
                                <span class="foro-comment-date">${cf}</span>
                            </div>
                            <p>${c.contenido}</p>
                        </div>`;
                });
            } else {
                commentsHtml = '<p class="foro-no-comments">Sin comentarios aun. Se el primero en responder.</p>';
            }
            const delBtn = data.esPropio ? `<button class="btn-delete-post" data-id="${data.id_post}">Eliminar</button>` : '';
            content.innerHTML = `
                <div class="foro-detail-card">
                    <div class="foro-detail-header">
                        <h2>${data.titulo}</h2>
                        ${delBtn}
                    </div>
                    <div class="foro-detail-author-row">
                        <span class="foro-post-avatar" style="background:${avatarColor}">${initial}</span>
                        <span><strong>${data.autor}</strong></span>
                        <span class="foro-post-date">${fecha}</span>
                    </div>
                    <div class="foro-detail-body">${data.contenido}</div>
                </div>
                <div class="foro-comments-section">
                    <h3 class="foro-comments-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Comentarios (${data.comentarios ? data.comentarios.length : 0})
                    </h3>
                    <div class="foro-comments-list">${commentsHtml}</div>
                    <form id="foro-comment-form">
                        <textarea id="foro-comment-input" rows="2" placeholder="Escribe un comentario..." required></textarea>
                        <button type="submit" class="btn-foro-submit">Enviar</button>
                    </form>
                </div>`;

            content.querySelector('.btn-delete-post')?.addEventListener('click', async () => {
                if (!confirm('Eliminar este post?')) return;
                try {
                    const res = await apiFetch(`${BASE_URL}/api/foro/deletePost.php`, { method: 'POST', body: JSON.stringify({ id_post: data.id_post }) });
                    if (res.error) throw new Error(res.error);
                    showToast('Post eliminado', 'success');
                    showForoListView(); loadForoPosts();
                } catch (err) { showToast('Error: ' + err.message, 'error'); }
            });

            document.getElementById('foro-comment-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const input = document.getElementById('foro-comment-input');
                const contenido = input.value.trim();
                if (!contenido) return;
                try {
                    const res = await apiFetch(`${BASE_URL}/api/foro/createComment.php`, { method: 'POST', body: JSON.stringify({ id_post: data.id_post, contenido }) });
                    if (res.error) throw new Error(res.error);
                    input.value = ''; showToast('Comentario agregado', 'success');
                    loadForoDetail(data.id_post);
                } catch (err) { showToast('Error: ' + err.message, 'error'); }
            });
            showForoDetailView();
        } catch (err) { showToast('Error al cargar post: ' + err.message, 'error'); }
    }

    document.getElementById('btn-nuevo-post').addEventListener('click', showForoCreateView);
    document.getElementById('btn-cancelar-post').addEventListener('click', showForoListView);
    document.getElementById('btn-volver-foro').addEventListener('click', () => { showForoListView(); loadForoPosts(); });

    document.getElementById('foro-create-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const titulo = document.getElementById('foro-titulo').value.trim();
        const contenido = document.getElementById('foro-contenido').value.trim();
        if (!titulo || !contenido) { showToast('Completa todos los campos.', 'error'); return; }
        try {
            const data = await apiFetch(`${BASE_URL}/api/foro/createPost.php`, { method: 'POST', body: JSON.stringify({ titulo, contenido }) });
            if (data.error) throw new Error(data.error);
            showToast('Post creado exitosamente', 'success');
            document.getElementById('foro-titulo').value = '';
            document.getElementById('foro-contenido').value = '';
            showForoListView(); loadForoPosts();
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });

    // === ADMIN ===
    async function loadAdminUsers() {
        const list = document.getElementById('admin-users-list');
        const loader = document.getElementById('admin-users-loader');
        if (!list) return;
        loader.classList.add('active');
        try {
            const data = await fetchJSON(`${BASE_URL}/api/admin/getUsers.php`);
            if (data.error) throw new Error(data.error);
            list.innerHTML = '';
            data.forEach(u => {
                const div = document.createElement('div');
                div.className = 'admin-user-row';
                const fecha = new Date(u.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'short', day: 'numeric' });
                const isSelf = u.id_usuario == CURRENT_USER_ID;
                const roleSelect = isSelf
                    ? `<span class="admin-role-badge">${u.rol}</span>`
                    : `<select class="admin-role-select" data-userid="${u.id_usuario}">
                        <option value="usuario" ${u.rol === 'usuario' ? 'selected' : ''}>Usuario</option>
                        <option value="admin" ${u.rol === 'admin' ? 'selected' : ''}>Admin</option>
                       </select>`;
                const delBtn = isSelf ? '' : `<button class="btn-delete-admin" data-userid="${u.id_usuario}">Eliminar</button>`;
                div.innerHTML = `
                    <span class="admin-user-name">${u.nombre_usuario}</span>
                    <span class="admin-user-email">${u.email}</span>
                    <span class="admin-user-date">${fecha}</span>
                    ${roleSelect}
                    ${delBtn}
                `;
                list.appendChild(div);
            });

            list.querySelectorAll('.admin-role-select').forEach(sel => {
                sel.addEventListener('change', async () => {
                    try {
                        const res = await apiFetch(`${BASE_URL}/api/admin/updateUserRole.php`, {
                            method: 'POST',
                            body: JSON.stringify({ id_usuario: parseInt(sel.dataset.userid), rol: sel.value }),
                        });
                        if (res.error) throw new Error(res.error);
                        showToast('Rol actualizado', 'success');
                    } catch (err) { showToast('Error: ' + err.message, 'error'); }
                });
            });

            list.querySelectorAll('.btn-delete-admin').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Eliminar este usuario?')) return;
                    try {
                        const res = await apiFetch(`${BASE_URL}/api/admin/deleteUser.php`, {
                            method: 'POST',
                            body: JSON.stringify({ id_usuario: parseInt(btn.dataset.userid) }),
                        });
                        if (res.error) throw new Error(res.error);
                        showToast('Usuario eliminado', 'success');
                        loadAdminUsers();
                    } catch (err) { showToast('Error: ' + err.message, 'error'); }
                });
            });
        } catch (err) { showToast('Error al cargar usuarios: ' + err.message, 'error'); }
        finally { loader.classList.remove('active'); }
    }

    async function loadAdminCategorias() {
        const list = document.getElementById('admin-categorias-list');
        if (!list) return;
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getCategorias.php`);
            if (data.error) throw new Error(data.error);
            list.innerHTML = '';
            data.forEach(c => {
                const div = document.createElement('div');
                div.className = 'admin-cat-row';
                div.innerHTML = `
                    <span>${c.nombre}</span>
                    <button class="btn-delete-admin" data-catid="${c.id}">Eliminar</button>
                `;
                list.appendChild(div);
            });
            list.querySelectorAll('.btn-delete-admin').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Eliminar esta categoria?')) return;
                    try {
                        const res = await apiFetch(`${BASE_URL}/api/admin/deleteCategoria.php`, {
                            method: 'POST',
                            body: JSON.stringify({ id_juegos: parseInt(btn.dataset.catid) }),
                        });
                        if (res.error) throw new Error(res.error);
                        showToast('Categoria eliminada', 'success');
                        loadAdminCategorias();
                        loadCategorias();
                    } catch (err) { showToast('Error: ' + err.message, 'error'); }
                });
            });
        } catch (err) { showToast('Error al cargar categorias: ' + err.message, 'error'); }
    }

    document.getElementById('admin-categoria-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('admin-categoria-nombre');
        const nombre = input.value.trim();
        if (!nombre) return;
        try {
            const res = await apiFetch(`${BASE_URL}/api/admin/addCategoria.php`, {
                method: 'POST',
                body: JSON.stringify({ nombre }),
            });
            if (res.error) throw new Error(res.error);
            showToast('Categoria agregada', 'success');
            input.value = '';
            loadAdminCategorias();
            loadCategorias();
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });

    // Mobile nav toggle
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('open');
            navMenu.classList.toggle('open');
        });
    }

    // === Navigation ===
    navLinks.forEach(link => link.addEventListener('click', (event) => {
        event.preventDefault();
        const sectionId = event.target.getAttribute('data-section');
        showSection(sectionId);
        if (sectionId === 'perfil') { loadProfile(); loadProfileItems(); }
        if (sectionId === 'foro') { showForoListView(); loadForoPosts(); }
        if (sectionId === 'admin') { loadAdminUsers(); loadAdminCategorias(); }
        // Close mobile menu
        if (navToggle) navToggle.classList.remove('open');
        if (navMenu) navMenu.classList.remove('open');
    }));

    // === Fix profile edit CSRF ===
    const origProfileSubmit = document.getElementById('profile-edit')?.submit;
    document.getElementById('profile-edit')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const bio = document.getElementById('profile-bio-input').value.trim();
        const imageFile = document.getElementById('profile-image-input').files[0];
        const formData = new FormData();
        formData.append('bio', bio);
        formData.append('csrf_token', CSRF_TOKEN);
        if (imageFile) formData.append('imagen', imageFile);
        try {
            const res = await fetch(`${BASE_URL}/api/updateUserProfile.php`, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            showToast('Perfil actualizado', 'success');
            document.getElementById('profile-edit-form').style.display = 'none';
            loadProfile();
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    });

    // === Init ===
    showSection('items-disponibles');
    loadCategorias();
    loadItems(true);
});
