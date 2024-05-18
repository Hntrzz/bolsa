<?php
include "..\db\db_connect.php";
session_start();
if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        // Redirigir al panel de alumno
        header('Location: panel_alumno.php');
        exit();
    } elseif ($_SESSION['tipo_usuario'] == 'empresa') {
        // Redirigir al panel de empresa
        header('Location: panel_empresa.php');
        exit();
    }
}

// Comprobar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregamos valores de formulario a variables
    $cif = trim($_POST['cif'], ' ');
    $password = $_POST['password'];

    // Realizamos conexión a db
    $cnx = db_connect();
    $sql = "SELECT * FROM usuarios WHERE documento = '$cif'";
    $res = mysqli_query($cnx, $sql) or die();
    mysqli_close($cnx);
    // Comprobamos si hay resultados
    if (mysqli_num_rows($res) > 0) {
        // Si hay, comprobamos credenciales
        $authData = mysqli_fetch_array($res);
        $cifDB = $authData['documento'];
        $pwDB = $authData['password'];

        if ($cif == $cifDB && password_verify($password, $pwDB)) {
            $_SESSION['tipo_usuario'] = 'empresa';
            $_SESSION['cif'] = $cif;
            header('Location: ../paneles/panel_empresa.php');
            exit();
        } else {
            $mensaje_error = 'Credenciales inválidas. Inténtalo de nuevo.';
        }
    } else {
        $mensaje_error = "El CIF introducido no existe";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Empresa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h1 class="text-center text-primary">BOLSA DE EMPLEO IES ALCÁNTARA</h1>
        <h2 class="text-center text-secondary">Login empresa</h2>
        <?php if (isset($mensaje_error)) { ?>
            <p style="color: red;" class="text-center"><?php echo $mensaje_error; ?></p>
        <?php } ?>
        <form method="post">
            <div class="form-group">
                <label for="cif">CIF:</label>
                <input type="text" id="cif" name="cif" class="form-control" style="width: 100%;" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" style="width: 100%;" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="window.location.href='crear_cuenta_emp.php'">Crear
                cuenta</button>
            <button class="btn btn-secondary" onclick="window.location.href='../index.php'">Volver</button>
        </div>
    </div>


</body>

</html>