@extends('adminlte::page')

@section('title', 'Reporte - Ventas')

@section('content_header')
  <h1>REPORTES</h1>
@stop

@section('content')

  <div class="card">{{-- small --}}
    <div class="card-body">
      <div class="row">
        <div class="col-xs-12 col-4">
          <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="list-indicator1-list" data-toggle="list" href="#list-indicator1" role="tab" aria-controls="indicator1">OSINERGMIN</a>
            {{-- <a class="list-group-item list-group-item-action" id="list-indicator2-list" data-toggle="list" href="#list-indicator2" role="tab" aria-controls="indicator2">Billetes por fechas</a>
            <a class="list-group-item list-group-item-action" id="list-indicator3-list" data-toggle="list" href="#list-indicator3" role="tab" aria-controls="indicator3">Monedas por fechas</a> --}}
          </div>
        </div>

        <div class="col-xs-12 col-8">
          <br>        
          <div class="tab-content" id="nav-tabContent">
              <div class="tab-pane fade show active" id="list-indicator1" role="tabpanel" aria-labelledby="list-indicator1-list">              
                  <form id="formulario" autocomplete="off">
                      @csrf
                      <p>Retransmisiones de OSINERGMIN</p>
                      <div class="form-row">
                          <div class="form-group col-lg-4">
                              <label for="from">Desde</label>
                              <input id="from" name="from" type="date" class="form-control @error('from') is-invalid @enderror" 
                                     value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                          </div>
                          <div class="form-group col-lg-4">
                              <label for="to">Hasta</label>
                              <input id="to" name="to" type="date" class="form-control @error('to') is-invalid @enderror" 
                                     value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                          </div>
                          <div class="form-group col-lg-4">
                              <button type="button" id="bt_find" class="btn btn-success">
                                  <i class="fas fa-search"></i> Buscar 
                              </button>
                          </div>
                      </div>  
                  </form>
              </div>
          </div>
      </div>
      
      <!-- Contenedor donde se mostrará el reporte -->
      <div id="reportResult"></div>
      

        {{-- <div class="col-xs-12 col-8">  <br>        
          <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="list-indicator1" role="tabpanel" aria-labelledby="list-indicator1-list">              
              <form action = "#1" autocomplete = "off" enctype = "multipart/form-data" id = "formulario" files = "true">
                <p>Retransmisiones de OSINERGMIN</p>
                <div class="form-row">
                  <div class="form-group col-lg-4">
                    <input id="from" type="date" class="form-control @error('from') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-4">
                    <input id="to" type="date" class="form-control @error('to') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-4">
                      <button type="button" id="bt_find" class="btn btn-success"><i class="fas fa-search"></i> Buscar </button>
                  </div>
                </div>  
              </form>
            </div> --}}

            {{-- <div class="tab-pane fade" id="list-indicator2" role="tabpanel" aria-labelledby="list-indicator2-list">
              <form action = "#2" autocomplete = "off" enctype = "multipart/form-data" id = "formulario" files = "true">
                <p>BILLETES: Seleccione el rango de fechas y empresa destino</p>
                <div class="form-row">
                  <div class="form-group col-lg-3">
                    <input id="from" type="date" class="form-control @error('from') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-3">
                    <input id="to" type="date" class="form-control @error('to') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-3">
                    <select name="select" class="form-control">
                      <option value="" selected>--- SELECCIONE ---</option>
                      <option value="1">CBC LOGISTICS</option>
                      <option value="2">CBC MARKET</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-3">
                      <button type="button" id="bt_find" class="btn btn-success"><i class="fas fa-search"></i> Buscar</button>
                  </div>
                </div>
              </form>
            </div>

            <div class="tab-pane fade" id="list-indicator3" role="tabpanel" aria-labelledby="list-indicator3-list">
              <form action = "#3" autocomplete = "off" enctype = "multipart/form-data" id = "formulario" files = "true">
                <p>MONEDAS: Seleccione el rango de fechas y empresa destino</p>
                <div class="form-row">
                  <div class="form-group col-lg-3">
                    <input id="from" type="date" class="form-control @error('from') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-3">
                    <input id="to" type="date" class="form-control @error('to') is-invalid @enderror" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                  </div>
                  <div class="form-group col-lg-3">
                    <select name="select" class="form-control">
                      <option value="" selected>--- SELECCIONE ---</option>
                      <option value="1">CBC LOGISTICS</option>
                      <option value="2">CBC MARKET</option>
                    </select>
                  </div>
                  <div class="form-group col-lg-3">
                      <button type="button" id="bt_find" class="btn btn-success"><i class="fas fa-search"></i> Buscar</button>
                  </div>
                </div>
              </form>
            </div> --}}
          </div>
        </div>
      </div>
    </div>
  </div>

@stop

@section('css')

@stop

@section('js')
  <script>

    const triggerTabList = document.querySelectorAll('#myTab button')
    triggerTabList.forEach(triggerEl => {
      const tabTrigger = new bootstrap.Tab(triggerEl)

      triggerEl.addEventListener('click', event => {
        event.preventDefault()
        tabTrigger.show()
      })
    })

  </script>

  <!-- Script AJAX para enviar los datos -->
<script>
  document.getElementById('bt_find').addEventListener('click', function() {
      let fromDate = document.getElementById('from').value;
      let toDate = document.getElementById('to').value;

      fetch("{{ route('report.retransmissions') }}", {
          method: "GET",
          headers: {
              "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
              "Content-Type": "application/json"
          },
          body: JSON.stringify({ from: fromDate, to: toDate })
      })
      .then(response => response.json())
      .then(data => {
          document.getElementById("reportResult").innerHTML = data.html;
      })
      .catch(error => console.error("Error:", error));
  });
</script>

@stop

