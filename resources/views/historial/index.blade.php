@extends('layouts.app')

@section('title', 'Historial - Smart Ahorro')
@section('page-title', 'Historial Financiero')
@section('page-subtitle', 'Consulta todos tus movimientos en un solo lugar.')

@section('content')

  <x-card>
    <form method="GET" action="{{ route('historial.index') }}" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
      <div class="campo" style="margin-bottom:0;">
        <label for="desde">Desde</label>
        <input type="date" name="desde" id="desde" value="{{ $desde }}" />
      </div>
      <div class="campo" style="margin-bottom:0;">
        <label for="hasta">Hasta</label>
        <input type="date" name="hasta" id="hasta" value="{{ $hasta }}" />
      </div>
      <div class="campo" style="margin-bottom:0;">
        <label for="tipo">Tipo</label>
        <select name="tipo" id="tipo">
          <option value="todos" {{ $tipo === 'todos' ? 'selected' : '' }}>Todos</option>
          <option value="ingreso" {{ $tipo === 'ingreso' ? 'selected' : '' }}>Solo ingresos</option>
          <option value="gasto" {{ $tipo === 'gasto' ? 'selected' : '' }}>Solo gastos</option>
        </select>
      </div>
      <button type="submit" class="btn-primario">Filtrar</button>
      <a href="{{ route('historial.index') }}" class="btn-secundario">Limpiar</a>
    </form>
  </x-card>

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin:16px 0;">
    <x-card>
      <p style="margin:0; font-size:13px; color:var(--texto-secundario);">Total ingresos (filtro actual)</p>
      <p style="margin:4px 0 0 0; font-size:22px; font-weight:700; color:var(--semaforo-verde);">
        + ${{ number_format($totalIngresos, 0, ',', '.') }}
      </p>
    </x-card>
    <x-card>
      <p style="margin:0; font-size:13px; color:var(--texto-secundario);">Total gastos (filtro actual)</p>
      <p style="margin:4px 0 0 0; font-size:22px; font-weight:700; color:var(--semaforo-rojo);">
        - ${{ number_format($totalGastos, 0, ',', '.') }}
      </p>
    </x-card>
  </div>

  <x-card titulo="Movimientos">
    @if ($movimientos->isEmpty())
      <p style="color:var(--texto-secundario); margin:0;">No hay movimientos para el filtro seleccionado.</p>
    @else
      <table class="tabla-lista">
        <thead><tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Detalle</th><th>Monto</th></tr></thead>
        <tbody>
          @foreach ($movimientos as $m)
            <tr>
              <td>{{ \Illuminate\Support\Carbon::parse($m['fecha'])->format('d/m/Y') }}</td>
              <td><x-badge :tipo="$m['tipo'] === 'Ingreso' ? 'cumplida' : 'variable'">{{ $m['tipo'] }}</x-badge></td>
              <td>{{ $m['descripcion'] ?: '—' }}</td>
              <td style="color:var(--texto-secundario); font-size:13px;">{{ $m['extra'] ?? '—' }}</td>
              <td style="font-weight:600; color: {{ $m['tipo'] === 'Ingreso' ? 'var(--semaforo-verde)' : 'var(--semaforo-rojo)' }};">
                {{ $m['tipo'] === 'Ingreso' ? '+' : '-' }} ${{ number_format($m['monto'], 0, ',', '.') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </x-card>
@endsection
