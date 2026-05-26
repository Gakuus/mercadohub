document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('login-form');
  const email = document.getElementById('email');
  const username = document.getElementById('nombre_usuario');
  const password = document.getElementById('contrasena');
  const btn = document.getElementById('login-btn');
  const alert = document.getElementById('auth-alert');

  const BASE_URL = window.BASE_URL !== undefined ? window.BASE_URL : '/proyecto';

  function showAlert(msg, type = 'error') {
    alert.textContent = msg;
    alert.className = `auth-alert ${type} visible`;
  }

  function clearErrors() {
    document.querySelectorAll('.field-error').forEach(e => e.classList.remove('visible'));
    document.querySelectorAll('.input-wrapper input').forEach(i => i.classList.remove('input-error'));
  }

  function setError(id, msg) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = msg;
      el.classList.add('visible');
      el.previousElementSibling?.querySelector('input')?.classList.add('input-error');
    }
  }

  function setLoading(loading) {
    btn.classList.toggle('loading', loading);
    btn.disabled = loading;
  }

  // Password toggle
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.textContent = isPassword ? '\u{1F441}' : '\u{1F441}'; // eye emoji for both states
    });
  });

  email.addEventListener('blur', () => {
    email.classList.toggle('input-error', email.value && !email.validity.valid);
  });

  username.addEventListener('blur', () => {
    username.classList.toggle('input-error', username.value && username.value.length < 3);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    alert.className = 'auth-alert';

    let valid = true;

    if (!email.value.trim() || !email.validity.valid) {
      setError('email-error', 'Ingresa un correo valido.');
      valid = false;
    }
    if (!username.value.trim() || username.value.trim().length < 3) {
      setError('username-error', 'El usuario debe tener al menos 3 caracteres.');
      valid = false;
    }
    if (!password.value) {
      setError('password-error', 'La contrasena es obligatoria.');
      valid = false;
    }

    if (!valid) return;

    setLoading(true);

    try {
      const res = await fetch(BASE_URL + '/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email.value.trim(),
          nombre_usuario: username.value.trim(),
          contrasena: password.value,
        }),
      });

      const data = await res.json();

      if (data.success) {
        window.location.href = data.redirect || BASE_URL + '/public/index.php';
      } else {
        showAlert(data.message || 'Credenciales incorrectas.');
      }
    } catch (err) {
      showAlert('Error de conexion. Intenta de nuevo.');
    } finally {
      setLoading(false);
    }
  });
});
