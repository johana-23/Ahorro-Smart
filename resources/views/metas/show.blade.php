@extends('layouts.app')

@section('title', $meta->nombre_meta . ' - Smart Ahorro')
@section('page-title', $meta->nombre_meta)
@section('page-subtitle', 'Detalle y seguimiento de tu meta.')

@section('content')
  <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <x-card>
      @if ($meta->imagen)
        <img src="{{ asset('storage/' . $meta->imagen) }}" alt="{{ $meta->nombre_meta }}" style="width:100%; max-height:220px; object-fit:contain; background:var(--fondo); border-radius:8px; margin-bottom:16px;" />
      @endif

      <p><strong>Ahorrado:</strong> ${{ number_format($meta->monto_actual, 0, ',', '.') }} de ${{ number_format($meta->monto_objetivo, 0, ',', '.') }}</p>
      <x-progress-bar :porcentaje="$meta->progreso" />
      <p style="font-size:13px; color:var(--texto-secundario); margin-top:6px;">{{ $meta->progreso }}% completado</p>

      <hr style="border:none; border-top:1px solid var(--borde); margin:16px 0;" />

      <p><strong>Fecha límite estimada:</strong> {{ $meta->fecha_limite->format('d/m/Y') }}</p>
      <p><strong>Debes ahorrar aprox.:</strong> ${{ number_format($meta->ahorro_periodico, 0, ',', '.') }} / mes</p>
      <p><strong>Estado:</strong> <x-badge :tipo="$meta->estado_meta === 'cumplida' ? 'cumplida' : 'progreso'">{{ ucfirst(str_replace('_', ' ', $meta->estado_meta)) }}</x-badge></p>
    </x-card>

    <x-card titulo="Aportar a esta meta">
      @if ($meta->estado_meta === 'cumplida')
        <p style="color:var(--semaforo-verde); font-weight:600;">¡Meta cumplida! 🎉</p>
      @else
        <form method="POST" action="{{ route('metas.aportar', $meta) }}">
          @csrf
          <div class="campo">
            <label for="monto">Monto a aportar</label>
            <input type="number" step="0.01" min="0.01" name="monto" id="monto" placeholder="0" required />
          </div>
          <button type="submit" class="btn-gradiente" style="width:100%;">Aportar 🌱</button>
        </form>
        @if ($errors->any())
          <x-alerta tipo="error">
            @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
          </x-alerta>
        @endif
      @endif
    </x-card>
  </div>

  <div style="margin-top:16px;">
    <a href="{{ route('metas.index') }}" class="btn-secundario">← Volver a metas</a>
    <form action="{{ route('metas.destroy', $meta) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar esta meta?');">
      @csrf @method('DELETE')
      <button type="submit" class="btn-peligro">Eliminar meta</button>
    </form>
  </div>
@endsection
