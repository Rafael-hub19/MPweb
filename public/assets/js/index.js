/* ============================================
   JavaScript para index.php - Panel de Entregas
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ------ Animación de aparición al cargar ------ */
    const wrap = document.querySelector('.wrap');
    if (wrap) {
        wrap.style.opacity = '0';
        wrap.style.transform = 'translateY(20px)';

        setTimeout(() => {
            wrap.style.transition = 'all 0.6s ease-out';
            wrap.style.opacity = '1';
            wrap.style.transform = 'translateY(0)';
        }, 100);
    }

    /* ------ Botón recargar ------ */
    const btnReload = document.getElementById('btn-reload');
    if (btnReload) {
        btnReload.addEventListener('click', () => location.reload());
    }

    /* ------ Hover suave en las cards del menú ------ */
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    /* ------ Animación escalonada de secciones al cargar ------ */
    const sections = document.querySelectorAll('.main-menu .card, .auth-section');
    sections.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 300 + i * 100);
    });

    /* ------ Resaltar botón de login con pulso sutil al cargar ------ */
    const btnLogin = document.querySelector('.btn-login');
    if (btnLogin) {
        setTimeout(() => {
            btnLogin.style.transition = 'box-shadow 0.4s ease';
            btnLogin.style.boxShadow = '0 0 18px rgba(100, 181, 246, 0.45)';
            setTimeout(() => {
                btnLogin.style.boxShadow = '';
            }, 1200);
        }, 1000);
    }

});