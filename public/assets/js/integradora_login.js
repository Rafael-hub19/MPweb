/* =============================================
   integradora_login.js
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */

function val() {
    var u = document.getElementById('u').value.trim();
    var p = document.getElementById('p').value.trim();
    if (!u) {
        alert('El usuario es obligatorio.');
        return false;
    }
    if (!p) {
        alert('La contrasena es obligatoria.');
        return false;
    }
    if (p.length < 4) {
        alert('La contrasena debe tener al menos 4 caracteres.');
        return false;
    }
    return true;
}
