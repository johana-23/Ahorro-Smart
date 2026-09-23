@extends('layouts.app')

@section('title', 'Ingresos - Smart Ahorro')
@section('page-title', 'Ingresos')
@section('page-subtitle', 'Lleva el control de tus entradas de dinero.')

@section('content')
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
@endsection
