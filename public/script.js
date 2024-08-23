document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.section');
    const navLinks = document.querySelectorAll('nav a');

    function showSection(sectionId) {
        sections.forEach(section => {
            section.style.display = section.id === sectionId ? 'block' : 'none';
        });

        navLinks.forEach(link => {
            link.classList.toggle('active', link.getAttribute('data-section') === sectionId);
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);

            if (sectionId === 'perfil') {
                loadProfile();
            }
        });
    });

    // Mostrar la primera sección por defecto
    showSection('items-disponibles');

    // Manejo del formulario de agregar items
    document.getElementById('intercambio-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const itemNombre = document.getElementById('item-nombre').value;
        const itemImagenInput = document.getElementById('item-imagen');
        const itemImagen = itemImagenInput.files[0];
        const itemCategoria = document.getElementById('item-categoria').value;

        if (!itemNombre.trim() || !itemImagen || !itemCategoria) {
            alert('Por favor, introduce un nombre, una imagen y una categoría válidos.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const itemUrl = e.target.result;

            fetch('/proyecto/api/addItem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nombre: itemNombre,
                    url: itemUrl,
                    categoria: itemCategoria
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.error) {
                      alert(data.error);
                  } else {
                      alert(data.message);
                      loadItems();
                  }
              }).catch(error => {
                  console.error('Error:', error);
                  alert('Hubo un problema con la solicitud.');
              });
        };
        reader.readAsDataURL(itemImagen);

        document.getElementById('item-nombre').value = '';
        document.getElementById('item-imagen').value = '';
    });

    // Cargar items disponibles con categoría filtrada
    function loadItems(categoriaId = 'all') {
        fetch('/proyecto/api/getItems.php?categoria=' + categoriaId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const itemsLista = document.getElementById('items-disponibles-lista');
                itemsLista.innerHTML = '';

                data.forEach(item => {
                    const itemDisponiblesElement = document.createElement('div');
                    itemDisponiblesElement.classList.add('item');

                    const itemDisponiblesImg = document.createElement('img');
                    itemDisponiblesImg.src = item.img;
                    itemDisponiblesImg.alt = item.id;

                    const itemDisponiblesSpan = document.createElement('span');
                    itemDisponiblesSpan.textContent = item.nombre;

                    const itemUserSpan = document.createElement('span');
                    itemUserSpan.textContent = `Usuario: ${item.usuario}`;

                    const itemCategorySpan = document.createElement('span');
                    itemCategorySpan.textContent = `Categoría: ${item.categoria}`;

                    itemDisponiblesElement.appendChild(itemDisponiblesImg);
                    itemDisponiblesElement.appendChild(itemDisponiblesSpan);
                    itemDisponiblesElement.appendChild(itemUserSpan);
                    itemDisponiblesElement.appendChild(itemCategorySpan);
                    itemsLista.appendChild(itemDisponiblesElement);
                });
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al cargar los items.');
            });
    }

    // Función unificada para cargar categorías en ambos select
    function loadCategorias() {
        fetch('/proyecto/api/getCategorias.php')
            .then(response => response.json())
            .then(data => {
                const categoriasSelectForm = document.getElementById('item-categoria');
                const categoriasSelectFilter = document.getElementById('categoria-filter');

                categoriasSelectForm.innerHTML = '<option value="" disabled selected>Selecciona una categoría</option>';
                categoriasSelectFilter.innerHTML = '<option value="all">Todas las categorias</option>';

                data.forEach(categoria => {
                    const optionForm = document.createElement('option');
                    optionForm.value = categoria.id;
                    optionForm.textContent = categoria.nombre;
                    categoriasSelectForm.appendChild(optionForm);

                    const optionFilter = document.createElement('option');
                    optionFilter.value = categoria.id;
                    optionFilter.textContent = categoria.nombre;
                    categoriasSelectFilter.appendChild(optionFilter);
                });
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al cargar las categorías.');
            });
    }

    // Filtrar items por categoría
    document.getElementById('categoria-filter').addEventListener('change', function() {
        const selectedCategoria = this.value;
        loadItems(selectedCategoria);
    });

    // Cargar categorías y items al inicio
    loadCategorias();  // Cargamos categorías en ambos apartados
    loadItems();

    // Cargar perfil de usuario
    function loadProfile() {
        fetch('/proyecto/api/getProfile.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('profile-image').src = data.profile_image;
                document.getElementById('profile-name').textContent = data.profile_name;
                document.getElementById('profile-bio').textContent = data.profile_bio;
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al cargar el perfil.');
            });
    }
});