@extends('layouts.app')

@section('title', 'Editar gasto - Smart Ahorro')
@section('page-title', 'Editar gasto')
@section('page-subtitle', 'Actualiza los datos de este gasto.')

@section('content')
  <x-card>
    <form method="POST" action="{{ route('gastos.update', $gasto) }}">
      @csrf @method('PUT')

      <div class="campo">
        <label for="monto">Monto</label>
        <input type="number" step="0.01" min="0.01" name="monto" id="monto" value="{{ old('monto', $gasto->monto) }}" required />
      </div>

      <div class="campo">
        <label for="fecha_gasto">Fecha</label>
        <input type="date" name="fecha_gasto" id="fecha_gasto" value="{{ old('fecha_gasto', $gasto->fecha_gasto->format('Y-m-d')) }}" required />
      </div>

      <div class="campo">
        <label for="descripcion">Descripción (opcional)</label>
        <input type="text" name="descripcion" id="descripcion" value="{{ old('descripcion', $gasto->descripcion) }}" />
      </div>

      <div class="campo">
        <label>Tipo de gasto</label>
        <select name="tipo_gasto" required>
          <option value="fijo" {{ old('tipo_gasto', $gasto->tipo_gasto) === 'fijo' ? 'selected' : '' }}>Fijo</option>
          <option value="variable" {{ old('tipo_gasto', $gasto->tipo_gasto) === 'variable' ? 'selected' : '' }}>Variable</option>
        </select>
      </div>

      <div class="campo">
        <label for="categoria_idcategoria">Categoría</label>
        <select name="categoria_idcategoria" id="categoria_idcategoria">
          <option value="">Sin categoría</option>
          @foreach ($categorias as $categoria)
            <option value="{{ $categoria->idcategoria }}" {{ old('categoria_idcategoria', $gasto->categoria_idcategoria) == $categoria->idcategoria ? 'selected' : '' }}>
              {{ $categoria->nombre_categoria }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="campo">
        <label for="categoria_nueva">O crea una categoría nueva (opcional)</label>
        <input type="text" name="categoria_nueva" id="categoria_nueva" placeholder="Ej. Transporte" />
      </div>

      @if ($errors->any())
        <x-alerta tipo="error">
          @foreach ($errors->all() as $error) {{ $error }}<br /> @endforeach
        </x-alerta>
      @endif

      <button type="submit" class="btn-primario">Guardar cambios</button>
      <a href="{{ route('gastos.index') }}" class="btn-secundario">Cancelar</a>
    </form>
  </x-card>
@endsection
