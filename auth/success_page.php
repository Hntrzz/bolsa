<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        header('Location: ../paneles/panel_alu.php');
    } elseif ($_SESSION['tipo_usuario'] == 'empresa') {
        header('Location: ../paneles/panel_empresa.php');
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta creada</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="text-center">
            <h1 class="text-primary">BOLSA DE EMPLEO IES ALCÁNTARA</h1>
            <p class="lead">¡Felicidades! Tu cuenta ha sido creada satisfactoriamente.</p>
            <form method="POST" enctype="multipart/form-data">
                <button type="submit" class="btn btn-primary">Continuar</button>
            </form>
        </div>
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <img src="../media/fiesta.png" class="img-fluid" alt="Imagen relacionada con empleo">
            </div>
        </div>
    </div>
</body>

</html>