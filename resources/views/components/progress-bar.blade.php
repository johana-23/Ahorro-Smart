{{--
  Uso: <x-progress-bar :porcentaje="$meta->progreso" />
--}}
@props(['porcentaje' => 0])
<div class="barra-progreso">
  <div class="barra-progreso-relleno" style="width: {{ min(100, max(0, $porcentaje)) }}%;"></div>
</div>
