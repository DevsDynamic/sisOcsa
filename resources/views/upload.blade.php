<!DOCTYPE html>
<html>
<head>
    <title>Subir Imagen</title>
</head>
<body>
    @if(session('success'))
        <div>
            <h3>{{ session('success') }}</h3>
            <img src="{{ Storage::url(session('image')) }}" alt="Imagen subida">
            <p>Metadatos de la imagen:</p>
            <pre>{{ print_r(session('exif'), true) }}</pre>
        </div>
    @endif

    <form action="{{ route('image.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="image">Selecciona una imagen:</label>
        <input type="file" name="image" id="image" required>
        <button type="submit">Subir Imagen</button>
    </form>
</body>
</html>
