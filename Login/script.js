document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value;
            const username = document.getElementById('nombre_usuario').value;
            const password = document.getElementById('contrasena').value;

            fetch('/proyecto/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    nombre_usuario: username,
                    contrasena: password
                })
            }).then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            }).then(data => {
                if (data.success) {
                    window.location.href = '/proyecto/public/index.php';
                } else {
                    alert(data.message);
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema con la solicitud.');
            });
        });
    }
});
