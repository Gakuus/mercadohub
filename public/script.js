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
    let tradeTargetUserId = null;
    let tradeTargetItemId = null;
    let tradeSelectedItems = [];
    let notifInterval = null;

    // === Toast ===
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    function setLoading(show) {
        const skeleton = document.getElementById('items-skeleton');
        if (show && skeleton) {
            skeleton.innerHTML = '';
            for (let i = 0; i < 8; i++) {
                const div = document.createElement('div');
                div.className = 'skeleton-card';
                div.innerHTML = `
                    <div class="skeleton-img skeleton-pulse"></div>
                    <div class="skeleton-body">
                        <div class="skeleton-line skeleton-pulse" style="width:80%"></div>
                        <div class="skeleton-line skeleton-pulse" style="width:40%"></div>
                        <div class="skeleton-line skeleton-pulse" style="width:55%"></div>
                    </div>`;
                skeleton.appendChild(div);
            }
            skeleton.classList.add('active');
            if (itemList) itemList.style.display = 'none';
        } else if (skeleton) {
            skeleton.classList.remove('active');
            if (itemList) itemList.style.display = '';
        }
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
            items.forEach((item, i) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                itemList.appendChild(item);
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        item.style.transitionDelay = `${i * 0.05}s`;
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    });
                });
            });
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
        const galleryMain = document.getElementById('item-gallery-main');
        const mainImg = document.getElementById('item-gallery-main-img');
        const thumbsContainer = document.getElementById('item-gallery-thumbs');

        infoContainer.innerHTML = '';
        commentsList.innerHTML = '';
        relatedGrid.innerHTML = '';
        if (divider) divider.style.display = 'none';
        itemModal.style.display = 'flex';
        requestAnimationFrame(() => itemModal.classList.add('open'));

        try {
            const itemData = allItems.find(i => i.id == id);
            if (!itemData) throw new Error('Item no encontrado');

            // Load gallery images
            const galleryData = await fetchJSON(`${BASE_URL}/api/getItemImages.php?id=${id}`);
            let images = [];
            if (!galleryData.error && galleryData.length > 0) {
                images = galleryData;
            } else if (itemData.img) {
                images = [{ id: 0, url: itemData.img, orden: 0 }];
            }

            // Gallery
            if (images.length > 0) {
                mainImg.src = images[0].url;
                galleryMain.style.display = '';
                thumbsContainer.innerHTML = '';
                images.forEach((img, idx) => {
                    const thumb = document.createElement('img');
                    thumb.className = 'item-gallery-thumb' + (idx === 0 ? ' active' : '');
                    thumb.src = img.url;
                    thumb.addEventListener('click', () => {
                        mainImg.src = img.url;
                        thumbsContainer.querySelectorAll('.item-gallery-thumb').forEach(t => t.classList.remove('active'));
                        thumb.classList.add('active');
                    });
                    thumbsContainer.appendChild(thumb);
                });
            } else {
                mainImg.src = '';
                galleryMain.style.display = 'none';
                thumbsContainer.innerHTML = '';
            }

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
                ${!itemData.esPropio ? `<button class="btn-trade" data-id="${itemData.id}" data-userid="${itemData.id_usuario}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Intercambiar
                </button>` : ''}
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

            const tradeBtn = infoContainer.querySelector('.btn-trade');
            if (tradeBtn) {
                tradeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeItemModal();
                    currentModalItemId = null;
                    openTradeModal(tradeBtn.dataset.userid, tradeBtn.dataset.id);
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

        // Load images
        loadEditImages(id);

        editItemModal.style.display = 'flex';
        requestAnimationFrame(() => editItemModal.classList.add('open'));
    }

    async function loadEditImages(itemId) {
        const grid = document.getElementById('edit-images-grid');
        grid.innerHTML = '<div class="spinner" style="margin:8px auto"></div>';
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getItemImages.php?id=${itemId}`);
            grid.innerHTML = '';
            if (data.error || data.length === 0) {
                grid.innerHTML = '<p style="color:rgba(255,255,255,0.4);font-size:0.85em">Sin imagenes.</p>';
                return;
            }
            data.forEach(img => {
                const div = document.createElement('div');
                div.className = 'edit-image-item';
                const isCover = img.orden === 0;
                div.innerHTML = `
                    <img src="${img.url}" alt="" loading="lazy">
                    <div class="edit-img-actions">
                        ${isCover
                            ? '<span style="color:#77ff00;font-size:0.7em;font-weight:600">PORTADA</span>'
                            : `<button class="btn-set-cover" data-imgid="${img.id}" title="Portada">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                               </button>
                               <button class="btn-del-img" data-imgid="${img.id}" title="Eliminar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                               </button>`
                        }
                    </div>
                `;
                grid.appendChild(div);

                const setCoverBtn = div.querySelector('.btn-set-cover');
                if (setCoverBtn) {
                    setCoverBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        try {
                            const res = await apiFetch(`${BASE_URL}/api/setItemCoverImage.php`, {
                                method: 'POST', body: JSON.stringify({ id: parseInt(setCoverBtn.dataset.imgid) }),
                            });
                            if (res.error) throw new Error(res.error);
                            showToast('Portada actualizada', 'success');
                            loadEditImages(itemId);
                        } catch (err) { showToast('Error: ' + err.message, 'error'); }
                    });
                }

                const delBtn = div.querySelector('.btn-del-img');
                if (delBtn) {
                    delBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        if (!confirm('Eliminar esta imagen?')) return;
                        try {
                            const res = await apiFetch(`${BASE_URL}/api/deleteItemImage.php`, {
                                method: 'POST', body: JSON.stringify({ id: parseInt(delBtn.dataset.imgid) }),
                            });
                            if (res.error) throw new Error(res.error);
                            showToast('Imagen eliminada', 'success');
                            loadEditImages(itemId);
                        } catch (err) { showToast('Error: ' + err.message, 'error'); }
                    });
                }
            });
        } catch (err) {
            grid.innerHTML = '<p class="error-msg">Error al cargar imagenes.</p>';
        }
    }

    // Upload extra images in edit modal
    const editExtraInput = document.getElementById('edit-item-imagenes-extra');
    if (editExtraInput) {
        editExtraInput.addEventListener('change', async () => {
            const itemId = document.getElementById('edit-item-id').value;
            if (!itemId || editExtraInput.files.length === 0) return;
            const formData = new FormData();
            formData.append('id_items', itemId);
            formData.append('csrf_token', CSRF_TOKEN);
            for (let i = 0; i < editExtraInput.files.length; i++) {
                formData.append('imagenes[]', editExtraInput.files[i]);
            }
            try {
                const res = await fetch(`${BASE_URL}/api/addItemImages.php`, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.error) throw new Error(data.error);
                showToast(data.message, 'success');
                editExtraInput.value = '';
                loadEditImages(itemId);
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
        });
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

    function closeEditModal() {
        editItemModal.classList.remove('open');
        setTimeout(() => { editItemModal.style.display = 'none'; }, 200);
    }
    editItemClose.addEventListener('click', closeEditModal);
    editItemCancel.addEventListener('click', closeEditModal);
    editItemModal.addEventListener('click', (e) => { if (e.target === editItemModal) closeEditModal(); });

    // === Public Profile Modal ===
    async function openPublicProfile(userId) {
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getPublicProfile.php?id=${userId}`);
            if (data.error) throw new Error(data.error);
            const user = data.user;
            const avatarColor = getUserColor(user.nombre_usuario);
            const initial = getInitials(user.nombre_usuario);
            const imgHtml = user.img_perfil
                ? `<img src="data:image/png;base64,${user.img_perfil}" alt="${user.nombre_usuario}" class="pp-avatar">`
                : `<div class="pp-avatar pp-avatar-placeholder" style="background:${avatarColor}">${initial}</div>`;

            let itemsHtml = '';
            if (data.items.length > 0) {
                itemsHtml = '<div class="pp-items">';
                data.items.forEach(item => {
                    const priceHtml = item.items_precio
                        ? `<span class="item-precio">$${parseFloat(item.items_precio).toFixed(2)}</span>` : '';
                    const itemImg = item.img ? `<img src="${item.img}" alt="${item.nombre_items}" loading="lazy">` : '';
                    itemsHtml += `<div class="pp-item">${itemImg}<span class="pp-item-name">${item.nombre_items}</span>${priceHtml}</div>`;
                });
                itemsHtml += '</div>';
            } else {
                itemsHtml = '<p class="pp-empty">Este usuario no ha publicado items aun.</p>';
            }

            const fecha = new Date(user.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'long', day: 'numeric' });
            publicProfileBody.innerHTML = `
                <div class="pp-header">
                    ${imgHtml}
                    <h2>${user.nombre_usuario}</h2>
                    <p class="pp-bio">${user.bio || 'Sin biografia aun.'}</p>
                    <p class="pp-meta">Miembro desde ${fecha} &middot; ${data.items.length} items</p>
                </div>
                <div class="pp-section">
                    <h3 class="pp-section-title">Items de ${user.nombre_usuario}</h3>
                    ${itemsHtml}
                </div>`;
            publicProfileModal.style.display = 'flex';
            requestAnimationFrame(() => publicProfileModal.classList.add('open'));
        } catch (err) { showToast('Error al cargar perfil: ' + err.message, 'error'); }
    }

    publicProfileClose.addEventListener('click', () => {
        publicProfileModal.classList.remove('open');
        setTimeout(() => { publicProfileModal.style.display = 'none'; }, 200);
    });
    publicProfileModal.addEventListener('click', (e) => {
        if (e.target === publicProfileModal) {
            publicProfileModal.classList.remove('open');
            setTimeout(() => { publicProfileModal.style.display = 'none'; }, 200);
        }
    });

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

        try {
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('descripcion', descripcion);
            if (precio) formData.append('precio', precio);
            formData.append('categoria', categoria);
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('imagen', imagen);

            const extraFiles = document.getElementById('item-imagenes-extra').files;
            for (let i = 0; i < extraFiles.length; i++) {
                formData.append('imagenes_extra[]', extraFiles[i]);
            }

            const res = await fetch(`${BASE_URL}/api/addItem.php`, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            showToast('Item agregado exitosamente', 'success');
            intercambioForm.reset();
            imagePreview.classList.remove('visible');
            imagePreview.src = '';
            if (dropzone) dropzone.classList.remove('has-image');
            document.getElementById('extra-previews').innerHTML = '';
            loadItems(true);
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
        finally { submitBtn.disabled = false; submitBtn.textContent = 'Publicar Item'; }
    });

    // Drag & drop
    const dropzone = document.getElementById('dropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => { e.preventDefault(); dropzone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => { e.preventDefault(); dropzone.classList.remove('drag-over'); });
        });
        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) {
                imageInput.files = files;
                const event = new Event('change', { bubbles: true });
                imageInput.dispatchEvent(event);
            }
        });
    }

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.classList.add('visible');
                dropzone.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.classList.remove('visible');
            dropzone.classList.remove('has-image');
        }
    });

    // Extra images preview
    const extraInput = document.getElementById('item-imagenes-extra');
    const extraPreviews = document.getElementById('extra-previews');
    if (extraInput) {
        extraInput.addEventListener('change', () => {
            extraPreviews.innerHTML = '';
            Array.from(extraInput.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.className = 'extra-preview-thumb';
                    img.src = e.target.result;
                    extraPreviews.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }

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

    // === TRADES ===
    let currentTradeFilter = 'all';

    async function loadTrades() {
        const list = document.getElementById('trades-list');
        const loader = document.getElementById('trades-loader');
        const empty = document.getElementById('trades-empty');
        if (!list) return;
        loader.classList.add('active');
        try {
            const data = await fetchJSON(`${BASE_URL}/api/trade/getUserTrades.php?filter=${currentTradeFilter}`);
            if (data.error) throw new Error(data.error);
            list.innerHTML = '';
            if (data.length === 0) { empty.classList.add('active'); return; }
            empty.classList.remove('active');

            data.forEach(t => {
                const div = document.createElement('div');
                div.className = 'trade-card';
                const isSender = t.soy_solicitante;
                const otherName = isSender ? t.receptor_nombre : t.solicitante_nombre;
                const otherId = isSender ? t.receptor_id : t.solicitante_id;
                const fecha = new Date(t.created_at).toLocaleDateString('es-AR', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                // Items HTML
                let ofreceHtml = '';
                t.items_ofrecidos.forEach(it => {
                    const img = it.img ? `<img src="${it.img}" alt="">` : '';
                    ofreceHtml += `<div class="trade-item-mini">${img}<span>${it.nombre}</span></div>`;
                });
                let recibeHtml = '';
                t.items_solicitados.forEach(it => {
                    const img = it.img ? `<img src="${it.img}" alt="">` : '';
                    recibeHtml += `<div class="trade-item-mini">${img}<span>${it.nombre}</span></div>`;
                });

                const isPending = t.estado === 'pendiente';
                const canRespond = !isSender && isPending;

                let actionsHtml = '';
                if (canRespond) {
                    actionsHtml = `
                        <div class="trade-card-actions">
                            <button class="btn-trade-accept" data-tradeid="${t.id_intercambio}">Aceptar</button>
                            <button class="btn-trade-reject" data-tradeid="${t.id_intercambio}">Rechazar</button>
                        </div>`;
                }

                const mensajeHtml = t.mensaje ? `<div class="trade-card-message">"${t.mensaje}"</div>` : '';

                div.innerHTML = `
                    <div class="trade-card-header">
                        <div class="trade-card-users">
                            ${isSender ? 'Tu' : `<strong>${otherName}</strong>`}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            ${isSender ? `<strong>${otherName}</strong>` : 'Tu'}
                            <span class="trade-card-date">${fecha}</span>
                        </div>
                        <span class="trade-status-badge trade-status-${t.estado}">${t.estado}</span>
                    </div>
                    <div class="trade-card-items">
                        <div class="trade-item-col">
                            <span class="trade-item-col-label">${isSender ? 'Ofreces' : 'Recibes'}</span>
                            ${ofreceHtml}
                        </div>
                        <div class="trade-card-arrow">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </div>
                        <div class="trade-item-col">
                            <span class="trade-item-col-label">${isSender ? 'Recibes' : 'Ofreces'}</span>
                            ${recibeHtml}
                        </div>
                    </div>
                    ${mensajeHtml}
                    ${actionsHtml}
                `;
                list.appendChild(div);

                const acceptBtn = div.querySelector('.btn-trade-accept');
                if (acceptBtn) {
                    acceptBtn.addEventListener('click', () => respondTrade(t.id_intercambio, 'aceptar'));
                }
                const rejectBtn = div.querySelector('.btn-trade-reject');
                if (rejectBtn) {
                    rejectBtn.addEventListener('click', () => respondTrade(t.id_intercambio, 'rechazar'));
                }
            });
        } catch (err) { showToast('Error al cargar intercambios: ' + err.message, 'error'); }
        finally { loader.classList.remove('active'); }
    }

    async function respondTrade(tradeId, accion) {
        if (!confirm(accion === 'aceptar' ? 'Aceptar este intercambio?' : 'Rechazar este intercambio?')) return;
        try {
            const data = await apiFetch(`${BASE_URL}/api/trade/respondTrade.php`, {
                method: 'POST', body: JSON.stringify({ id_intercambio: tradeId, accion }),
            });
            if (data.error) throw new Error(data.error);
            showToast(data.message, 'success');
            loadTrades();
        } catch (err) { showToast('Error: ' + err.message, 'error'); }
    }

    // Trade modal
    async function openTradeModal(userId, itemId) {
        tradeTargetUserId = userId;
        tradeTargetItemId = itemId;
        tradeSelectedItems = [];
        document.getElementById('trade-mensaje').value = '';
        document.getElementById('trade-error').style.display = 'none';

        const solicContainer = document.getElementById('trade-items-solicitados');
        const myContainer = document.getElementById('trade-my-items');
        const myEmpty = document.getElementById('trade-my-empty');

        // Show the target item(s) being requested
        const itemData = allItems.find(i => i.id == itemId);
        if (itemData) {
            solicContainer.innerHTML = `
                <div class="trade-item-mini">
                    ${itemData.img ? `<img src="${itemData.img}" alt="">` : ''}
                    <span>${itemData.nombre}</span>
                </div>`;
        }

        // Load user's items
        myContainer.innerHTML = '<div class="spinner" style="margin:20px auto"></div>';
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getMyItems.php`);
            if (data.error) throw new Error(data.error);
            myContainer.innerHTML = '';
            if (data.length === 0) {
                myEmpty.style.display = '';
                myContainer.innerHTML = '';
            } else {
                myEmpty.style.display = 'none';
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'trade-item-select';
                    if (item.id == itemId) div.style.display = 'none'; // can't offer the item being requested
                    const img = item.img ? `<img src="${item.img}" alt="">` : '';
                    div.innerHTML = `
                        ${img}
                        <div class="trade-select-check">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="#0f0f1a" stroke="none"><path d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"/></svg>
                        </div>
                        <div class="trade-item-name-overlay">${item.nombre}</div>
                    `;
                    div.dataset.id = item.id;
                    div.addEventListener('click', () => {
                        div.classList.toggle('selected');
                        const idx = tradeSelectedItems.indexOf(item.id);
                        if (idx > -1) tradeSelectedItems.splice(idx, 1);
                        else tradeSelectedItems.push(item.id);
                        document.getElementById('btn-send-trade').disabled = tradeSelectedItems.length === 0;
                    });
                    myContainer.appendChild(div);
                });
            }
        } catch (err) {
            myContainer.innerHTML = '<p class="error-msg">Error al cargar tus items.</p>';
        }

        document.getElementById('trade-modal').style.display = 'flex';
        requestAnimationFrame(() => document.getElementById('trade-modal').classList.add('open'));
    }

    function closeTradeModal() {
        const modal = document.getElementById('trade-modal');
        modal.classList.remove('open');
        setTimeout(() => { modal.style.display = 'none'; }, 200);
    }

    document.getElementById('btn-send-trade').addEventListener('click', async () => {
        const btn = document.getElementById('btn-send-trade');
        const errorEl = document.getElementById('trade-error');
        errorEl.style.display = 'none';
        if (tradeSelectedItems.length === 0) {
            errorEl.textContent = 'Selecciona al menos un item para ofrecer.';
            errorEl.style.display = 'block';
            return;
        }
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        try {
            const data = await apiFetch(`${BASE_URL}/api/trade/createTrade.php`, {
                method: 'POST',
                body: JSON.stringify({
                    id_receptor: tradeTargetUserId,
                    items_ofrecidos: tradeSelectedItems,
                    items_solicitados: [parseInt(tradeTargetItemId)],
                    mensaje: document.getElementById('trade-mensaje').value.trim(),
                }),
            });
            if (data.error) throw new Error(data.error);
            showToast('Propuesta enviada exitosamente', 'success');
            closeTradeModal();
            loadTrades();
        } catch (err) {
            errorEl.textContent = err.message;
            errorEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Enviar propuesta';
        }
    });

    document.getElementById('trade-modal-close').addEventListener('click', closeTradeModal);
    document.getElementById('trade-modal-cancel').addEventListener('click', closeTradeModal);
    document.getElementById('trade-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('trade-modal')) closeTradeModal();
    });

    // Trade filters
    document.querySelectorAll('.trade-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.trade-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTradeFilter = btn.dataset.filter;
            loadTrades();
        });
    });

    // === NOTIFICATIONS ===
    async function checkNotifications() {
        const bell = document.getElementById('notif-bell');
        const badge = document.getElementById('notif-badge');
        if (!bell || !badge) return;
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getNotifications.php`);
            if (data.error) return;
            if (data.total > 0) {
                badge.textContent = data.total > 9 ? '9+' : data.total;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        } catch (e) { /* silent */ }
    }

    function renderNotifDropdown(data) {
        const list = document.getElementById('notif-list');
        if (!list) return;
        list.innerHTML = '';
        if (data.pending_trades.length === 0) {
            list.innerHTML = '<div class="notif-empty">Sin notificaciones nuevas</div>';
            return;
        }
        data.pending_trades.forEach(t => {
            const div = document.createElement('div');
            div.className = 'notif-item';
            div.innerHTML = `
                <div class="notif-item-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                </div>
                <div class="notif-item-body">
                    <div class="notif-item-title">Nuevo intercambio</div>
                    <div class="notif-item-desc">${t.solicitante_nombre} quiere intercambiar ${t.items_ofrecidos} item(s) por ${t.items_solicitados} tuyo(s).</div>
                </div>
            `;
            div.addEventListener('click', () => {
                document.getElementById('notif-dropdown').style.display = 'none';
                showSection('intercambios');
                navLinks.forEach(l => l.classList.toggle('active', l.getAttribute('data-section') === 'intercambios'));
                loadTrades();
            });
            list.appendChild(div);
        });
        const link = document.createElement('a');
        link.href = '#';
        link.className = 'notif-link';
        link.textContent = 'Ver todos los intercambios';
        link.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('notif-dropdown').style.display = 'none';
            showSection('intercambios');
            navLinks.forEach(l => l.classList.toggle('active', l.getAttribute('data-section') === 'intercambios'));
            loadTrades();
        });
        list.appendChild(link);
    }

    // Bell click
    document.getElementById('notif-bell')?.addEventListener('click', async (e) => {
        e.stopPropagation();
        const dd = document.getElementById('notif-dropdown');
        if (dd.style.display === 'block') { dd.style.display = 'none'; return; }
        dd.style.display = 'block';
        try {
            const data = await fetchJSON(`${BASE_URL}/api/getNotifications.php`);
            if (!data.error) renderNotifDropdown(data);
        } catch (e) { /* silent */ }
    });

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        const dd = document.getElementById('notif-dropdown');
        if (dd && !e.target.closest('.nav-notif-wrap')) dd.style.display = 'none';
    });

    // Start polling
    notifInterval = setInterval(checkNotifications, 30000);

    // === THEME TOGGLE ===
    (function initTheme() {
        const saved = localStorage.getItem('theme');
        if (saved === 'light') document.documentElement.setAttribute('data-theme', 'light');
    })();

    document.getElementById('theme-toggle')?.addEventListener('click', () => {
        const html = document.documentElement;
        const isLight = html.getAttribute('data-theme') === 'light';
        if (isLight) {
            html.removeAttribute('data-theme');
            localStorage.setItem('theme', 'dark');
        } else {
            html.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
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
        if (sectionId === 'intercambios') { loadTrades(); }
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
