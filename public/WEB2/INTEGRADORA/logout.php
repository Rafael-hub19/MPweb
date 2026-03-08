<?php
/* =============================================
   logout.php - Cierre de Sesión
   Programación Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
