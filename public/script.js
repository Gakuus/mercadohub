document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('nav a');
    const itemList = document.getElementById('items-disponibles-lista');
    const categoriaFilter = document.getElementById('categoria-filter');
    const categoriasSelectForm = document.getElementById('item-categoria');
    const navbarSearchForm = document.getElementById('navbar-search-form');
    const intercambioForm = document.getElementById('intercambio-form');
    let allItems = [];

    const fetchJSON = (url) => fetch(url).then(res => res.json());

    const showSection = (sectionId) => {
        sections.forEach(section => section.style.display = section.id === sectionId ? 'block' : 'none');
        navLinks.forEach(link => link.classList.toggle('active', link.getAttribute('data-section') === sectionId));
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

    const createItemElement = ({ img, nombre, usuario, categoria }) => {
        const itemElement = document.createElement('div');
        itemElement.classList.add('item');

        itemElement.innerHTML = `
            <img src="${img}" alt="${nombre}">
            <span>${nombre}</span>
            <span>Usuario: ${usuario}</span>
            <span>Categoría: ${categoria}</span>
        `;

        return itemElement;
    };

    const displayItems = (items) => {
        itemList.innerHTML = '';
        items.forEach(item => itemList.appendChild(createItemElement(item)));
    };

    const loadItems = (categoriaId = 'all') => {
        fetchJSON(`/proyecto/api/getItems.php?categoria=${categoriaId}`)
            .then(data => {
                if (data.error) throw new Error(data.error);
                allItems = data; // Almacena todos los items para la búsqueda
                displayItems(allItems);
            })
            .catch(error => alert(`Hubo un problema al cargar los items: ${error.message}`));
    };

    const loadProfile = () => {
        fetchJSON('/proyecto/api/getProfile.php')
            .then(data => {
                document.getElementById('profile-image').src = data.profile_image;
                document.getElementById('profile-name').textContent = data.profile_name;
                document.getElementById('profile-bio').textContent = data.profile_bio;
            })
            .catch(error => alert(`Hubo un problema al cargar el perfil: ${error.message}`));
    };

    const loadCategorias = () => {
        fetchJSON('/proyecto/api/getCategorias.php')
            .then(data => {
                updateOptions(data, categoriasSelectForm, '<option value="" disabled selected>Selecciona una categoría</option>');
                updateOptions(data, categoriaFilter, '<option value="all">Todas las categorías</option>');
            })
            .catch(error => alert(`Hubo un problema al cargar las categorías: ${error.message}`));
    };

    const filterItems = (query) => {
        const lowerCaseQuery = query.toLowerCase();
        const filteredItems = allItems.filter(({ nombre, usuario }) =>
            nombre.toLowerCase().includes(lowerCaseQuery) ||
            usuario.toLowerCase().includes(lowerCaseQuery)
        );
        displayItems(filteredItems);
    };

    navLinks.forEach(link => link.addEventListener('click', (event) => {
        event.preventDefault();
        const sectionId = event.target.getAttribute('data-section');
        showSection(sectionId);
        if (sectionId === 'perfil') loadProfile();
    }));

    categoriaFilter.addEventListener('change', (event) => loadItems(event.target.value));

    intercambioForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const itemNombre = document.getElementById('item-nombre').value.trim();
        const itemImagen = document.getElementById('item-imagen').files[0];
        const itemCategoria = document.getElementById('item-categoria').value;

        if (!itemNombre || !itemImagen || !itemCategoria) {
            alert('Por favor, introduce un nombre, una imagen y una categoría válidos.');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            fetch('/proyecto/api/addItem.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre: itemNombre,
                    url: e.target.result,
                    categoria: itemCategoria
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                alert(data.message);
                loadItems();
            })
            .catch(error => alert(`Hubo un problema con la solicitud: ${error.message}`));
        };
        reader.readAsDataURL(itemImagen);

        intercambioForm.reset();
    });

    navbarSearchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const query = document.getElementById('navbar-search').value.trim();
        if (query) {
            filterItems(query);
        } else {
            alert('Por favor, introduce un término de búsqueda.');
        }
    });

    

    // Initialize
    showSection('items-disponibles');
    loadCategorias();
    loadItems();
});
