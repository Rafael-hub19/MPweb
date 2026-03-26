<?php
/* generar_reporte.php - Ticket de Compra en PDF (FPDF)
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require('../../../fpdf185/fpdf.php');
include 'conectbd.php';

$idOrden = isset($_GET['orden']) ? intval($_GET['orden']) : 0;
if ($idOrden <= 0) {
    header('Location: index.php');
    exit();
}

/* ── Consultar orden ────────────────────────────────── */
$sqlOrden = "SELECT o.idOrden, o.nombre, o.apellido, o.telefono, o.email,
                    o.notas, o.total, o.paypal_order_id, o.estado, o.fecha,
                    u.usuarioU
             FROM ordenes o
             JOIN usuarios u ON o.idUsuario = u.idU
             WHERE o.idOrden = $idOrden";
$resOrden = mysqli_query($conexion, $sqlOrden);
if (!$resOrden || mysqli_num_rows($resOrden) === 0) {
    header('Location: index.php');
    exit();
}
$orden = mysqli_fetch_assoc($resOrden);

/* ── Consultar detalle ──────────────────────────────── */
$sqlDet = "SELECT od.nombreP, p.marcaP, od.precioP, od.cantidad
           FROM orden_detalle od
           LEFT JOIN productos p ON od.idP = p.idP
           WHERE od.idOrden = $idOrden";
$resDet = mysqli_query($conexion, $sqlDet);
$items  = array();
while ($row = mysqli_fetch_assoc($resDet)) {
    $items[] = $row;
}
mysqli_close($conexion);

/* ═══════════════════════════════════════════════════
   Clase PDF con cabecera y pie personalizados
   ═══════════════════════════════════════════════════ */
class TicketPDF extends FPDF {

    var $orden;

    function Header() {
        /* Banda superior oscura */
        $this->SetFillColor(15, 15, 15);
        $this->Rect(0, 0, 216, 42, 'F');

        /* Nombre de la tienda */
        $this->SetFont('Arial', 'B', 26);
        $this->SetTextColor(255, 107, 53);   // naranja #ff6b35
        $this->SetY(6);
        $this->Cell(0, 12, 'MOTOSTORE', 0, 1, 'C');

        /* Subtítulo */
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(200, 200, 200);
        $this->Cell(0, 5, 'Motocicletas Premium  |  Guadalajara, Jalisco, Mexico', 0, 1, 'C');
        $this->Cell(0, 5, 'Tel: (33) 1234-5678  |  contacto@motostore.mx', 0, 1, 'C');

        /* Linea separadora naranja */
        $this->SetDrawColor(255, 107, 53);
        $this->SetLineWidth(0.8);
        $this->Line(10, 42, 200, 42);

        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->SetY(48);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'Gracias por tu compra en MotoStore. Conserva este ticket como comprobante de pago.', 0, 1, 'C');
        $this->Cell(0, 5, 'Pagina ' . $this->PageNo() . '/{nb}  |  Programacion Web 2 - CETI - Rafael Avila Sanchez 22300193', 0, 0, 'C');
    }

    /* Bloque de info (etiqueta + valor) */
    function InfoRow($label, $value) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(45, 6, strtoupper($label), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(20, 20, 20);
        $this->Cell(0, 6, $value, 0, 1, 'L');
    }

    /* Cabecera de tabla de productos */
    function TablaHeader() {
        $this->SetFillColor(15, 15, 15);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(70, 7, 'PRODUCTO',    1, 0, 'C', true);
        $this->Cell(35, 7, 'MARCA',       1, 0, 'C', true);
        $this->Cell(20, 7, 'CANT.',       1, 0, 'C', true);
        $this->Cell(30, 7, 'P. UNIT.',    1, 0, 'C', true);
        $this->Cell(35, 7, 'SUBTOTAL',    1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
    }
}

/* ─── Construir el PDF ───────────────────────────────── */
$pdf = new TicketPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

/* ── TITULO DEL TICKET ─────────────────────────────── */
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(15, 15, 15);
$pdf->Cell(0, 9, 'TICKET DE COMPRA', 'B', 1, 'C', false);
$pdf->Ln(4);

/* ── DATOS DE LA ORDEN (dos columnas) ─────────────── */
/* Columna izquierda */
$xLeft  = 10;
$xRight = 110;
$pdf->SetXY($xLeft, $pdf->GetY());

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 53);
$pdf->Cell(90, 6, 'Datos de la Orden', 0, 0, 'L');
$pdf->SetXY($xRight, $pdf->GetY());
$pdf->Cell(0, 6, 'Datos de Contacto', 0, 1, 'L');

$pdf->SetDrawColor(255, 107, 53);
$pdf->Line($xLeft,  $pdf->GetY(), $xLeft  + 90, $pdf->GetY());
$pdf->Line($xRight, $pdf->GetY(), $xRight + 90, $pdf->GetY());
$pdf->Ln(2);

$yInfoStart = $pdf->GetY();

/* Bloque izquierdo */
$pdf->SetXY($xLeft, $yInfoStart);
$pdf->SetDrawColor(0, 0, 0);

$infoLeft = array(
    array('No. Orden',  '#' . str_pad($orden['idOrden'], 6, '0', STR_PAD_LEFT)),
    array('Fecha',      date('d/m/Y H:i', strtotime($orden['fecha']))),
    array('Estado',     strtoupper($orden['estado'])),
    array('Referencia', $orden['paypal_order_id'] ? substr($orden['paypal_order_id'], 0, 20) . '...' : 'N/A'),
    array('Usuario',    $orden['usuarioU']),
);
foreach ($infoLeft as $r) {
    $pdf->SetXY($xLeft, $pdf->GetY());
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(32, 6, strtoupper($r[0]) . ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(58, 6, $r[1], 0, 1, 'L');
}

/* Bloque derecho */
$yRight = $yInfoStart;
$infoRight = array(
    array('Nombre',    $orden['nombre'] . ' ' . $orden['apellido']),
    array('Telefono',  $orden['telefono']),
    array('Email',     $orden['email']),
    array('Notas',     $orden['notas'] ? $orden['notas'] : '—'),
);
foreach ($infoRight as $r) {
    $pdf->SetXY($xRight, $yRight);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(28, 6, strtoupper($r[0]) . ':', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(62, 6, $r[1], 0, 0, 'L');
    $yRight += 6;
}

/* Ir al punto más bajo de ambas columnas */
$pdf->SetY(max($pdf->GetY(), $yRight) + 4);

/* ── TABLA DE PRODUCTOS ─────────────────────────── */
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 53);
$pdf->Cell(0, 6, 'Productos Adquiridos', 0, 1, 'L');
$pdf->SetDrawColor(255, 107, 53);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetDrawColor(0, 0, 0);

$pdf->TablaHeader();

$fill = false;
foreach ($items as $item) {
    $marca    = isset($item['marcaP'])  ? $item['marcaP']  : '—';
    $subtotal = $item['precioP'] * $item['cantidad'];

    $pdf->SetFillColor($fill ? 242 : 255, $fill ? 242 : 255, $fill ? 242 : 255);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(70, 7, $item['nombreP'],                             1, 0, 'L', true);
    $pdf->Cell(35, 7, $marca,                                       1, 0, 'C', true);
    $pdf->Cell(20, 7, $item['cantidad'],                            1, 0, 'C', true);
    $pdf->Cell(30, 7, '$' . number_format($item['precioP'], 2),    1, 0, 'R', true);
    $pdf->Cell(35, 7, '$' . number_format($subtotal, 2),           1, 1, 'R', true);
    $fill = !$fill;
}

/* ── FILA TOTAL ──────────────────────────────────── */
$pdf->SetFillColor(15, 15, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(155, 8, 'TOTAL A PAGAR (MXN)', 1, 0, 'R', true);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(35,  8, '$' . number_format($orden['total'], 2), 1, 1, 'R', true);

/* ── NOTA DE RECOGIDA ────────────────────────────── */
$pdf->Ln(6);
$pdf->SetFillColor(230, 243, 255);
$pdf->SetDrawColor(100, 160, 220);
$pdf->SetLineWidth(0.5);

$pdf->SetTextColor(20, 60, 100);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, '  INFORMACION DE RECOGIDA EN AGENCIA', 1, 1, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(240, 248, 255);
$pdf->MultiCell(0, 5,
    "  Tu motocicleta estara lista para recoger en nuestra agencia.\n" .
    "  Nos pondremos en contacto al telefono " . $orden['telefono'] . " para coordinar fecha y hora de entrega.\n" .
    "  Presenta este ticket impreso o en pantalla al momento de recoger tu moto.\n" .
    "  Direccion: Av. Vallarta 123, Guadalajara, Jalisco, Mexico.",
    1, 'L', true);

/* ── REFERENCIA PAYPAL ───────────────────────────── */
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->Cell(0, 5, 'Referencia de pago PayPal: ' . ($orden['paypal_order_id'] ? $orden['paypal_order_id'] : 'N/A'), 'T', 1, 'C');

/* ── OUTPUT ──────────────────────────────────────── */
$pdf->Output('I', 'ticket_orden_' . $idOrden . '.pdf');
?>
