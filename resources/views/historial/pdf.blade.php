<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial Financiero - Smart Ahorro</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #111111;
      margin: 24px;
    }
    .header {
      border-bottom: 2px solid #111111;
      padding-bottom: 12px;
      margin-bottom: 18px;
    }
    .header h1 {
      margin: 0;
      font-size: 20px;
      letter-spacing: 0.5px;
    }
    .header p {
      margin: 4px 0 0 0;
      font-size: 11px;
      color: #444444;
    }
    .resumen {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .resumen td {
      border: 1px solid #cccccc;
      padding: 10px;
      width: 33.33%;
    }
    .resumen-label {
      font-size: 10px;
      text-transform: uppercase;
      color: #555555;
      display: block;
      margin-bottom: 4px;
    }
    .resumen-valor {
      font-size: 15px;
      font-weight: bold;
    }
    table.movimientos {
      width: 100%;
      border-collapse: collapse;
    }
    table.movimientos th {
      background-color: #111111;
      color: #ffffff;
      text-align: left;
      padding: 8px;
      font-size: 11px;
    }
    table.movimientos td {
      border-bottom: 1px solid #dddddd;
      padding: 8px;
      font-size: 11px;
    }
    .text-right { text-align: right; }
    .ingreso { color: #15803d; font-weight: bold; }
    .gasto { color: #b91c1c; font-weight: bold; }
    .footer {
      margin-top: 24px;
      font-size: 10px;
      color: #666666;
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>SMART AHORRO — Historial Financiero</h1>
    <p>
      Usuario: <strong>{{ $usuario->nombre ?? $usuario->name ?? $usuario->email }}</strong> |
      Generado el: {{ $fechaGeneracion }} |
      Periodo: {{ $desde ?: 'Inicio' }} a {{ $hasta ?: 'Actualidad' }}
    </p>
  </div>

  <table class="resumen">
    <tr>
      <td>
        <span class="resumen-label">Total Ingresos</span>
        <span class="resumen-valor ingreso">+ ${{ number_format($totalIngresos, 0, ',', '.') }}</span>
      </td>
      <td>
        <span class="resumen-label">Total Gastos</span>
        <span class="resumen-valor gasto">- ${{ number_format($totalGastos, 0, ',', '.') }}</span>
      </td>
      <td>
        <span class="resumen-label">Balance Neto</span>
        <span class="resumen-valor">${{ number_format($balance, 0, ',', '.') }}</span>
      </td>
    </tr>
  </table>

  <table class="movimientos">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Descripción</th>
        <th>Detalle / Categoría</th>
        <th class="text-right">Monto (COP)</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($movimientos as $m)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($m['fecha'])->format('d/m/Y') }}</td>
          <td>{{ $m['tipo'] }}</td>
          <td>{{ $m['descripcion'] ?: '—' }}</td>
          <td>{{ $m['extra'] ?? '—' }}</td>
          <td class="text-right {{ $m['tipo'] === 'Ingreso' ? 'ingreso' : 'gasto' }}">
            {{ $m['tipo'] === 'Ingreso' ? '+' : '-' }} ${{ number_format($m['monto'], 0, ',', '.') }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" style="text-align:center; color:#666666;">No hay movimientos registrados para el periodo seleccionado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="footer">
    Documento generado automáticamente desde Smart Ahorro.
  </div>
</body>
</html>