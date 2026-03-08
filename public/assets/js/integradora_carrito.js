/* =============================================
   integradora_carrito.js
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */

function valU(f) {
    var q = parseInt(f.cantidad.value);
    if (isNaN(q) || q < 1) {
        alert('La cantidad debe ser al menos 1.');
        f.cantidad.focus();
        return false;
    }
    if (q > 99) {
        alert('La cantidad maxima es 99.');
        f.cantidad.focus();
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function () {
    var m = document.getElementById('mc');
    if (m) {
        m.addEventListener('click', function (e) {
            if (e.target === m) {
                m.classList.remove('show');
            }
        });
    }
});
