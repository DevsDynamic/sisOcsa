@extends('adminlte::page')
@section('title','Dashboard')
@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center">
 <div><h1 class="mb-1">Hola, {{ auth()->user()->person?->full_name ?? auth()->user()->username }}</h1><p class="text-muted mb-0">Estado general de la retransmisión GPS</p></div>
 <span class="environment-pill {{ $environment==='production'?'production':'demo' }}"><i class="fas fa-circle mr-1"></i>{{ $environment==='production'?'PRODUCCIÓN':'DEMO / CERTIFICACIÓN' }}</span>
</div>
@stop
@section('content')
<div class="row">
 @foreach([
  ['value'=>$clients,'label'=>'Clientes registrados','detail'=>$activeClients.' activos · '.$inactiveClients.' inactivos','icon'=>'fa-users','color'=>'primary','url'=>route('people.index')],
  ['value'=>$gpsSources,'label'=>'Fuentes GPS activas','icon'=>'fa-satellite-dish','color'=>'info','url'=>route('osinergmins.index')],
  ['value'=>$today,'label'=>'Envíos de hoy','icon'=>'fa-paper-plane','color'=>'success','url'=>$todayReportUrl],
  ['value'=>$errorsToday,'label'=>'Errores de hoy','icon'=>'fa-exclamation-triangle','color'=>'danger','url'=>$todayErrorsUrl]
 ] as $card)
 <div class="col-xl-3 col-md-6"><a href="{{$card['url']}}" class="metric-card"><div class="metric-icon bg-{{$card['color']}}"><i class="fas {{$card['icon']}}"></i></div><div><strong>{{$card['value']}}</strong><span>{{$card['label']}}</span>@isset($card['detail'])<small>{{$card['detail']}}</small>@endisset</div><i class="fas fa-chevron-right ml-auto text-muted"></i></a></div>
 @endforeach
</div>
<div class="row">
 <div class="col-lg-8"><div class="card modern-card"><div class="card-header border-0"><h3 class="card-title font-weight-bold">Actividad de los últimos 7 días</h3></div><div class="card-body"><canvas id="activityChart" height="110"></canvas></div></div></div>
 <div class="col-lg-4">
  <div class="card modern-card"><div class="card-header border-0"><h3 class="card-title font-weight-bold">Estado operativo</h3></div><div class="card-body">
   <div class="status-row"><span>Ambiente</span><b>{{$environment==='production'?'Producción':'Demo'}}</b></div>
   <div class="status-row"><span>Último envío</span><b>{{$lastTransmission?->created_at?->format('d/m/Y H:i:s') ?? 'Sin registros'}}</b></div>
   <div class="status-row"><span>Último estado</span><span class="badge badge-{{$lastTransmission?->response_status==='SUCCESS'?'success':'secondary'}}">{{$lastTransmission?->response_status ?? 'PENDIENTE'}}</span></div>
  </div></div>
  @if(auth()->user()->is_system_owner)<a href="{{route('system-settings.edit')}}" class="btn btn-dark btn-block py-3"><i class="fas fa-cog mr-2"></i>Administrar integración</a>@endif
 </div>
</div>
@stop
@section('css')<style>
.environment-pill{padding:9px 14px;border-radius:30px;font-size:.78rem;font-weight:800;letter-spacing:.05em}.environment-pill.demo{background:#fff3cd;color:#856404}.environment-pill.production{background:#d4edda;color:#155724}.metric-card{display:flex;align-items:center;background:#fff;color:#263238;padding:20px;border-radius:14px;margin-bottom:20px;box-shadow:0 5px 20px rgba(30,55,90,.07);transition:.2s}.metric-card:hover{transform:translateY(-3px);color:#1266f1;box-shadow:0 10px 28px rgba(30,55,90,.13)}.metric-icon{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;color:#fff;font-size:20px;margin-right:14px}.metric-card strong{font-size:26px;display:block;line-height:1}.metric-card span{font-size:13px;color:#6c757d}.modern-card{border:0;border-radius:14px;box-shadow:0 5px 20px rgba(30,55,90,.07)}.status-row{display:flex;justify-content:space-between;padding:14px 0;border-bottom:1px solid #edf1f5}.status-row:last-child{border:0}
.metric-card small{display:block;margin-top:3px;color:#98a1ab;font-size:11px}
</style>@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>new Chart(document.getElementById('activityChart'),{type:'bar',data:{labels:@json($daily->pluck('day')),datasets:[{label:'Total',data:@json($daily->pluck('total')),backgroundColor:'#1266f1',borderRadius:6},{label:'Exitosos',data:@json($daily->pluck('success')),backgroundColor:'#2ecc71',borderRadius:6}]},options:{responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});</script>
@stop
