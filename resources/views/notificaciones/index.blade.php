@extends('layouts.app')

@section('title', 'Notificaciones - Smart Ahorro')
@section('page-title', 'Notificaciones')
@section('page-subtitle', 'Tus recordatorios y alertas.')

@section('content')
  <x-card>
    @if ($notificaciones->isEmpty())
      <p style="color:var(--texto-secundario); margin:0;">No tienes notificaciones todavía.</p>
    @else
      @foreach ($notificaciones as $notificacion)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--borde); {{ $notificacion->estado === 'no_leida' ? 'font-weight:600;' : 'opacity:0.65;' }}">
          <div>
            <p style="margin:0 0 4px 0;">{{ $notificacion->mensaje }}</p>
            <small style="color:var(--texto-secundario);">{{ $notificacion->fecha_envio->diffForHumans() }}</small>
          </div>
          @if ($notificacion->estado === 'no_leida')
            <form action="{{ route('notificaciones.leida', $notificacion) }}" method="POST">
              @csrf
              <button type="submit" class="btn-secundario" style="font-size:12px; padding:6px 12px;">Marcar leída</button>
            </form>
          @endif
        </div>
      @endforeach
      <div style="margin-top:16px;">{{ $notificaciones->links() }}</div>
    @endif
  </x-card>
@endsection
