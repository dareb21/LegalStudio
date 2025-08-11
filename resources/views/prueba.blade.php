<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Subir Archivo</title>
</head>
<body>
    <h1>Subir archivo</h1>

    <form action="/uploadDoc/7" method="POST" enctype="multipart/form-data">
        <!-- Token CSRF de Laravel -->
       @csrf
        <input type="file" name="file" required>
        <br><br>
        <input type="hidden" name="important" value=2>
        <button type="submit">Subir</button>
    </form>
</body>
</html>
