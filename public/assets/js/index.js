/* ============================================
   JavaScript para index.php - Panel de Entregas
   ============================================ */

/**
 * Botón de recargar página
 */
const btnReload = document.getElementById('btn-reload');
if (btnReload) {
    btnReload.addEventListener('click', () => {
        location.reload();
    });
}

/**
 * Efecto suave en las cards al pasar el mouse
 */
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-4px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

/**
 * Animación de aparición al cargar
 */
document.addEventListener('DOMContentLoaded', () => {
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
});