<?php
include "..\db\db_connect.php";
session_start();
if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] == 'admin') {
        // Redirigir al panel de admin
        header('Location: panel_admin.php');
        exit();
    }
}

// Comprobar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregamos valores de formulario a variables
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Realizamos conexión a db
    $cnx = db_connect();
    $sql = "SELECT * FROM admin WHERE username = '$usuario'";
    $res = mysqli_query($cnx, $sql) or die();
    mysqli_close($cnx);
    //Comprobamos si hay resultados
    if (mysqli_num_rows($res) > 0) {
        // Si hay, comprobamos credenciales
        $authData = mysqli_fetch_assoc($res);
        $usuarioDB = $authData['username'];
        $pwDB = $authData['password'];
        if ($usuario == $usuarioDB && password_verify($password, $pwDB)) {
            $_SESSION['tipo_usuario'] = 'admin';
            $_SESSION['usuario'] = $usuario;
            header('Location: panel_admin.php');
            exit();
        } else {
            $mensaje_error = 'Credenciales inválidas. Inténtalo de nuevo.';
        }
    } else {
        $mensaje_error = "El usuario introducido no existe";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración Bolsa de Empleo Alcántara</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="text-center text-primary mt-4 mb-4">Panel de administración Bolsa de Empleo Alcántara</h1>
        <?php if (isset($mensaje_error)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $mensaje_error; ?>
            </div>
        <?php } ?>
        <form method="post">
            <div class="form-group row">
                <label for="usuario" class="col-sm-2 col-form-label">Nombre de usuario:</label>
                <div class="col-sm-10">
                    <input type="text" id="usuario" name="usuario" class="form-control" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-sm-2 col-form-label">Contraseña:</label>
                <div class="col-sm-10">
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </div>
        </form>
        <p style="color: green;">Si es un usuario administrador y desea una cuenta, por favor solicítela en el servicio
            de la aplicación</p>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='../index.php'">Ir a inicio de
            sesión usuarios</button>
    </div>
</body>

</html>