<?php
/* generar_reporte.php - Factura de Venta en PDF (FPDF)
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

/* ── Calculos de IVA ────────────────────────────────── */
$totalFinal   = (float)$orden['total'];
$subtotalSinIva = round($totalFinal / 1.16, 2);
$ivaAmount      = round($totalFinal - $subtotalSinIva, 2);

/* ═══════════════════════════════════════════════════
   Clase PDF con cabecera y pie personalizados
   ═══════════════════════════════════════════════════ */
class FacturaPDF extends FPDF {

    var $logoPath;

    function Header() {
        /* Banda superior oscura */
        $this->SetFillColor(15, 15, 15);
        $this->Rect(0, 0, 216, 46, 'F');

        /* Logo izquierda */
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 8, 4, 34);
        }

        /* Nombre de la tienda (a la derecha del logo) */
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(255, 107, 53);
        $this->SetXY(46, 5);
        $this->Cell(0, 11, 'MOTOSTORE', 0, 1, 'L');

        /* Subtitulo */
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(200, 200, 200);
        $this->SetX(46);
        $this->Cell(0, 5, 'Motocicletas Premium  |  Guadalajara, Jalisco, Mexico', 0, 1, 'L');
        $this->SetX(46);
        $this->Cell(0, 5, 'Tel: (33) 1234-5678  |  contacto@motostore.mx  |  RFC: MST123456ABC', 0, 1, 'L');

        /* Linea separadora naranja */
        $this->SetDrawColor(255, 107, 53);
        $this->SetLineWidth(0.8);
        $this->Line(10, 46, 200, 46);

        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
        $this->SetY(52);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'Este documento es una factura de venta. Conservala para efectos fiscales y como comprobante de pago.', 0, 1, 'C');
        $this->Cell(0, 5, 'Pagina ' . $this->PageNo() . '/{nb}  |  Programacion Web 2 - CETI - Rafael Avila Sanchez 22300193', 0, 0, 'C');
    }

    /* Cabecera de tabla de productos */
    function TablaHeader() {
        $this->SetFillColor(15, 15, 15);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(70, 7, 'PRODUCTO',      1, 0, 'C', true);
        $this->Cell(35, 7, 'MARCA',         1, 0, 'C', true);
        $this->Cell(20, 7, 'CANT.',         1, 0, 'C', true);
        $this->Cell(30, 7, 'P. UNIT S/IVA', 1, 0, 'C', true);
        $this->Cell(35, 7, 'SUBTOTAL S/IVA',1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0);
    }
}

/* ─── Construir el PDF ───────────────────────────────── */
$pdf = new FacturaPDF('P', 'mm', 'Letter');
$pdf->logoPath = __DIR__ . '/img/Logo_motostore.png';
$pdf->AliasNbPages();
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

/* ── TITULO: FACTURA ───────────────────────────────── */
$pdf->SetFillColor(245, 245, 245);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 15, 15);
$pdf->Cell(0, 9, 'FACTURA DE VENTA', 'B', 1, 'C', false);
$pdf->Ln(4);

/* ── DATOS EN DOS COLUMNAS ─────────────────────────── */
$xLeft  = 10;
$xRight = 110;

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 53);
$pdf->SetXY($xLeft, $pdf->GetY());
$pdf->Cell(90, 6, 'Datos de la Factura', 0, 0, 'L');
$pdf->SetXY($xRight, $pdf->GetY());
$pdf->Cell(0, 6, 'Datos del Cliente', 0, 1, 'L');

$pdf->SetDrawColor(255, 107, 53);
$pdf->Line($xLeft,  $pdf->GetY(), $xLeft  + 90, $pdf->GetY());
$pdf->Line($xRight, $pdf->GetY(), $xRight + 90, $pdf->GetY());
$pdf->Ln(2);

$yInfoStart = $pdf->GetY();

/* Bloque izquierdo */
$infoLeft = array(
    array('No. Factura', 'FAC-' . str_pad($orden['idOrden'], 6, '0', STR_PAD_LEFT)),
    array('Fecha',       date('d/m/Y H:i', strtotime($orden['fecha']))),
    array('Estado',      strtoupper($orden['estado'])),
    array('Ref. PayPal', $orden['paypal_order_id'] ? substr($orden['paypal_order_id'], 0, 20) . '...' : 'N/A'),
    array('Usuario',     $orden['usuarioU']),
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
    array('Nombre',   $orden['nombre'] . ' ' . $orden['apellido']),
    array('Telefono', $orden['telefono']),
    array('Email',    $orden['email']),
    array('RFC',      'XAXX010101000'),
    array('Notas',    $orden['notas'] ? $orden['notas'] : '—'),
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

$pdf->SetY(max($pdf->GetY(), $yRight) + 4);

/* ── TABLA DE PRODUCTOS ─────────────────────────── */
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(255, 107, 53);
$pdf->Cell(0, 6, 'Relacion de Productos', 0, 1, 'L');
$pdf->SetDrawColor(255, 107, 53);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetDrawColor(0, 0, 0);

$pdf->TablaHeader();

$fill = false;
$grandSubSinIva = 0;
foreach ($items as $item) {
    $marca         = isset($item['marcaP']) ? $item['marcaP'] : '—';
    $precioSinIva  = round((float)$item['precioP'] / 1.16, 2);
    $subtotalSin   = round($precioSinIva * (int)$item['cantidad'], 2);
    $grandSubSinIva += $subtotalSin;

    $pdf->SetFillColor($fill ? 242 : 255, $fill ? 242 : 255, $fill ? 242 : 255);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(70, 7, $item['nombreP'],                        1, 0, 'L', true);
    $pdf->Cell(35, 7, $marca,                                  1, 0, 'C', true);
    $pdf->Cell(20, 7, $item['cantidad'],                       1, 0, 'C', true);
    $pdf->Cell(30, 7, '$' . number_format($precioSinIva, 2),   1, 0, 'R', true);
    $pdf->Cell(35, 7, '$' . number_format($subtotalSin, 2),    1, 1, 'R', true);
    $fill = !$fill;
}

/* ── BLOQUE SUBTOTAL / IVA / TOTAL ───────────────── */
$colLabel = 155;
$colVal   = 35;

/* Subtotal sin IVA */
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(40, 40, 40);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($colLabel, 7, 'Subtotal (sin IVA)', 1, 0, 'R', true);
$pdf->Cell($colVal,   7, '$' . number_format($subtotalSinIva, 2), 1, 1, 'R', true);

/* IVA 16% */
$pdf->SetFillColor(245, 245, 200);
$pdf->SetTextColor(80, 80, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colLabel, 7, 'IVA (16%)', 1, 0, 'R', true);
$pdf->Cell($colVal,   7, '$' . number_format($ivaAmount, 2), 1, 1, 'R', true);

/* Total */
$pdf->SetFillColor(15, 15, 15);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell($colLabel, 9, 'TOTAL A PAGAR (IVA INCLUIDO) MXN', 1, 0, 'R', true);
$pdf->Cell($colVal,   9, '$' . number_format($totalFinal, 2), 1, 1, 'R', true);

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
    "  Presenta esta factura impresa o en pantalla al momento de recoger tu moto.\n" .
    "  Direccion: Av. Vallarta 123, Guadalajara, Jalisco, Mexico.",
    1, 'L', true);

/* ── LEYENDA FISCAL ──────────────────────────────── */
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->Cell(0, 5, 'Precios expresados en Pesos Mexicanos (MXN). IVA incluido en el total. Referencia PayPal: ' . ($orden['paypal_order_id'] ? $orden['paypal_order_id'] : 'N/A'), 'T', 1, 'C');

/* ── OUTPUT ──────────────────────────────────────── */
$pdf->Output('I', 'factura_' . str_pad($idOrden, 6, '0', STR_PAD_LEFT) . '.pdf');
?>
