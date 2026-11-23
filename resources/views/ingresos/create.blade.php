@extends('layouts.app')

@section('title', 'Nuevo ingreso - Smart Ahorro')
@section('page-title', 'Nuevo ingreso')
@section('page-subtitle', 'Registra una nueva entrada de dinero.')

@section('content')
  <x-card>
    <form method="POST" action="{{ route('ingresos.store') }}">
      @csrf

      <div class="campo">
        <div class="campo">
        <label for="monto">Monto</label>
        <div class="currency-field">
          <span class="currency-prefix">$ COP</span>
          <input type="text" inputmode="decimal" data-currency-input name="monto" id="monto" value="{{ old('monto') }}" placeholder="0" required />
        </div>
      </div>
      <div class="campo">
        <label for="fecha_ingreso">Fecha</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" value="{{ old('fecha_ingreso', now()->format('Y-m-d')) }}" required />
      </div>

      <div class="campo">
        <label for="descripcion">Descripción (opcional)</label>
        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" placeholder="Ej. Salario, freelance..." />
      </div>

      @if ($errors->any())
        <x-alerta tipo="error">
          @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
        </x-alerta>
      @endif

      <button type="submit" class="btn-primario">Guardar ingreso</button>
      <a href="{{ route('ingresos.index') }}" class="btn-secundario">Cancelar</a>
    </form>
  </x-card>
@endsection
