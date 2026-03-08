<?php
/* =============================================
   conectbd.php - Conexión a Base de Datos
   Programación Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   ============================================= */

$conexion = mysqli_connect("localhost", "mueblesweb", "429afaa4d", "mueblesweb")
    or die("Fallo en el establecimiento de la conexión");

mysqli_set_charset($conexion, "utf8");

// Seleccionar la base de datos
mysqli_select_db($conexion, "mueblesweb")
    or die("Error en la selección de la base de datos");
?>
