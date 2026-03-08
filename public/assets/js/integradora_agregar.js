/* =============================================
   integradora_agregar.js
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */

function val() {
    var c = parseInt(document.getElementById('cant').value);
    if (isNaN(c) || c < 1) {
        alert('La cantidad debe ser al menos 1.');
        return false;
    }
    if (c > 10) {
        alert('La cantidad maxima es 10.');
        return false;
    }
    return true;
}
