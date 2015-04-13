<?php

class PDF extends FPDF {

// Cabecera de página
    function Header() {
        global $title;
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(0, 10, 'Informe: Datos de Proveedor cod: ' . $title, 1, 0, 'C');
        // Salto de línea
        $this->Ln(20);
    }

// Pie de página
    function Footer() {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

}

class HPDF extends FPDF {

// Cabecera de página
    function Header() {
        global $title;
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Movernos a la derecha
        $this->Cell(80);
        // Título
        $this->Cell(0, 10, $title, 0, 0, 'L');
        // Salto de línea
        $this->Ln(20);
    }

// Pie de página
    function Footer() {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function SetCol($col) {
        // Establecer la posición de una columna dada
        $this->col = $col;
        $x = 10 + $col * 65;
        $this->SetLeftMargin($x);
        $this->SetX($x);
    }

    function AcceptPageBreak() {
        // Método que acepta o no el salto automático de página
        if ($this->col < 2) {
            // Ir a la siguiente columna
            $this->SetCol($this->col + 1);
            // Establecer la ordenada al principio
            $this->SetY($this->y0);
            // Seguir en esta página
            return false;
        } else {
            // Volver a la primera columna
            $this->SetCol(0);
            // Salto de página
            return true;
        }
    }

    function ChapterTitle($num, $label) {
        // Título
        $this->SetFont('Arial', '', 12);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(0, 6, "$label", 0, 1, 'L', true);
        $this->Ln(4);
        // Guardar ordenada
        $this->y0 = $this->GetY();
    }

    function ChapterBody($file, $s_str) {
        // Abrir fichero de texto
        $archivo = fopen(APP_PATH . 'scripts/' . LOG, 'r') or die('Problemas de permisos');
        $txt = "";
        while (!feof($archivo)) {
            $cadena = fgets($archivo);
            $fecha = strpos($cadena, 'Fecha:');
            $modulo = strpos($cadena, 'Modulo:');
            $accion = strpos($cadena, 'Accion:');
            $usuario = strpos($cadena, 'Usuario:');
            $len = strlen($cadena);
            $fecha_str = substr($cadena, $fecha + 6, ($modulo - $fecha - 6));
            $modulo_str = substr($cadena, $modulo + 7, ($accion - $modulo - 7));
            $accion_str = substr($cadena, $accion + 7, ($usuario - $accion - 7));
            $usuario_str = substr($cadena, $usuario + 8, ($len - $accion - 8));
            if (strpos($modulo_str, $s_str) !== false) {
                $txt.="$fecha_str          $accion_str            $usuario_str   ";
            }
        }
        fclose($archivo);
        $txt = utf8_decode($txt);
        // Fuente
        $this->SetFont('Times', '', 10);
        // Imprimir texto en una columna de 6 cm de ancho
        $this->MultiCell(0, 5, $txt);
        $this->Ln();
        // Cita en itálica
        $this->SetFont('', 'I');
        $this->Cell(0, 5, '(fin)');
        // Volver a la primera columna
        $this->SetCol(0);
    }

    function PrintChapter($num, $title, $file, $modulo) {
        // Añadir capítulo
        $this->AddPage();
        $this->ChapterTitle($num, $title);
        $this->ChapterBody($file, $modulo);
    }

}

class IPDF extends FPDF {

// Cargar los datos
    function LoadData($file) {
        // Leer las líneas del fichero
        $lines = file($file);
        $data = array();
        foreach ($lines as $line)
            $data[] = explode(';', trim($line));
        return $data;
    }

// Tabla simple
    function BasicTable($header, $data) {
        // Cabecera
        foreach ($header as $col)
            $this->Cell(23, 7, $col, 1);
        $this->Ln();
        // Datos
        foreach ($data as $row) {
            foreach ($row as $col)
                $this->Cell(23, 10, $col, 1);
            $this->Ln();
        }
    }

// Una tabla más completa
    function ImprovedTable($header, $data) {
        // Anchuras de las columnas
        $w = array(20, 15, 18, 13, 13, 25, 30, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16, 16);
        // Cabeceras
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        $this->Ln();
        // Datos
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR');
            $this->Cell($w[1], 6, $row[1], 'LR');
            $this->Cell($w[2], 6, $row[2], 'LR', 0);
            $this->Cell($w[3], 6, $row[3], 'LR', 0);
            $this->Cell($w[4], 6, $row[4], 'LR');
            $this->Cell($w[5], 6, $row[5], 'LR');
            $this->Cell($w[6], 6, substr_replace($row[6], ' ...', 15, strlen($row[6])), 'LR', 0);
            $this->Cell($w[8], 6, $row[7], 'LR', 0);
            $this->Cell($w[9], 6, $row[7], 'LR', 0);
            $this->Cell($w[10], 6, $row[7], 'LR', 0);
            $this->Cell($w[11], 6, $row[7], 'LR', 0);
            $this->Cell($w[12], 6, $row[7], 'LR', 0);
            $this->Cell($w[13], 6, $row[7], 'LR', 0);
            $this->Ln();
        }
        // Línea de cierre
        $this->Cell(array_sum($w), 0, '', 'T');
    }

// Tabla coloreada
    function FancyTable($header, $data) {
        // Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');
        // Cabecera
        $w = array(40, 35, 45, 40);
        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        // Restauración de colores y fuentes
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Datos
        $fill = false;
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, number_format($row[2]), 'LR', 0, 'R', $fill);
            $this->Cell($w[3], 6, number_format($row[3]), 'LR', 0, 'R', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Línea de cierre
        $this->Cell(array_sum($w), 0, '', 'T');
    }

}

?>