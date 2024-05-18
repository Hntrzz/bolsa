<?php
session_start();

// Comprobar si el usuario ya ha iniciado sesión y si es del tipo 'alumno'
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'alumno') {
    // Si el tipo de usuario es 'empresa', redirigir al panel de empresa
    if ($_SESSION['tipo_usuario'] == 'empresa') {
        header('Location: panel_empresa.php');
        exit();
    }
    // Si no hay sesión o el tipo de usuario no es 'alumno', redirigir al login
    header('Location: ../index.php');
    exit();
}

// Conexión a la base de datos
require_once '../db/db_connect.php';
$idcnx = db_connect();

// Consulta a la base de datos para obtener los datos del alumno
$queryAlumno = "SELECT * FROM alumno WHERE dni = '" . $_SESSION['dni'] . "'";
$resultAlumno = mysqli_query($idcnx, $queryAlumno);
$alumno = mysqli_fetch_assoc($resultAlumno);

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $dni = $_SESSION['dni']; // El DNI viene de la sesión, el usuario no puede cambiarlo
    $nombre = $alumno['nombre']; // Viene de la base de datos, no alterable
    $apellidos = $alumno['apellidos']; // Viene de la base de datos, no alterable
    $direccion = $alumno['direccion']; // Viene de la base de datos, no alterable
    $email = $alumno['email']; // Viene de la base de datos, no alterable
    $telefono = $alumno['tlf']; // Viene de la base de datos, no alterable
    $habilidades = $_POST['habilidades']; // Alterable por el usuario
    $observaciones = $_POST['observaciones']; // Alterable por el usuario

    // Insertar los datos en la tabla 'demanda_emp'
    $queryInsert = "INSERT INTO demanda_emp (DNI, nombre, apellidos, direccion, email, tlf_contacto, habilidades_ofertadas, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($idcnx, $queryInsert);
    mysqli_stmt_bind_param($stmt, 'sssssiss', $dni, $nombre, $apellidos, $direccion, $email, $telefono, $habilidades, $observaciones);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo <<<HTML
        <h3>Demanda ingresada correctamente</h3>
    HTML;
}

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Demanda de Empleo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center text-primary mt-4 mb-4">Crear Demanda de Empleo</h1>
        <form action="crear_demanda.php" method="post">
            <div class="form-group row">
                <label for="dni" class="col-sm-2 col-form-label">DNI:</label>
                <div class="col-sm-10">
                    <input type="text" name="dni" value="<?php echo $alumno['dni']; ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="nombre" class="col-sm-2 col-form-label">Nombre:</label>
                <div class="col-sm-10">
                    <input type="text" name="nombre" value="<?php echo $alumno['nombre']; ?>" class="form-control"
                        readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="apellidos" class="col-sm-2 col-form-label">Apellidos:</label>
                <div class="col-sm-10">
                    <input type="text" name="apellidos" value="<?php echo $alumno['apellidos']; ?>" class="form-control"
                        readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="direccion" class="col-sm-2 col-form-label">Dirección:</label>
                <div class="col-sm-10">
                    <input type="text" name="direccion" value="<?php echo $alumno['direccion']; ?>" class="form-control"
                        readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email:</label>
                <div class="col-sm-10">
                    <input type="text" name="email" value="<?php echo $alumno['email']; ?>" class="form-control"
                        readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="telefono" class="col-sm-2 col-form-label">Número de teléfono:</label>
                <div class="col-sm-10">
                    <input type="text" name="tlf" value="<?php echo $alumno['tlf']; ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="habilidades" class="col-sm-2 col-form-label">Habilidades ofertadas:</label>
                <div class="col-sm-10">
                    <textarea name="habilidades" maxlength="300" class="form-control" required></textarea>
                </div>
            </div>
            <div class="form-group row">
                <label for="observaciones" class="col-sm-2 col-form-label">Observaciones:</label>
                <div class="col-sm-10">
                    <textarea name="observaciones" maxlength="300" class="form-control" required></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" class="btn btn-primary" value="Enviar Demanda">
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location.href='panel_alu.php'">Volver</button>
                </div>
            </div>
        </form>
    </div>

</body>

</html>