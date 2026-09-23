@extends('layouts.app')

@section('title', 'Editar ingreso - Smart Ahorro')
@section('page-title', 'Editar ingreso')
@section('page-subtitle', 'Actualiza los datos de este ingreso.')

@section('content')
  <x-card>
    <form method="POST" action="{{ route('ingresos.update', $ingreso) }}">
      @csrf @method('PUT')

      <div class="campo">
        <label for="monto">Monto</label>
        <input type="number" step="0.01" min="0.01" name="monto" id="monto" value="{{ old('monto', $ingreso->monto) }}" required />
      </div>

      <div class="campo">
        <label for="fecha_ingreso">Fecha</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ old('fecha_ingreso', $ingreso->fecha_ingreso->format('Y-m-d')) }}" required />
      </div>

      <div class="campo">
        <label for="descripcion">Descripción (opcional)</label>
        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $ingreso->descripcion) }}" />
      </div>

      @if ($errors->any())
        <x-alerta tipo="error">
          @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
        </x-alerta>
      @endif

      <button type="submit" class="btn-primario">Guardar cambios</button>
      <a href="{{ route('ingresos.index') }}" class="btn-secundario">Cancelar</a>
    </form>
  </x-card>
@endsection
