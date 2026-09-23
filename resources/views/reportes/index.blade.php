@extends('layouts.app')

@section('title', 'Reportes - Smart Ahorro')
@section('page-title', 'Reportes')
@section('page-subtitle', 'Visualiza tus ingresos, gastos y hábitos de consumo.')

@section('extra-css')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endsection

@section('content')

  <x-card titulo="Ingresos vs. Gastos (últimos 6 meses)">
    <canvas id="graficaMeses" height="90"></canvas>
  </x-card>

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:16px;">
    <x-card titulo="Gasto por categoría">
      @if ($gastoPorCategoria->isEmpty())
        <p style="color:var(--texto-secundario); margin:0;">Registra gastos para ver esta gráfica.</p>
      @else
        <canvas id="graficaCategorias" height="220"></canvas>
      @endif
    </x-card>

    <x-card titulo="Gasto fijo vs. variable">
      <canvas id="graficaTipoGasto" height="220"></canvas>
    </x-card>
  </div>

  <script>
    const colorTexto = '#64748B';
    const colorGrid = '#E2E8F0';

    // ---- Gráfica 1: Ingresos vs Gastos por mes ----
    new Chart(document.getElementById('graficaMeses'), {
      type: 'bar',
      data: {
        labels: @json($labels),
        datasets: [
          { label: 'Ingresos', data: @json($ingresosPorMes), backgroundColor: '#00D084' },
          { label: 'Gastos', data: @json($gastosPorMes), backgroundColor: '#F87171' },
        ],
      },
      options: {
        responsive: true,
        scales: {
          x: { ticks: { color: colorTexto }, grid: { color: colorGrid } },
          y: { ticks: { color: colorTexto }, grid: { color: colorGrid } },
        },
        plugins: { legend: { labels: { color: colorTexto } } },
      },
    });

    // ---- Gráfica 2: Gasto por categoría ----
    @if ($gastoPorCategoria->isNotEmpty())
    new Chart(document.getElementById('graficaCategorias'), {
      type: 'pie',
      data: {
        labels: @json($gastoPorCategoria->pluck('categoria')),
        datasets: [{
          data: @json($gastoPorCategoria->pluck('total')),
          backgroundColor: ['#00D084', '#6366F1', '#FBBF24', '#F87171', '#F97316', '#0EA5E9', '#A855F7', '#14B8A6'],
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { color: colorTexto } } },
      },
    });
    @endif

    // ---- Gráfica 3: Gasto fijo vs variable ----
    new Chart(document.getElementById('graficaTipoGasto'), {
      type: 'doughnut',
      data: {
        labels: ['Fijo', 'Variable'],
        datasets: [{
          data: [{{ $gastoFijoVsVariable['fijo'] }}, {{ $gastoFijoVsVariable['variable'] }}],
          backgroundColor: ['#6366F1', '#F97316'],
        }],
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { color: colorTexto } } },
      },
    });
  </script>
@endsection
