/* ============================================
   xml.js - Logica CRUD XML
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8 Semestre
   ============================================ */

/**
 * Cambia el tab activo del formulario CRUD.
 * @param {string} name - 'crear', 'actualizar' o 'eliminar'
 * @param {HTMLElement} btn  - Boton clickeado
 */
function switchTab(name, btn) {
    var panels = document.querySelectorAll('.form-panel');
    var tabs   = document.querySelectorAll('.tab');
    var i;

    for (i = 0; i < panels.length; i++) {
        panels[i].classList.remove('active');
    }

    for (i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }

    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}