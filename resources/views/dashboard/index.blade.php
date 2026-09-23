@extends('layouts.app')

@section('title', 'Dashboard - Smart Ahorro')
@section('page-title')
  Hola, {{ Auth::user()->name }} 👋
@endsection
@section('page-subtitle', 'Este es el resumen de tu salud financiera hoy.')

@section('content')

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">

    {{-- SEMÁFORO FINANCIERO (RF-032) --}}
    <x-card titulo="Semáforo financiero">
      <div style="display:flex; align-items:center; gap:12px;">
        <span class="semaforo-punto semaforo-{{ $semaforo['color'] }}" style="width:28px;height:28px;"></span>
        <div>
          <p style="margin:0; font-weight:600; text-transform:capitalize;">{{ $semaforo['color'] }}</p>
          <p style="margin:0; font-size:13px; color:var(--texto-secundario);">{{ $semaforo['mensaje'] }}</p>
        </div>
      </div>
    </x-card>

    {{-- PLANTA DE AHORRO (RF-033) --}}
    <x-card titulo="Planta de ahorro">
      <div class="planta-card">
        <div class="planta-emoji">{{ $planta->emoji }}</div>
        <p class="planta-estado-{{ $planta->estado_planta }}" style="font-weight:600; margin:8px 0 4px 0;">
          {{ $planta->mensaje }}
        </p>
        @if ($planta->estado_planta !== 'floreciendo' && $metas->isNotEmpty())
          <a href="{{ route('metas.show', $metas->first()) }}" class="btn-primario" style="display:inline-block; margin-top:6px;">Cuidar planta</a>
        @endif
      </div>
    </x-card>
  </div>

  <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-bottom:16px;">

    {{-- METAS ACTIVAS --}}
    <x-card titulo="Tus metas activas">
      <x-slot:acciones>
        <a href="{{ route('metas.index') }}" style="font-size:13px; color:var(--acento);">Ver todas →</a>
      </x-slot:acciones>

      @if ($metas->isEmpty())
        <p style="color:var(--texto-secundario); margin:0;">Aún no tienes metas activas. <a href="{{ route('metas.create') }}">Crea una</a>.</p>
      @else
        @foreach ($metas as $meta)
          <div style="margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; font-size:14px;">
              <span>{{ $meta->nombre_meta }}</span>
              <span style="color:var(--texto-secundario);">{{ $meta->progreso }}%</span>
            </div>
            <x-progress-bar :porcentaje="$meta->progreso" />
          </div>
        @endforeach
      @endif
    </x-card>

    {{-- RECOMENDACIÓN (RF-031) --}}
    <x-card titulo="💡 Recomendación para ti">
      <p style="margin:0; font-size:14px;">{{ $recomendacion }}</p>
    </x-card>
  </div>

  {{-- ÚLTIMOS MOVIMIENTOS --}}
  <x-card titulo="Últimos movimientos">
    @if ($movimientos->isEmpty())
      <p style="color:var(--texto-secundario); margin:0;">Sin movimientos recientes.</p>
    @else
      <table class="tabla-lista">
        <thead><tr><th>Fecha</th><th>Tipo</th><th>Descripción</th><th>Monto</th></tr></thead>
        <tbody>
          @foreach ($movimientos as $m)
            <tr>
              <td>{{ \Illuminate\Support\Carbon::parse($m['fecha'])->format('d/m/Y') }}</td>
              <td><x-badge :tipo="$m['tipo'] === 'Ingreso' ? 'cumplida' : 'variable'">{{ $m['tipo'] }}</x-badge></td>
              <td>{{ $m['descripcion'] ?: '—' }}</td>
              <td style="color: {{ $m['tipo'] === 'Ingreso' ? 'var(--semaforo-verde)' : 'var(--semaforo-rojo)' }}; font-weight:600;">
                {{ $m['tipo'] === 'Ingreso' ? '+' : '-' }} ${{ number_format($m['monto'], 0, ',', '.') }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </x-card>

@endsection
