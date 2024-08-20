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

    // Cargar la primera sección por defecto
    showSection('items-disponibles');

    // Manejo de la carga de items
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

    // Búsqueda de items
    document.getElementById('navbar-search').addEventListener('input', function(event) {
        const query = event.target.value.toLowerCase();
        const itemElements = document.querySelectorAll('#items-disponibles-lista .item');

        itemElements.forEach(item => {
            const itemId = item.querySelector('img').alt.toLowerCase();
            const itemNombre = item.querySelector('span').textContent.toLowerCase();

            if (itemId.includes(query) || itemNombre.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });

   // Carga de items disponibles
function loadItems() {
    fetch('/proyecto/api/getItems.php')
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
                itemDisponiblesImg.alt = item.nombre;

                const itemDisponiblesName = document.createElement('span');
                itemDisponiblesName.textContent = item.nombre;

                const itemUser = document.createElement('p');
                itemUser.textContent = 'Publicado por: ' + item.usuario;

                const itemCategoria = document.createElement('p');
                itemCategoria.textContent = 'Categoría: ' + item.categoria;

                itemDisponiblesElement.appendChild(itemDisponiblesImg);
                itemDisponiblesElement.appendChild(itemDisponiblesName);
                itemDisponiblesElement.appendChild(itemUser);
                itemDisponiblesElement.appendChild(itemCategoria);
                itemsLista.appendChild(itemDisponiblesElement);
            });
        }).catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al cargar los items.');
        });
}


    // Cargar perfil
    function loadProfile() {
        fetch('/proyecto/api/getProfile.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                document.getElementById('profile-image').src = data.imagen;
                document.getElementById('profile-name').textContent = data.nombre;
                document.getElementById('profile-bio').textContent = data.bio;
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al cargar el perfil.');
            });
    }

    // Cargar categorías
    function loadCategorias() {
        fetch('/proyecto/api/getCategorias.php')
            .then(response => response.json())
            .then(data => {
                const categoriasSelect = document.getElementById('item-categoria');
                categoriasSelect.innerHTML = '';

                data.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id;
                    option.textContent = categoria.nombre;
                    categoriasSelect.appendChild(option);
                });
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al cargar las categorías.');
            });
    }

    // Cargar datos al inicio
    loadCategorias();
    loadItems();
});
