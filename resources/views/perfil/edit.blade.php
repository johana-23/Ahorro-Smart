@extends('layouts.app')

@section('title', 'Mi Perfil - Smart Ahorro')
@section('page-title', 'Mi Perfil')
@section('page-subtitle', 'Administra tu información personal y financiera.')

@section('content')
  <div style="display:grid; grid-template-columns: 1fr 2fr; gap:20px;">

    <div>
      <x-card>
        <div style="text-align:center;">
          <i class="fa-solid fa-circle-user" style="font-size:64px; color:var(--primario);"></i>
          <h3 style="margin:12px 0 4px 0;">{{ Auth::user()->name }}</h3>
          <p style="font-size:13px; color:var(--texto-secundario); margin:0;">
            Organiza tus finanzas hoy para cumplir tus sueños mañana.
          </p>
        </div>
      </x-card>

      @if ($perfil)
        <div style="margin-top:16px;">
          <x-card>
            <p style="margin:0; font-size:13px; color:var(--texto-secundario);">Dinero de bolsillo</p>
            <p style="margin:6px 0 0 0; font-size:20px; font-weight:700;">
              ${{ number_format($perfil->dinero_bolsillo, 0, ',', '.') }}
            </p>

              {{-- se calcula apartir de los : ingresos aproximados − gastos mensuales --}}

          </x-card>
        </div>

        <div style="margin-top:16px;">
          <x-card>
            <p style="margin:0; font-size:13px; color:var(--texto-secundario);">Saldo actual</p>
            <p
              style="margin:6px 0 0 0; font-size:26px; font-weight:700; color: {{ $perfil->saldo_actual >= 0 ? 'var(--semaforo-verde)' : 'var(--semaforo-rojo)' }};">
              ${{ number_format($perfil->saldo_actual, 0, ',', '.') }}
            </p>

              {{-- se calcula apartir del Dinero de bolsillo + ingresos − gastos registrados --}}

          </x-card>
        </div>
      @endif
    </div>

    <x-card titulo="Información financiera">
      <form method="POST" action="{{ route('perfil.update') }}">
        @csrf

        <div class="campo">
          <label for="ingresos_aproximados">Ingresos aproximados</label>
          <small>Total que recibes mensualmente</small>
          <input type="number" step="0.01" min="0" placeholder="0" name="ingresos_aproximados" id="ingresos_aproximados"
            value="{{ old('ingresos_aproximados', $perfil->ingresos_aproximados ?? '') }}" />
        </div>

        <div class="campo">
          <label for="gastos_mensuales">Gastos mensuales aproximados</label>
          <small>Total de gastos mensuales</small>
          <input type="number" step="0.01" min="0" placeholder="0" name="gastos_mensuales" id="gastos_mensuales"
            value="{{ old('gastos_mensuales', $perfil->gastos_mensuales ?? '') }}" />
        </div>


        @if ($errors->any())
          <x-alerta tipo="error">
            @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
          </x-alerta>
        @endif

        <button type="submit" class="btn-primario">Guardar cambios</button>
      </form>
    </x-card>

  </div>
@endsection
