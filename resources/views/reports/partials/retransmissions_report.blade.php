@if ($retransmissions->isEmpty())
    <div class="alert alert-info">No se encontraron retransmisiones en este rango.</div>
@else
    <div class="alert alert-warning">Esta vista rápida muestra como máximo los 500 registros más recientes.</div>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th><th>Fecha</th><th>Placa</th><th>Estado</th><th>Mensaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($retransmissions as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ optional($item->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $item->plate ?: '-' }}</td>
                        <td>{{ $item->response_status ?: 'SIN ESTADO' }}</td>
                        <td>{{ $item->response_message ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
