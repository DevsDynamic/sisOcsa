@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
  <p>BIENVENID@ <b>{{ strtoupper(auth()->user()->name) }}</b> AL SISTEMA EMPRESARIAL DE <b>OCSA</b></p>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>    

    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawVisualization);
  
        function drawVisualization() {
          // Some raw data (not necessarily accurate)
          var data = google.visualization.arrayToDataTable([
            ['Month', 'Bolivia', 'Ecuador', 'Madagascar', 'Papua New Guinea', 'Rwanda', 'Average'],
            ['2004/05',  165,      938,         522,             998,           450,      614.6],
            ['2005/06',  135,      1120,        599,             1268,          288,      682],
            ['2006/07',  157,      1167,        587,             807,           397,      623],
            ['2007/08',  139,      1110,        615,             968,           215,      609.4],
            ['2008/09',  136,      691,         629,             1026,          366,      569.6]
          ]);
  
          var options = {
            title : 'Monthly Coffee Production by Country',
            vAxis: {title: 'Cups'},
            hAxis: {title: 'Month'},
            seriesType: 'bars',
            series: {5: {type: 'line'}}
          };
  
          var chart = new google.visualization.ComboChart(document.getElementById('indicador1-PRE-TEST'));
          chart.draw(data, options);
        }
    </script>

    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['Work',     11],
          ['Eat',      2],
          ['Commute',  2],
          ['Watch TV', 2],
          ['Sleep',    7]
        ]);

        var options = {
          title: 'Porcentajes'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
      }
    </script>
@stop

@section('content')

  <div class="form-row">
    <div class="container-fluid">
        <div class="row" style="color: #fff">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $co }}</h3>
                <p>Contactos</p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $cp }}</h3>
                <p>Clientes potenciales</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $personToken }}</h3>
                <p>Clientes con retransmisión</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
            <div class="inner">
                <h3>En proceso{{ '' }}</h3>
                <p>Cantidad de alertas en el mes</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        </div>
    </div>
  </div>
  {{-- @include('orden_servicio.modal.alerta') --}}
  <br>
  <br>
  <div class="col-lg-12 col-12" style="text-align: center">
    <img src="{{ asset('image/logo_ocsa.png') }}" alt="Logo" width="30%">
</div>

@stop

@section('css')
<link rel="stylesheet" media="only screen and (max-width: 768px)" href="../css/celulares.css">
  {{-- <style>  
    table, th, td {
      border: 0px solid black;
      border-collapse: collapse;
        
    }
    /* setting the text-align property to center*/
    th, td {
      padding: 5px;
      text-align:center;
            
    }
  </style> --}}

    
@stop

@section('js')
  {{-- <script src="{{ asset('js/datatables.js') }}"></script> --}}

  {{-- @if (!$datos == '')
    <script>
      $('#staticBackdrop').modal('show')
    </script>
  @endif --}}
@stop
