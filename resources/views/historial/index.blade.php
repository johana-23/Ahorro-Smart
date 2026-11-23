@extends('layouts.app')

@section('title', 'Historial - Smart Ahorro')
@section('page-title', 'Historial Financiero')
@section('page-subtitle', 'Consulta todos tus movimientos en un solo lugar.')

@section('content')

  <x-card>
    <div style="display:flex; justify-content:space-between; align-items:end; gap:12px; flex-wrap:wrap;">
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

      <div class="descarga-dropdown" style="position:relative;">
        <button
          type="button"
          id="btnDescargarHistorial"
          class="btn-secundario"
          aria-expanded="false"
          aria-controls="menuDescargarHistorial"
          style="display:inline-flex; align-items:center; gap:8px;"
        >
          <span>Descargar historial financiero</span>
          <span aria-hidden="true" style="font-size:11px;">▼</span>
        </button>

        <div
          id="menuDescargarHistorial"
          hidden
          style="
            position:absolute;
            right:0;
            top:calc(100% + 6px);
            min-width:230px;
            background:var(--superficie);
            border:1px solid var(--borde);
            border-radius:var(--radio);
            box-shadow:var(--sombra);
            padding:6px;
            z-index:50;
          "
        >
          <a
            href="{{ route('historial.exportar.pdf', request()->only(['desde', 'hasta', 'tipo'])) }}"
            class="opcion-descarga"
            style="
              display:block;
              padding:9px 12px;
              border-radius:7px;
              color:var(--texto-principal);
              text-decoration:none;
              font-size:14px;
            "
          >
            Descargar en PDF (.pdf)
          </a>
          <a
            href="{{ route('historial.exportar.excel', request()->only(['desde', 'hasta', 'tipo'])) }}"
            class="opcion-descarga"
            style="
              display:block;
              padding:9px 12px;
              border-radius:7px;
              color:var(--texto-principal);
              text-decoration:none;
              font-size:14px;
            "
          >
            Descargar en Excel (.xlsx)
          </a>
        </div>
      </div>
    </div>
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

  <style>
    .opcion-descarga:hover {
      background: var(--fondo);
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('btnDescargarHistorial');
      const menu = document.getElementById('menuDescargarHistorial');

      if (!btn || !menu) return;

      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const abierto = !menu.hasAttribute('hidden');
        if (abierto) {
          menu.setAttribute('hidden', '');
          btn.setAttribute('aria-expanded', 'false');
        } else {
          menu.removeAttribute('hidden');
          btn.setAttribute('aria-expanded', 'true');
        }
      });

      menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
          menu.setAttribute('hidden', '');
          btn.setAttribute('aria-expanded', 'false');
        });
      });

      document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && e.target !== btn) {
          menu.setAttribute('hidden', '');
          btn.setAttribute('aria-expanded', 'false');
        }
      });
    });
  </script>
@endsection