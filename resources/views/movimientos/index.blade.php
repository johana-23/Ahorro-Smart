@extends('layouts.app')

@section('title', 'Movimientos - Smart Ahorro')
@section('page-title', 'Movimientos')
@section('page-subtitle', 'Administra tus ingresos y gastos desde un mismo lugar.')

@section('content')
  <div class="movimientos-tabs" role="tablist" aria-label="Secciones de movimientos">
    <a href="{{ route('movimientos.index', ['tab' => 'ingresos']) }}"
       role="tab"
       aria-selected="{{ $tab === 'ingresos' ? 'true' : 'false' }}"
       class="movimientos-tab {{ $tab === 'ingresos' ? 'active' : '' }}">
      <i class="fa-solid fa-circle-arrow-up"></i> Ingresos
    </a>
    <a href="{{ route('movimientos.index', ['tab' => 'gastos']) }}"
       role="tab"
       aria-selected="{{ $tab === 'gastos' ? 'true' : 'false' }}"
       class="movimientos-tab {{ $tab === 'gastos' ? 'active' : '' }}">
      <i class="fa-solid fa-circle-arrow-down"></i> Gastos
    </a>
  </div>

  @if ($tab === 'ingresos')
    <x-card titulo="Historial de ingresos">
      <x-slot:acciones>
        <a href="{{ route('ingresos.create') }}" class="btn-primario"><i class="fa-solid fa-plus"></i> Nuevo ingreso</a>
      </x-slot:acciones>

      @if ($ingresos->isEmpty())
        <p style="color:var(--texto-secundario);">Todavía no has registrado ningún ingreso.</p>
      @else
        <table class="tabla-lista">
          <thead>
            <tr><th>Fecha</th><th>Descripción</th><th>Monto</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($ingresos as $ingreso)
              <tr>
                <td>{{ $ingreso->fecha_ingreso->format('d/m/Y') }}</td>
                <td>{{ $ingreso->descripcion ?: '—' }}</td>
                <td style="color: var(--semaforo-verde); font-weight:600;">+ ${{ number_format($ingreso->monto, 0, ',', '.') }}</td>
                <td style="text-align:right; white-space:nowrap;">
                  <a href="{{ route('ingresos.edit', $ingreso) }}" title="Editar"><i class="fa-regular fa-pen-to-square"></i></a>
                  &nbsp;
                  <form action="{{ route('ingresos.destroy', $ingreso) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este ingreso?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="border:none;background:none;cursor:pointer;color:var(--semaforo-rojo);" title="Eliminar"><i class="fa-regular fa-trash-can"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div style="margin-top:16px;">{{ $ingresos->links() }}</div>
      @endif
    </x-card>
  @else
    <x-card titulo="Historial de gastos">
      <x-slot:acciones>
        <a href="{{ route('gastos.create') }}" class="btn-primario"><i class="fa-solid fa-plus"></i> Nuevo gasto</a>
      </x-slot:acciones>

      @if ($gastos->isEmpty())
        <p style="color:var(--texto-secundario);">Todavía no has registrado ningún gasto.</p>
      @else
        <table class="tabla-lista">
          <thead>
            <tr><th>Fecha</th><th>Descripción</th><th>Categoría</th><th>Tipo</th><th>Monto</th><th></th></tr>
          </thead>
          <tbody>
            @foreach ($gastos as $gasto)
              <tr>
                <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                <td>{{ $gasto->descripcion ?: '—' }}</td>
                <td>{{ $gasto->categoria->nombre_categoria ?? '—' }}</td>
                <td><x-badge :tipo="$gasto->tipo_gasto">{{ ucfirst($gasto->tipo_gasto) }}</x-badge></td>
                <td style="color: var(--semaforo-rojo); font-weight:600;">- ${{ number_format($gasto->monto, 0, ',', '.') }}</td>
                <td style="text-align:right; white-space:nowrap;">
                  <a href="{{ route('gastos.edit', $gasto) }}" title="Editar"><i class="fa-regular fa-pen-to-square"></i></a>
                  &nbsp;
                  <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar este gasto?');">
                    @csrf @method('DELETE')
                    <button type="submit" style="border:none;background:none;cursor:pointer;color:var(--semaforo-rojo);" title="Eliminar"><i class="fa-regular fa-trash-can"></i></button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div style="margin-top:16px;">{{ $gastos->links() }}</div>
      @endif
    </x-card>
  @endif
@endsection