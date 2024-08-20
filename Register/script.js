document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const email = document.getElementById('register-email').value;
            const username = document.getElementById('register-nombre_usuario').value;
            const password = document.getElementById('register-contrasena').value;

            fetch('/proyecto/auth/register.php', {
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
                    alert(data.message);
                    window.location.href = '/proyecto/Login/index.html';
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
