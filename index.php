<?php
session_start();

if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        // Redirigir al panel de alumno
        header('Location: paneles/panel_alu.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] == 'empresa') {
        // Redirigir al panel de empresa
        header('Location: paneles/panel_empresa.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-7">
                <div class="text-center">
                    <h1 class="mb-4 text-primary">BOLSA DE EMPLEO IES ALCÁNTARA</h1>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary mr-2" onclick="window.location.href='auth/login_alu.php'">Entrar como
                        Alumno</button>
                    <button class="btn btn-primary" onclick="window.location.href='auth/login_empresa.php'">Entrar como
                        Empresa</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>