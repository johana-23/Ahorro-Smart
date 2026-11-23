@extends('layouts.app')

@section('title', 'Nuevo gasto - Smart Ahorro')
@section('page-title', 'Nuevo gasto')
@section('page-subtitle', 'Registra un egreso y clasifícalo.')

@section('content')
  <x-card>
    <form method="POST" action="{{ route('gastos.store') }}">
      @csrf

      <div class="campo">
        <label for="monto_objetivo">Monto objetivo</label>
        <div class="currency-field">
          <span class="currency-prefix">$ COP</span>
          <input type="text" inputmode="decimal" data-currency-input name="monto_objetivo" id="monto_objetivo" value="{{ old('monto_objetivo') }}" placeholder="0" required />
        </div>
      </div>

      <div class="campo">
        <label for="fecha_gasto">Fecha</label>
        <input type="date" name="fecha_gasto" id="fecha_gasto" value="{{ old('fecha_gasto', now()->format('Y-m-d')) }}" required />
      </div>

      <div class="campo">
        <label for="descripcion">Descripción (opcional)</label>
        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion') }}" placeholder="Ej. Arriendo, mercado..." />
      </div>

      <div class="campo">
        <label>Tipo de gasto</label>
        <small>Fijo: recurrente y predecible (arriendo, servicios). Variable: no planeado o cambia cada mes.</small>
        <select name="tipo_gasto" required>
          <option value="">Selecciona...</option>
          <option value="fijo" {{ old('tipo_gasto') === 'fijo' ? 'selected' : '' }}>Fijo</option>
          <option value="variable" {{ old('tipo_gasto') === 'variable' ? 'selected' : '' }}>Variable</option>
        </select>
      </div>

      <div class="campo">
        <label for="categoria_idcategoria">Categoría</label>
        <select name="categoria_idcategoria" id="categoria_idcategoria">
          <option value="">Sin categoría</option>
          @foreach ($categorias as $categoria)
            <option value="{{ $categoria->idcategoria }}" {{ old('categoria_idcategoria') == $categoria->idcategoria ? 'selected' : '' }}>
              {{ $categoria->nombre_categoria }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="campo">
        <label for="categoria_nueva">O crea una categoría nueva (opcional)</label>
        <small>Si escribes algo aquí, se usará esta en vez de la seleccionada arriba.</small>
        <input type="text" name="categoria_nueva" id="categoria_nueva" placeholder="Ej. Transporte" />
      </div>

      @if ($errors->any())
        <x-alerta tipo="error">
          @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
        </x-alerta>
      @endif

      <button type="submit" class="btn-primario">Guardar gasto</button>
      <a href="{{ route('gastos.index') }}" class="btn-secundario">Cancelar</a>
    </form>
  </x-card>
@endsection
