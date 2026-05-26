document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('register-form');
  const email = document.getElementById('register-email');
  const username = document.getElementById('register-nombre_usuario');
  const password = document.getElementById('register-contrasena');
  const btn = document.getElementById('register-btn');
  const alert = document.getElementById('auth-alert');
  const strengthBars = document.querySelectorAll('.strength-bar');
  const strengthText = document.getElementById('pw-strength-text');

  const BASE_URL = window.BASE_URL !== undefined ? window.BASE_URL : '/proyecto';

  function showAlert(msg, type = 'error') {
    alert.textContent = msg;
    alert.className = `auth-alert ${type} visible`;
  }

  function clearErrors() {
    document.querySelectorAll('.field-error').forEach(e => e.classList.remove('visible'));
    document.querySelectorAll('.input-wrapper input').forEach(i => i.classList.remove('input-error'));
  }

  function setFieldError(id, msg) {
    const el = document.getElementById(id);
    if (el) {
      el.textContent = msg;
      el.classList.add('visible');
      const input = el.closest('.form-group')?.querySelector('input');
      if (input) input.classList.add('input-error');
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
    });
  });

  // Password strength
  function evaluateStrength(pw) {
    let score = 0;
    if (pw.length >= 8) score++;
    if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
    if (/\d/.test(pw)) score++;
    return score;
  }

  function updateStrengthMeter(pw) {
    const score = evaluateStrength(pw);
    const labels = ['', 'Debil', 'Media', 'Fuerte'];
    const classes = ['', 'weak', 'medium', 'strong'];

    strengthBars.forEach((bar, i) => {
      bar.className = 'strength-bar';
      if (i < score) {
        bar.classList.add('active', classes[score]);
      }
    });

    strengthText.textContent = pw.length > 0 ? labels[score] : '';
  }

  password.addEventListener('input', () => {
    updateStrengthMeter(password.value);
  });

  // Live validation on blur
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

    // Email
    if (!email.value.trim() || !email.validity.valid) {
      setFieldError('reg-email-error', 'Ingresa un correo valido.');
      valid = false;
    }

    // Username
    const userName = username.value.trim();
    if (userName.length < 3 || !/^[a-zA-Z0-9_]+$/.test(userName)) {
      setFieldError('reg-username-error', 'Min. 3 caracteres, solo letras, numeros y guion bajo.');
      valid = false;
    }

    // Password
    const pw = password.value;
    if (pw.length < 8) {
      setFieldError('reg-password-error', 'La contrasena debe tener al menos 8 caracteres.');
      valid = false;
    } else if (!/[a-z]/.test(pw) || !/[A-Z]/.test(pw) || !/\d/.test(pw)) {
      setFieldError('reg-password-error', 'Debe incluir mayuscula, minuscula y numero.');
      valid = false;
    }

    if (!valid) return;

    setLoading(true);

    try {
      const res = await fetch(BASE_URL + '/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: email.value.trim(),
          nombre_usuario: userName,
          contrasena: pw,
        }),
      });

      const data = await res.json();

      if (data.success) {
        showAlert('Cuenta creada exitosamente. Redirigiendo...', 'success');
        setTimeout(() => {
          window.location.href = BASE_URL + '/Login/index.html';
        }, 1500);
      } else {
        showAlert(data.message || 'Error al crear la cuenta.');
      }
    } catch (err) {
      showAlert('Error de conexion. Intenta de nuevo.');
    } finally {
      setLoading(false);
    }
  });
});
