<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Ingreso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $datos = $this->obtenerDatosHistorial($request);

        return view('historial.index', $datos);
    }

    public function exportarPdf(Request $request)
    {
        $datos = $this->obtenerDatosHistorial($request);
        $datos['usuario'] = Auth::user();
        $datos['fechaGeneracion'] = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('historial.pdf', $datos)->setPaper('a4', 'portrait');
        $nombreArchivo = 'historial-financiero-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    public function exportarExcel(Request $request)
    {
        $datos = $this->obtenerDatosHistorial($request);
        $usuario = Auth::user();
        $nombreArchivo = 'historial-financiero-' . now()->format('Y-m-d') . '.xlsx';
        $rutaTemporal = storage_path('app/' . uniqid('excel_', true) . '.xlsx');

        $this->generarArchivoXlsx($rutaTemporal, $datos, $usuario);

        return response()->download($rutaTemporal, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function obtenerDatosHistorial(Request $request): array
    {
        $userId = Auth::id();
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $tipo = $request->input('tipo', 'todos');

        $movimientos = collect();

        if ($tipo !== 'gasto') {
            $q = Ingreso::where('usuario_idusuario', $userId);
            if ($desde) $q->where('fecha_ingreso', '>=', $desde);
            if ($hasta) $q->where('fecha_ingreso', '<=', $hasta);

            $movimientos = $movimientos->concat(
                $q->get()->map(fn ($i) => [
                    'tipo' => 'Ingreso',
                    'descripcion' => $i->descripcion,
                    'monto' => (float) $i->monto,
                    'fecha' => $i->fecha_ingreso,
                    'extra' => null,
                ])
            );
        }

        if ($tipo !== 'ingreso') {
            $q = Gasto::where('usuario_idusuario', $userId)->with('categoria');
            if ($desde) $q->where('fecha_gasto', '>=', $desde);
            if ($hasta) $q->where('fecha_gasto', '<=', $hasta);

            $movimientos = $movimientos->concat(
                $q->get()->map(fn ($g) => [
                    'tipo' => 'Gasto',
                    'descripcion' => $g->descripcion,
                    'monto' => (float) $g->monto,
                    'fecha' => $g->fecha_gasto,
                    'extra' => ($g->categoria->nombre_categoria ?? 'Sin categoría') . ' · ' . ucfirst($g->tipo_gasto),
                ])
            );
        }

        $movimientos = $movimientos->sortByDesc('fecha')->values();

        $totalIngresos = $movimientos->where('tipo', 'Ingreso')->sum('monto');
        $totalGastos = $movimientos->where('tipo', 'Gasto')->sum('monto');
        $balance = $totalIngresos - $totalGastos;

        return compact('movimientos', 'totalIngresos', 'totalGastos', 'balance', 'desde', 'hasta', 'tipo');
    }

    private function generarArchivoXlsx(string $rutaDestino, array $datos, $usuario): void
    {
        $filas = [
            ['SMART AHORRO - HISTORIAL FINANCIERO PERSONAL'],
            ['Usuario:', $usuario->nombre ?? $usuario->name ?? $usuario->email ?? 'Usuario'],
            ['Fecha de generación:', now()->format('d/m/Y H:i')],
            ['Periodo:', ($datos['desde'] ?: 'Inicio') . ' hasta ' . ($datos['hasta'] ?: 'Actualidad')],
            [],
            ['Total Ingresos (COP)', 'Total Gastos (COP)', 'Balance Neto (COP)'],
            [(float) $datos['totalIngresos'], (float) $datos['totalGastos'], (float) $datos['balance']],
            [],
            ['Fecha', 'Tipo', 'Descripción', 'Detalle / Categoría', 'Monto (COP)'],
        ];

        foreach ($datos['movimientos'] as $m) {
            $montoConSigno = $m['tipo'] === 'Ingreso' ? (float) $m['monto'] : -(float) $m['monto'];
            $filas[] = [
                Carbon::parse($m['fecha'])->format('d/m/Y'),
                $m['tipo'],
                $m['descripcion'] ?: '—',
                $m['extra'] ?: '—',
                $montoConSigno,
            ];
        }

        $xmlFilas = '';
        foreach ($filas as $rIndex => $celdas) {
            $rowNum = $rIndex + 1;
            $xmlFilas .= '<row r="' . $rowNum . '">';
            foreach ($celdas as $cIndex => $valor) {
                $colLetra = $this->columnaExcel($cIndex);
                $ref = $colLetra . $rowNum;
                $esCabecera = in_array($rowNum, [1, 6, 9], true);
                $styleAttr = $esCabecera ? ' s="1"' : '';

                if (is_int($valor) || is_float($valor)) {
                    $xmlFilas .= '<c r="' . $ref . '" s="2"><v>' . $valor . '</v></c>';
                } else {
                    $textoSeguro = htmlspecialchars((string) $valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $xmlFilas .= '<c r="' . $ref . '" t="inlineStr"' . $styleAttr . '><is><t>' . $textoSeguro . '</t></is></c>';
                }
            }
            $xmlFilas .= '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="16" customWidth="1"/>'
            . '<col min="3" max="3" width="36" customWidth="1"/>'
            . '<col min="4" max="4" width="30" customWidth="1"/>'
            . '<col min="5" max="5" width="20" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $xmlFilas . '</sheetData>'
            . '</worksheet>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF111111"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Historial Financiero" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $zip = new ZipArchive();
        $zip->open($rutaDestino, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $relsXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();
    }

    private function columnaExcel(int $indice): string
    {
        $letra = '';
        while ($indice >= 0) {
            $letra = chr($indice % 26 + 65) . $letra;
            $indice = intdiv($indice, 26) - 1;
        }
        return $letra;
    }
}