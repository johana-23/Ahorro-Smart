{{--
  Componente <x-card> — demuestra el uso de componentes y slots de Blade.
  Uso:
  <x-card titulo="Mis ingresos">
      contenido aquí...
  </x-card>
--}}
<div class="tarjeta {{ $class ?? '' }}">
  @isset($titulo)
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
      <h3 style="margin:0; font-size:16px;">{{ $titulo }}</h3>
      {{ $acciones ?? '' }} {{-- slot nombrado opcional, ej. un botón "Nuevo" --}}
    </div>
  @endisset

  {{ $slot }} {{-- slot por defecto: el contenido entre <x-card>...</x-card> --}}
</div>
