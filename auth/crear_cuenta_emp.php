<?php
require "../db/db_connect.php";
require "../dni/dni_val.php";
session_start();

// Comprobar si el usuario ya ha iniciado sesión
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

// Validar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = array();

    // Validar campos obligatorios
    $required_fields = array('cif', 'denominacion', 'nombre_gerente', 'nif_gerente', 'direccion', 'email', 'tlf_contacto', 'persona_contacto', 'practicas_pasado', 'participa_practicas', );
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo $field es obligatorio.";
        }
    }

    if (empty($errors)) {

        // Validar CIF
        $cifVal = validateSpanishID($_POST['cif']);
        if ($cifVal['valid'] == false) {
            $errors[] = "CIF no válido";
        }

        // Validar que el NIF del gerente sea válido
        $nif_gerente = $_POST['nif_gerente'];
        $nifVal = validateSpanishID($nif_gerente);
        if ($nifVal['valid'] == false) {
            $errors[] = "NIF del gerente no válido";
        }

        // Comprobar que la empresa no existe
        $idcnx = db_connect();
        $sql = "SELECT CIF FROM empresa WHERE CIF = '$_POST[cif]'";
        $query = mysqli_query($idcnx, $sql);

        if (mysqli_num_rows($query) >= 1) {
            $errors[] = "La empresa ya existe";
        }

        // Fortaleza de la contraseña
        $_POST['pwd'] = trim($_POST['pwd']);
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^a-zA-Z\\d]).{8,}$/', $_POST['pwd'])) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres y contener al menos un carácter especial.";
        }

        // Validar formato de email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido.";
        }

        // Validar que el nombre del gerente y persona de contacto contengan solo letras
        $nombre_gerente = $_POST['nombre_gerente'];
        $persona_contacto = $_POST['persona_contacto'];

        if (!preg_match("/^[a-zA-Z ]*$/", $nombre_gerente)) {
            $errors[] = "Solo se permiten letras y espacios en blanco en el nombre del gerente.";
        }
        if (!preg_match("/^[a-zA-Z ]*$/", $persona_contacto)) {
            $errors[] = "Solo se permiten letras y espacios en blanco en la persona de contacto.";
        }


        // Si todo ok procesar y redirigir
        if (empty($errors)) {
            // Procesar los datos del formulario y almacenar en la base de datos
            // Conectar a la base de datos
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO empresa (CIF, denominacion, nombre_gerente, nif_gerente, direccion, email, tlf_contacto, persona_contacto, practicas_pasado, participa_practicas, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sssssssssis', $cif, $denominacion, $nombre_gerente, $nif_gerente, $direccion, $email, $tlf_contacto, $persona_contacto, $practicas_pasado, $participa_practicas, $observaciones);

            // Asignar los valores a las variables
            $cif = $_POST['cif'];
            $pwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
            $denominacion = $_POST['denominacion'];
            $nombre_gerente = $_POST['nombre_gerente'];
            $nif_gerente = $_POST['nif_gerente'];
            $direccion = $_POST['direccion'];
            $email = $_POST['email'];
            $tlf_contacto = $_POST['tlf_contacto'];
            $persona_contacto = $_POST['persona_contacto'];
            $practicas_pasado = $_POST['practicas_pasado'];
            $participa_practicas = $_POST['participa_practicas'];
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';

            // Ejecutar la sentencia
            mysqli_stmt_execute($stmt);

            // Cerrar la sentencia y la conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);

            // Crear usuario
            // Conectar a la base de datos
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO usuarios (documento,password,tipo) VALUES (?,?,?)");

            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sss', $cif, $pwd, $tipo);

            // Asignar el valor a la variable tipo
            $tipo = "Empresa";

            // Ejecutar sentencia
            mysqli_stmt_execute($stmt);

            // Cerrar sentencia y conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);

            // Crear la sesión
            $_SESSION['tipo_usuario'] = 'empresa';
            $_SESSION['cif'] = $_POST['cif'];

            header('Location: success_page.php');
            exit;
        } else {
            // Repoblar los campos del formulario
            $cif = isset($_POST['cif']) ? $_POST['cif'] : '';
            $denominacion = isset($_POST['denominacion']) ? $_POST['denominacion'] : '';
            $nombre_gerente = isset($_POST['nombre_gerente']) ? $_POST['nombre_gerente'] : '';
            $nif_gerente = isset($_POST['nif_gerente']) ? $_POST['nif_gerente'] : '';
            $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $tlf_contacto = isset($_POST['tlf_contacto']) ? $_POST['tlf_contacto'] : '';
            $persona_contacto = isset($_POST['persona_contacto']) ? $_POST['persona_contacto'] : '';
            $practicas_pasado = isset($_POST['practicas_pasado']) ? $_POST['practicas_pasado'] : '';
            $participa_practicas = isset($_POST['participa_practicas']) ? $_POST['participa_practicas'] : '';
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
        }
    } else {
        // Repoblar los campos del formulario
        $cif = isset($_POST['cif']) ? $_POST['cif'] : '';
        $denominacion = isset($_POST['denominacion']) ? $_POST['denominacion'] : '';
        $nombre_gerente = isset($_POST['nombre_gerente']) ? $_POST['nombre_gerente'] : '';
        $nif_gerente = isset($_POST['nif_gerente']) ? $_POST['nif_gerente'] : '';
        $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $tlf_contacto = isset($_POST['tlf_contacto']) ? $_POST['tlf_contacto'] : '';
        $persona_contacto = isset($_POST['persona_contacto']) ? $_POST['persona_contacto'] : '';
        $practicas_pasado = isset($_POST['practicas_pasado']) ? $_POST['practicas_pasado'] : '';
        $participa_practicas = isset($_POST['participa_practicas']) ? $_POST['participa_practicas'] : '';
        $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    }
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta empresa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#direccion').on('input', function () {
                var inputVal = $(this).val();
                if (inputVal.length > 3) {
                    $.ajax({
                        url: 'https://api.geoapify.com/v1/geocode/autocomplete',
                        type: 'GET',
                        data: {
                            text: inputVal,
                            apiKey: '4a4565290b18475b963ce97c88d59827',
                            lang: 'es'
                        },
                        success: function (result) {
                            $('#direccion_dropdown').empty();
                            $.each(result.features, function (index, result) {
                                $('#direccion_dropdown').append($('<option>', {
                                    value: result.properties.formatted,
                                    text: result.properties.formatted
                                }));
                            });
                        }
                    });
                }
            });

            $('#direccion_dropdown').on('click', function () {
                $('#direccion').val(this.value);
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <h1 class="text-center mt-5 mb-4 text-primary">Crear cuenta (Empresa)</h1>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="cif">CIF:</label>
                <input type="text" id="cif" name="cif" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="pwd">Contraseña:</label>
                <input type="password" id="pwd" name="pwd" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="denominacion">Denominación:</label>
                <input type="text" id="denominacion" name="denominacion" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nombre_gerente">Nombre Gerente:</label>
                <input type="text" id="nombre_gerente" name="nombre_gerente" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nif_gerente">NIF Gerente:</label>
                <input type="text" id="nif_gerente" name="nif_gerente" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" class="form-control" required><br>
                <select id="direccion_dropdown" class="form-control"></select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="tlf_contacto">Teléfono de contacto:</label>
                <input type="tel" id="tlf_contacto" name="tlf_contacto" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="persona_contacto">Persona de contacto:</label>
                <input type="text" id="persona_contacto" name="persona_contacto" class="form-control" required>
            </div>

            <div class="form-group">
                <label>¿Ha participado en el programa de pr&aacute;cticas laborales con anterioridad?</label>
                <div class="form-check">
                    <label class="form-check-label" for="participa_pasado_si">Sí</label>
                    <input class="form-check-input" type="radio" id="practicas_pasado_si" name="practicas_pasado"
                        value="si" <?php
                        if (isset($practicas_pasado) && $practicas_pasado == "si") {
                            echo 'checked';
                        }
                        ?>>
                </div>
                <div class="form-check">
                    <label class="form-check-label" for="practicas_pasado_no"> No </label>
                    <input class="form-check-input" type="radio" id="practicas_pasado_no" name="practicas_pasado"
                        value="no" <?php
                        if (isset($practicas_pasado) && $practicas_pasado == "no") {
                            echo 'checked';
                        }
                        ?>>

                </div>
            </div>

            <div class="form-group">
                <label>¿Desea participar en el programa de pr&aacute;cticas laborales?</label>
                <div class="form-check">
                    <label class="form-check-label" for="participa_practicas_si">Sí</label>
                    <input class="form-check-input" type="radio" id="participa_practicas_si" name="participa_practicas"
                        value="si" <?php
                        if (isset($participa_practicas) && $participa_practicas == "si") {
                            echo 'checked';
                        }
                        ?>>
                </div>
                <div class="form-check">
                    <label class="form-check-label" for="participa_practicas_no">No</label>
                    <input class="form-check-input" type="radio" id="participa_practicas_no" name="participa_practicas"
                        value="no" <?php
                        if (isset($participa_practicas) && $participa_practicas == "no") {
                            echo 'checked';
                        }
                        ?>>
                </div>
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones:</label>
                <textarea id="observaciones" name="observaciones" class="form-control"></textarea>
            </div>

            <!-- Mostrar errores -->
            <?php if (!empty($errors)): ?>
                <div style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <?php echo $error; ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Crear cuenta</button>
        </form>
        <div class="text-center mt-3">
            <button class="btn btn-secondary" onclick="window.location.href='login_empresa.php'">Volver</button>
        </div>
    </div>


</body>

</html>