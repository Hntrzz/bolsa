<?php
include "..\db\db_connect.php";
session_start();
if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        // Redirigir al panel de alumno
        header('Location: ../paneles/panel_alu.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] == 'empresa') {
        // Redirigir al panel de empresa
        header('Location: ../paneles/panel_empresa.php');
        exit();
    }
}

// Comprobar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregamso valores de formulario a variables
    $dni = trim($_POST['dni'], ' ');
    $password = $_POST['password'];

    // Realizamos conexión a db
    $cnx = db_connect();
    $sql = "SELECT * FROM usuarios WHERE documento = '$dni'";
    $res = mysqli_query($cnx, $sql) or die();
    mysqli_close($cnx);
    //Comprobamos si hay resultados
    if (mysqli_num_rows($res) > 0) {
        // Si hay, comprobamos credenciales
        $authData = mysqli_fetch_array($res);
        $dniDB = $authData['documento'];
        $pwDB = $authData['password'];
        if ($dni === $dniDB && password_verify($password, $pwDB)) {
            $_SESSION['tipo_usuario'] = 'alumno';
            $_SESSION['dni'] = $dni;
            header('Location: ../paneles/panel_alu.php');
            exit();
        } else {
            $mensaje_error = 'Credenciales inválidas. Inténtalo de nuevo.';
        }
    } else {
        $mensaje_error = "El DNI introducido no existe";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login alumno</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center text-primary">BOLSA DE EMPLEO IES ALCÁNTARA</h1>
        <h2 class="text-center text-secondary">Login alumno</h2>
        <?php if (isset($mensaje_error)) { ?>
            <p class="text-center text-danger"><?php echo $mensaje_error; ?></p>
        <?php } ?>
        <form method="post" class="mt-4">
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" class="form-control" id="dni" name="dni" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="window.location.href='crear_cuenta_alu.php'">Crear
                cuenta</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">Volver</button>
        </div>
    </div>
</body>

</html>