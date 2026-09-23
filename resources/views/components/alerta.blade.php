{{--
  Uso: <x-alerta tipo="exito">Guardado correctamente</x-alerta>
--}}
@props(['tipo' => 'exito'])
<div class="alerta alerta-{{ $tipo }}">{{ $slot }}</div>
