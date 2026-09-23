@extends('layouts.app')

@section('title', 'Nueva Meta - Smart Ahorro')
@section('page-title', 'Nueva meta de ahorro')
@section('page-subtitle', 'Define tu objetivo y el plazo para alcanzarlo.')

@section('content')
  <x-card>
    {{-- enctype requerido para poder subir archivos --}}
    <form method="POST" action="{{ route('metas.store') }}" enctype="multipart/form-data">
      @csrf

      <div class="campo">
        <label for="nombre_meta">Nombre de la meta</label>
        <input type="text" name="nombre_meta" id="nombre_meta" value="{{ old('nombre_meta') }}" placeholder="Ej. Viaje a San Andrés" required />
      </div>

      <div class="campo">
        <label for="monto_objetivo">Monto objetivo</label>
        <input type="number" step="0.01" min="1" name="monto_objetivo" id="monto_objetivo" value="{{ old('monto_objetivo') }}" required />
      </div>

      <div class="campo">
        <label for="plazo_meses">Plazo (en meses)</label>
        <small>El sistema calculará automáticamente cuánto debes ahorrar cada mes.</small>
        <input type="number" min="1" max="120" name="plazo_meses" id="plazo_meses" value="{{ old('plazo_meses') }}" required />
      </div>

      <div class="campo">
        <label for="imagen">Imagen motivacional (opcional)</label>
        <small>Sube una foto personal que te recuerde por qué estás ahorrando. JPG, PNG o WEBP, máx. 2MB.</small>
        <input type="file" name="imagen" id="imagen" accept="image/*" />
      </div>

      @if ($errors->any())
        <x-alerta tipo="error">
          @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
        </x-alerta>
      @endif

      <button type="submit" class="btn-gradiente">Crear meta</button>
      <a href="{{ route('metas.index') }}" class="btn-secundario">Cancelar</a>
    </form>
  </x-card>
@endsection
