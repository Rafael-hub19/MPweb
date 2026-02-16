/* ============================================
   JavaScript para carrusel.html - Galería de Motos
   ============================================ */

// Variables globales
let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const totalSlides = slides.length;
const wrapper = document.getElementById('carouselWrapper');
const indicatorsContainer = document.getElementById('indicators');

/**
 * Crea los indicadores del carrusel
 */
function createIndicators() {
    for (let i = 0; i < totalSlides; i++) {
        const indicator = document.createElement('div');
        indicator.className = 'indicator';
        if (i === 0) indicator.classList.add('active');
        indicator.onclick = () => goToSlide(i);
        indicatorsContainer.appendChild(indicator);
    }
}

/**
 * Actualiza la posición del carrusel y los indicadores
 */
function updateCarousel() {
    wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    
    // Actualizar indicadores
    const indicators = document.querySelectorAll('.indicator');
    indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
    });
}

/**
 * Avanza al siguiente slide
 */
function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
}

/**
 * Retrocede al slide anterior
 */
function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    updateCarousel();
}

/**
 * Va a un slide específico
 * @param {number} index - Índice del slide
 */
function goToSlide(index) {
    currentSlide = index;
    updateCarousel();
}

/**
 * Maneja los gestos táctiles
 */
function setupTouchGestures() {
    let touchStartX = 0;
    let touchEndX = 0;

    wrapper.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    });

    wrapper.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        if (touchStartX - touchEndX > 50) nextSlide();
        if (touchEndX - touchStartX > 50) prevSlide();
    });
}

/**
 * Inicializa el carrusel
 */
function initCarousel() {
    createIndicators();
    setupTouchGestures();
    
    // Auto-avanzar cada 5 segundos
    setInterval(nextSlide, 5000);
}

// Iniciar cuando el DOM esté listo
initCarousel();