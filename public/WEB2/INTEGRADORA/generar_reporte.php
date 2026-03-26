<?php
require('../../../fpdf185/fpdf.php');
include 'conectbd.php';

class ReporteMotoStore extends FPDF {

    function Header() {
        // Dark header background
        $this->SetFillColor(30, 30, 30);
        $this->Rect(0, 0, 210, 38, 'F');

        // Main title
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->SetY(5);
        $this->Cell(0, 10, 'MotoStore - Reporte de Inventario', 0, 1, 'C');

        // Subtitle / course info
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, 'CETI  |  Programacion Web 2  |  Mtra. Patricia Torres', 0, 1, 'C');
        $this->Cell(0, 6, 'Rafael Avila Sanchez - 22300193', 0, 1, 'C');

        // Date (right-aligned inside dark bar)
        $this->SetFont('Arial', 'I', 8);
        $this->SetY(28);
        $this->Cell(0, 6, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R');

        // Move below header
        $this->SetY(44);

        // Column headers
        $this->SetFillColor(30, 30, 30);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->SetLineWidth(0);

        $this->Cell(15, 8, 'ID',          1, 0, 'C', true);
        $this->Cell(65, 8, 'Nombre',      1, 0, 'C', true);
        $this->Cell(40, 8, 'Marca',       1, 0, 'C', true);
        $this->Cell(30, 8, 'Existencia',  1, 0, 'C', true);
        $this->Cell(40, 8, 'Precio MXN',  1, 1, 'C', true);

        // Reset text color for body
        $this->SetTextColor(0, 0, 0);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Query products with existenciaP > 5
$sql = "SELECT idP, nombreP, marcaP, existenciaP, precioP FROM productos WHERE existenciaP > 5 ORDER BY idP ASC";
$result = mysqli_query($conexion, $sql);

// Build PDF
$pdf = new ReporteMotoStore('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

$row_num = 0;
while ($row = mysqli_fetch_assoc($result)) {
    // Alternating row colors
    if ($row_num % 2 === 0) {
        $pdf->SetFillColor(255, 255, 255);
    } else {
        $pdf->SetFillColor(240, 240, 240);
    }

    $pdf->Cell(15, 7, $row['idP'],                             1, 0, 'C', true);
    $pdf->Cell(65, 7, $row['nombreP'],                         1, 0, 'L', true);
    $pdf->Cell(40, 7, $row['marcaP'],                          1, 0, 'C', true);
    $pdf->Cell(30, 7, $row['existenciaP'],                     1, 0, 'C', true);
    $pdf->Cell(40, 7, '$' . number_format($row['precioP'], 2), 1, 1, 'R', true);

    $row_num++;
}

// Output inline
$pdf->Output('I', 'reporte_motostore.pdf');
?>
