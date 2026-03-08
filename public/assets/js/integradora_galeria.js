/* =============================================
   integradora_galeria.js
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */

function valQty(f) {
    var q = parseInt(f.cantidad.value);
    if (isNaN(q) || q < 1) {
        alert('La cantidad debe ser al menos 1.');
        f.cantidad.focus();
        return false;
    }
    if (q > 10) {
        alert('La cantidad maxima es 10.');
        f.cantidad.focus();
        return false;
    }
    return true;
}
