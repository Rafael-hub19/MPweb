<?php
/* generar_ticket.php - Ticket de Compra (formato termico) en PDF (FPDF)
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
$sqlDet = "SELECT od.nombreP, od.precioP, od.cantidad
           FROM orden_detalle od
           WHERE od.idOrden = $idOrden";
$resDet = mysqli_query($conexion, $sqlDet);
$items  = array();
while ($row = mysqli_fetch_assoc($resDet)) {
    $items[] = $row;
}
mysqli_close($conexion);

/* ── Calculos de IVA ────────────────────────────────── */
$totalFinal     = (float)$orden['total'];
$subtotalSinIva = round($totalFinal / 1.16, 2);
$ivaAmount      = round($totalFinal - $subtotalSinIva, 2);

/* ═══════════════════════════════════════════════════
   Calcular altura dinamica del ticket
   Cabecera ~70mm (con logo) + por item ~12mm + pie ~60mm
   ═══════════════════════════════════════════════════ */
$numItems   = count($items);
$alturaBase = 70 + ($numItems * 14) + 70;
$alturaMin  = 200;
$altura     = max($alturaBase, $alturaMin);

/* ═══════════════════════════════════════════════════
   Clase Ticket
   ═══════════════════════════════════════════════════ */
class TicketTermico extends FPDF {

    /* Dibuja una linea de guiones ocupando todo el ancho util */
    function Separador($caracter = '-') {
        $this->SetFont('Courier', '', 8);
        $this->SetTextColor(150, 150, 150);
        /* 76mm util / ~2.1mm por caracter Courier 8pt ≈ 36 chars */
        $this->Cell(0, 4, str_repeat($caracter, 36), 0, 1, 'C');
        $this->SetTextColor(20, 20, 20);
    }
}

/* ─── Construir el PDF ───────────────────────────────── */
/* Formato custom: 80mm de ancho */
$pdf = new TicketTermico('P', 'mm', array(80, $altura));
$pdf->SetMargins(3, 3, 3);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$w = 74; /* ancho util (80 - 3 - 3) */

/* ══════════════════ CABECERA ══════════════════════ */
$logoPath = __DIR__ . '/img/Logo_motostore.png';

/* Fondo negro en cabecera */
$pdf->SetFillColor(15, 15, 15);
$pdf->Rect(0, 0, 80, 46, 'F');

/* Logo centrado */
if (file_exists($logoPath)) {
    $logoW = 22;
    $logoX = (80 - $logoW) / 2;
    $pdf->Image($logoPath, $logoX, 3, $logoW);
}

/* Nombre tienda */
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(255, 107, 53);
$pdf->SetY(27);
$pdf->Cell($w, 7, 'MOTOSTORE', 0, 1, 'C');

/* Slogan */
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(180, 180, 180);
$pdf->Cell($w, 4, 'Motocicletas Premium', 0, 1, 'C');
$pdf->Cell($w, 4, 'Guadalajara, Jalisco', 0, 1, 'C');
$pdf->Cell($w, 4, 'Tel: (33) 1234-5678', 0, 1, 'C');

/* Linea naranja bajo cabecera */
$pdf->SetDrawColor(255, 107, 53);
$pdf->SetLineWidth(0.6);
$pdf->Line(3, $pdf->GetY() + 1, 77, $pdf->GetY() + 1);
$pdf->Ln(3);

/* ══════════════════ TITULO TICKET ═════════════════ */
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($w, 6, 'TICKET DE COMPRA', 0, 1, 'C');

$pdf->Separador('=');

/* ══════════════════ DATOS ORDEN ═══════════════════ */
$pdf->SetFont('Courier', 'B', 7);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell($w, 4, 'FOLIO: #' . str_pad($orden['idOrden'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');

$pdf->SetFont('Courier', '', 7);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($w, 4, date('d/m/Y  H:i', strtotime($orden['fecha'])), 0, 1, 'C');
$pdf->Cell($w, 4, 'Cliente: ' . $orden['nombre'] . ' ' . $orden['apellido'], 0, 1, 'C');
$pdf->Cell($w, 4, 'Tel: ' . $orden['telefono'], 0, 1, 'C');

$pdf->Separador('-');

/* ══════════════════ PRODUCTOS ═════════════════════ */
$pdf->SetFont('Courier', 'B', 7);
$pdf->SetTextColor(20, 20, 20);

/* Encabezado columnas */
$pdf->Cell(32, 5, 'PRODUCTO', 0, 0, 'L');
$pdf->Cell(8,  5, 'CANT', 0, 0, 'C');
$pdf->Cell(16, 5, 'P.UNIT', 0, 0, 'R');
$pdf->Cell(18, 5, 'TOTAL', 0, 1, 'R');

$pdf->SetDrawColor(180, 180, 180);
$pdf->SetLineWidth(0.2);
$pdf->Line(3, $pdf->GetY(), 77, $pdf->GetY());
$pdf->Ln(1);

$pdf->SetFont('Courier', '', 7);
$pdf->SetTextColor(30, 30, 30);

foreach ($items as $item) {
    $subtotal = (float)$item['precioP'] * (int)$item['cantidad'];

    /* Nombre del producto puede ser largo, dividirlo */
    $nombre = $item['nombreP'];
    if (strlen($nombre) > 22) {
        $nombre = substr($nombre, 0, 21) . '.';
    }

    $pdf->Cell(32, 5, $nombre, 0, 0, 'L');
    $pdf->Cell(8,  5, $item['cantidad'], 0, 0, 'C');
    $pdf->Cell(16, 5, '$' . number_format((float)$item['precioP'], 0, '.', ','), 0, 0, 'R');
    $pdf->Cell(18, 5, '$' . number_format($subtotal, 0, '.', ','), 0, 1, 'R');
}

$pdf->Line(3, $pdf->GetY(), 77, $pdf->GetY());
$pdf->Ln(1);

/* ══════════════════ TOTALES ═══════════════════════ */
$pdf->SetFont('Courier', '', 8);
$pdf->SetTextColor(60, 60, 60);

/* Subtotal sin IVA */
$pdf->Cell(46, 5, 'Subtotal (sin IVA):', 0, 0, 'L');
$pdf->Cell(28, 5, '$' . number_format($subtotalSinIva, 2), 0, 1, 'R');

/* IVA */
$pdf->Cell(46, 5, 'IVA (16%):', 0, 0, 'L');
$pdf->Cell(28, 5, '$' . number_format($ivaAmount, 2), 0, 1, 'R');

/* Total */
$pdf->SetDrawColor(255, 107, 53);
$pdf->SetLineWidth(0.5);
$pdf->Line(3, $pdf->GetY(), 77, $pdf->GetY());
$pdf->Ln(1);

$pdf->SetFont('Courier', 'B', 10);
$pdf->SetTextColor(15, 15, 15);
$pdf->Cell(38, 7, 'TOTAL MXN:', 0, 0, 'L');
$pdf->Cell(36, 7, '$' . number_format($totalFinal, 2), 0, 1, 'R');

$pdf->Separador('=');

/* ══════════════════ FORMA DE PAGO ═════════════════ */
$pdf->SetFont('Courier', '', 7);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($w, 4, 'Forma de pago: PayPal', 0, 1, 'C');

/* Ref PayPal truncada */
$refPaypal = $orden['paypal_order_id'] ? $orden['paypal_order_id'] : 'N/A';
if (strlen($refPaypal) > 24) {
    $refPaypal = substr($refPaypal, 0, 12) . '...' . substr($refPaypal, -8);
}
$pdf->Cell($w, 4, 'Ref: ' . $refPaypal, 0, 1, 'C');

$pdf->Separador('-');

/* ══════════════════ PIE ═══════════════════════════ */
$pdf->SetFillColor(15, 15, 15);
$pdf->Rect(0, $pdf->GetY(), 80, $altura, 'F');

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(255, 107, 53);
$pdf->Cell($w, 5, 'Recoge tu moto en agencia:', 0, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(180, 180, 180);
$pdf->Cell($w, 4, 'Av. Vallarta 123, Gdl., Jal.', 0, 1, 'C');
$pdf->Cell($w, 4, 'Te llamaremos al ' . $orden['telefono'], 0, 1, 'C');

$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 7);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell($w, 4, 'Gracias por tu compra!', 0, 1, 'C');
$pdf->Cell($w, 4, 'CETI - Prog. Web 2 - 22300193', 0, 1, 'C');

/* ── OUTPUT ──────────────────────────────────────── */
$pdf->Output('I', 'ticket_' . str_pad($idOrden, 6, '0', STR_PAD_LEFT) . '.pdf');
?>
