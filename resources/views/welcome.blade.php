<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Retransmisión OCSA</title>

    <!-- Incluir Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .section {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        img{
            height: 200px;
        }
    </style>
</head>

<body class="antialiased">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="section">
                    <div class="section-title">
                        GPS DATE
                    </div>
                    <div>
                        <div class="row">
                            <div class="col-md-6">
                                <p>{{ $Date }}</p>
                            </div>
                            <div class="col-md-6">
                                <!-- Espacio en blanco para la imagen -->
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
            <div class="col-md-9">
                <div class="header">
                    <img src="https://ocsa.dmautomotriz.com/image/banner.jpg" alt="banner ocsa" class="img-fluid rounded">
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">
                Respuesta PLATIN - OSINERGMIN
            </div>
            <div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Plate</th>
                            <th>Event</th>
                            <th>Speed</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Altitude</th>
                            <th>GPS Date</th>
                            <th>Odometer</th>
                            <th>UUID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resu as $result)
                            <tr>
                                <td>
                                    <span class="badge @if($result['status'] === 'SUCCESS') badge-success @else badge-danger @endif">{{ $result['status'] }}</span>
                                </td>
                                @if ($result['status'] === 'SUCCESS')
                                    <!-- Si el status es SUCCESS, mostramos la información de la unidad directamente -->
                                    <td>{{ $result['unit']['plate'] }}</td>
                                    <td>{{ $result['unit']['event'] }}</td>
                                    <td>{{ $result['unit']['speed'] }}</td>
                                    <td>{{ $result['unit']['position']['latitude'] }}</td>
                                    <td>{{ $result['unit']['position']['longitude'] }}</td>
                                    <td>{{ $result['unit']['position']['altitude'] }}</td>
                                    <td>{{ $result['unit']['gpsDate'] }}</td>
                                    <td>{{ $result['unit']['odometer'] }}</td>
                                    <td>{{ $result['unit']['uuid'] ?? 'sin dato recibido' }}</td>
                                @else
                                    <!-- Si el status es ERROR, mostramos el mensaje de error -->
                                    <td colspan="9">Error: {{ $result['error_message'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="footer">
            <hr>
            <div class="text-center text-sm text-gray-500">
                Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            </div>
        </div>
    </div>
</body>

</html>
