document.addEventListener('DOMContentLoaded', () => {

    /* ------ Referencias al DOM ------ */
    const form         = document.getElementById('registro-form');
    const nombreInput  = document.getElementById('nombre');
    const emailInput   = document.getElementById('email');
    const pwdInput     = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const btnSubmit    = document.getElementById('btn-submit');

    /* ------ Utilidades ------ */

    function getGroup(input) {
        return input.closest('.form-group');
    }

    function setValid(input) {
        const group = getGroup(input);
        group.classList.remove('is-invalid');
        group.classList.add('is-valid');
        const errEl = group.querySelector('.form-error');
        if (errEl) errEl.textContent = '';
    }

    function setInvalid(input, message) {
        const group = getGroup(input);
        group.classList.remove('is-valid');
        group.classList.add('is-invalid');
        const errEl = group.querySelector('.form-error');
        if (errEl) errEl.textContent = message;
    }

    function clearState(input) {
        const group = getGroup(input);
        group.classList.remove('is-valid', 'is-invalid');
        const errEl = group.querySelector('.form-error');
        if (errEl) errEl.textContent = '';
    }

    /* ------ Validaciones individuales ------ */

    function validateNombre() {
        const val = nombreInput.value.trim();
        if (!val) {
            setInvalid(nombreInput, 'El nombre es obligatorio.');
            return false;
        }
        if (val.length < 3) {
            setInvalid(nombreInput, 'Debe tener al menos 3 caracteres.');
            return false;
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s'-]+$/.test(val)) {
            setInvalid(nombreInput, 'Solo letras y espacios permitidos.');
            return false;
        }
        setValid(nombreInput);
        return true;
    }

    function validateEmail() {
        const val = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!val) {
            setInvalid(emailInput, 'El correo es obligatorio.');
            return false;
        }
        if (!emailRegex.test(val)) {
            setInvalid(emailInput, 'Ingresa un correo válido.');
            return false;
        }
        setValid(emailInput);
        return true;
    }

    function getPasswordStrength(pwd) {
        let score = 0;
        if (pwd.length >= 8)  score++;
        if (pwd.length >= 12) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;
        return score; // 0-5
    }

    function updateStrengthBar(pwd) {
        const fill  = document.getElementById('strength-fill');
        const label = document.getElementById('strength-label');
        if (!pwd) {
            fill.style.width = '0%';
            fill.className = 'strength-fill';
            label.textContent = '';
            label.className = 'strength-label';
            return;
        }
        const score = getPasswordStrength(pwd);
        const levels = [
            { w: '20%', cls: 'weak',   text: 'Débil' },
            { w: '40%', cls: 'weak',   text: 'Débil' },
            { w: '55%', cls: 'fair',   text: 'Regular' },
            { w: '75%', cls: 'good',   text: 'Buena' },
            { w: '90%', cls: 'strong', text: 'Fuerte' },
            { w: '100%',cls: 'strong', text: '¡Excelente!' },
        ];
        const lvl = levels[Math.min(score, 5)];
        fill.style.width = lvl.w;
        fill.className   = `strength-fill ${lvl.cls}`;
        label.textContent = lvl.text;
        label.className  = `strength-label ${lvl.cls}`;
    }

    function validatePassword() {
        const val = pwdInput.value;
        if (!val) {
            setInvalid(pwdInput, 'La contraseña es obligatoria.');
            return false;
        }
        if (val.length < 8) {
            setInvalid(pwdInput, 'Mínimo 8 caracteres.');
            return false;
        }
        if (getPasswordStrength(val) < 2) {
            setInvalid(pwdInput, 'Contraseña muy débil. Agrega mayúsculas o números.');
            return false;
        }
        setValid(pwdInput);
        return true;
    }

    function validateConfirm() {
        const val = confirmInput.value;
        if (!val) {
            setInvalid(confirmInput, 'Confirma tu contraseña.');
            return false;
        }
        if (val !== pwdInput.value) {
            setInvalid(confirmInput, 'Las contraseñas no coinciden.');
            return false;
        }
        setValid(confirmInput);
        return true;
    }

    /* ------ Eventos de validación en tiempo real ------ */

    nombreInput.addEventListener('blur',  validateNombre);
    nombreInput.addEventListener('input', () => {
        if (getGroup(nombreInput).classList.contains('is-invalid')) validateNombre();
    });

    emailInput.addEventListener('blur',  validateEmail);
    emailInput.addEventListener('input', () => {
        if (getGroup(emailInput).classList.contains('is-invalid')) validateEmail();
    });

    pwdInput.addEventListener('input', () => {
        updateStrengthBar(pwdInput.value);
        if (getGroup(pwdInput).classList.contains('is-invalid')) validatePassword();
        if (confirmInput.value) validateConfirm();
    });
    pwdInput.addEventListener('blur', validatePassword);

    confirmInput.addEventListener('input', () => {
        if (confirmInput.value) validateConfirm();
    });
    confirmInput.addEventListener('blur', validateConfirm);

    /* ------ Toggle visibilidad contraseña ------ */

    function makeToggle(btnId, inputEl) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const isText = inputEl.type === 'text';
            inputEl.type = isText ? 'password' : 'text';
            const svg = btn.querySelector('svg');
            if (isText) {
                svg.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>`;
            } else {
                svg.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>`;
            }
        });
    }

    makeToggle('toggle-pwd',     pwdInput);
    makeToggle('toggle-confirm', confirmInput);

    /* ------ Envío del formulario ------ */

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const v1 = validateNombre();
        const v2 = validateEmail();
        const v3 = validatePassword();
        const v4 = validateConfirm();

        if (!(v1 && v2 && v3 && v4)) return;

        // Simular envío (quitar en producción)
        const btnText   = btnSubmit.querySelector('.btn-text');
        const btnLoader = btnSubmit.querySelector('.btn-loader');
        const btnArrow  = btnSubmit.querySelector('.btn-arrow');

        btnSubmit.disabled = true;
        btnText.textContent = 'Registrando...';
        btnLoader.classList.remove('hidden');
        btnArrow.classList.add('hidden');

        // En producción: form.submit() o fetch()
        setTimeout(() => {
            btnText.textContent = '¡Cuenta creada!';
            btnLoader.classList.add('hidden');
            btnSubmit.style.background = 'linear-gradient(135deg, #2e7d32, #43a047)';
        }, 2000);
    });

    /* ------ Partículas de fondo ------ */

    (function initParticles() {
        const canvas = document.getElementById('particles-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        let W, H, particles = [];

        function resize() {
            W = canvas.width  = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }

        function rand(min, max) { return Math.random() * (max - min) + min; }

        function createParticle() {
            return {
                x:     rand(0, W),
                y:     rand(0, H),
                r:     rand(0.5, 2.2),
                vx:    rand(-0.25, 0.25),
                vy:    rand(-0.4, -0.1),
                alpha: rand(0.15, 0.55),
            };
        }

        function initParticles(count = 55) {
            particles = Array.from({ length: count }, createParticle);
        }

        function draw() {
            ctx.clearRect(0, 0, W, H);
            particles.forEach(p => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(100, 181, 246, ${p.alpha})`;
                ctx.fill();

                p.x += p.vx;
                p.y += p.vy;

                if (p.y < -10) { p.y = H + 5; p.x = rand(0, W); }
                if (p.x < -10) p.x = W + 5;
                if (p.x > W + 10) p.x = -5;
            });
            requestAnimationFrame(draw);
        }

        resize();
        initParticles();
        draw();
        window.addEventListener('resize', () => { resize(); initParticles(); });
    })();

});