{{--
  Uso: <x-badge tipo="fijo">Fijo</x-badge>
  $tipo controla la clase de color: fijo | variable | cumplida | progreso
--}}
@props(['tipo' => 'progreso'])
<span class="badge badge-{{ $tipo }}">{{ $slot }}</span>
