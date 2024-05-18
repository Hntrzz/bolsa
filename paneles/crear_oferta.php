<?php
session_start();

// Comprobar si el usuario ya ha iniciado sesión y si es del tipo 'empresa'
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'empresa') {
    // Si el tipo de usuario no es 'empresa', redirigir al login
    header('Location: ../index.php');
    exit();
}

// Conexión a la base de datos
require_once '../db/db_connect.php';
$idcnx = db_connect();

// Consulta a la base de datos para obtener los datos de la empresa
$queryEmpresa = "SELECT * FROM empresa WHERE cif = '" . $_SESSION['cif'] . "'";
$resultEmpresa = mysqli_query($idcnx, $queryEmpresa);
$empresa = mysqli_fetch_assoc($resultEmpresa);

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $cif = $_SESSION['cif']; // El CIF viene de la sesión, el usuario no puede cambiarlo
    $denominacion = $empresa['denominacion']; // Viene de la base de datos, no alterable
    $direccion = $empresa['direccion']; // Viene de la base de datos, no alterable
    $email = $empresa['email']; // Viene de la base de datos, no alterable
    $tlf_contacto = $empresa['tlf_contacto']; // Viene de la base de datos, no alterable
    $tipo_trabajo = $_POST['tipo_trabajo']; // Alterable por el usuario
    $num_plazas = $_POST['num_plazas']; // Alterable por el usuario
    $observaciones = $_POST['observaciones']; // Alterable por el usuario

    // Insertar los datos en la tabla 'ofertas_emp'
    $queryInsert = "INSERT INTO ofertas_emp (CIF, denominacion_emp, direccion, email, tlf_contacto, tipo_trabajo, num_plazas, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($idcnx, $queryInsert);
    mysqli_stmt_bind_param($stmt, 'ssssisss', $cif, $denominacion, $direccion, $email, $tlf_contacto, $tipo_trabajo, $num_plazas, $observaciones);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo <<<HTML
        <h3>Oferta ingresada correctamente</h3>
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
    <title>Crear Oferta de Empleo</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container ">
        <h1 class="text-center text-primary mt-4 mb-4">Crear Oferta de Empleo</h1>
        <form action="crear_oferta.php" method="post">
            <div class="form-group row">
                <label for="cif" class="col-sm-2 col-form-label">CIF:</label>
                <div class="col-sm-10">
                    <input type="text" name="cif" value="<?php echo $_SESSION['cif']; ?>" class="form-control" readonly
                        required>
                </div>
            </div>
            <div class="form-group row">
                <label for="denominacion" class="col-sm-2 col-form-label">Denominación:</label>
                <div class="col-sm-10">
                    <input type="text" name="denominacion" value="<?php echo $empresa['denominacion']; ?>"
                        class="form-control" readonly required>
                </div>
            </div>
            <div class="form-group row">
                <label for="direccion" class="col-sm-2 col-form-label">Dirección:</label>
                <div class="col-sm-10">
                    <input type="text" name="direccion" value="<?php echo $empresa['direccion']; ?>"
                        class="form-control" readonly required>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email:</label>
                <div class="col-sm-10">
                    <input type="text" name="email" value="<?php echo $empresa['email']; ?>" class="form-control"
                        readonly required>
                </div>
            </div>
            <div class="form-group row">
                <label for="tlf_contacto" class="col-sm-2 col-form-label">Teléfono de contacto:</label>
                <div class="col-sm-10">
                    <input type="number" name="tlf_contacto" value="<?php echo $empresa['tlf_contacto']; ?>"
                        class="form-control" readonly required>
                </div>
            </div>
            <div class="form-group row">
                <label for="tipo_trabajo" class="col-sm-2 col-form-label">Tipo de Trabajo:</label>
                <div class="col-sm-10">
                    <input type="text" name="tipo_trabajo" class="form-control" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="num_plazas" class="col-sm-2 col-form-label">Número de Plazas:</label>
                <div class="col-sm-10">
                    <input type="number" name="num_plazas" class="form-control" required>
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
                    <input type="submit" class="btn btn-primary" value="Publicar Oferta">
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location.href='panel_empresa.php'">Volver</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>