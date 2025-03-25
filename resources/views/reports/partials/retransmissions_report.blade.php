@if($retransmissions->isEmpty())
    <p>No se encontraron retransmisiones en este rango de fechas.</p>
@else
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retransmissions as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
