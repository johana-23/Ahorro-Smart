@extends('layouts.app')

@section('title', 'Metas de Ahorro - Smart Ahorro')
@section('page-title', 'Metas de Ahorro')
@section('page-subtitle', 'Define y sigue el progreso de tus objetivos financieros.')

@section('content')
  <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
    <a href="{{ route('metas.create') }}" class="btn-gradiente"><i class="fa-solid fa-plus"></i> Nueva meta</a>
  </div>

  @if ($metas->isEmpty())
    <x-card>
      <p style="color:var(--texto-secundario); margin:0;">Todavía no tienes metas de ahorro. ¡Crea la primera!</p>
    </x-card>
  @else
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
      @foreach ($metas as $meta)
        <a href="{{ route('metas.show', $meta) }}" style="text-decoration:none; color:inherit;">
          <x-card>
            @if ($meta->imagen)
              <img src="{{ asset('storage/' . $meta->imagen) }}" alt="{{ $meta->nombre_meta }}" style="width:100%; height:120px; object-fit:contain;  background:var(--fondo); border-radius:8px; margin-bottom:12px;" />
            @endif
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
              <h4 style="margin:0;">{{ $meta->nombre_meta }}</h4>
              @if ($meta->estado_meta === 'cumplida')
                <x-badge tipo="cumplida">Cumplida 🎉</x-badge>
              @endif
            </div>
            <p style="font-size:13px; color:var(--texto-secundario); margin:0 0 10px 0;">
              ${{ number_format($meta->monto_actual, 0, ',', '.') }} de ${{ number_format($meta->monto_objetivo, 0, ',', '.') }}
            </p>
            <x-progress-bar :porcentaje="$meta->progreso" />
            <p style="font-size:12px; color:var(--texto-secundario); margin-top:6px;">{{ $meta->progreso }}% completado</p>
          </x-card>
        </a>
      @endforeach
    </div>
  @endif
@endsection
