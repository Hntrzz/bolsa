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
//Obtener
$idcnx = db_connect();

// Preparar y ejecutar la consulta SQL para obtener las denominaciones únicas
$query = "SELECT DISTINCT denominacion FROM empresa ORDER BY denominacion ASC";
$result = mysqli_query($idcnx, $query);

// Crear un array para almacenar las opciones de la lista desplegable
$empresas_fct = array();
while ($row = mysqli_fetch_assoc($result)) {
    $empresas_fct[] = $row['denominacion'];
}

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);

// Validar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = array();

    // Validar campos obligatorios
    $required_fields = array('nombre', 'apellidos', 'direccion', 'email', 'tlf', 'afinest', 'empresa_fct', 'trabaja');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "El campo $field es obligatorio.";
        }
    }

    if (empty($errors)) {

        //Validar DNI
        $dniVal = validateSpanishID($_POST['dni']);
        if ($dniVal['valid'] == false) {
            $errors[] = "DNI no válido";
        }

        //Comprobarq que el usuario no existe
        $idcnx = db_connect();
        $sql = "SELECT DNI FROM alumno WHERE DNI = '$_POST[dni]'";
        $query = mysqli_query($idcnx, $sql);

        if (mysqli_num_rows($query) >= 1) {
            $errors[] = "El usuario ya existe";
        }

        // Fortaleza de la contraseña
        $_POST['pwd'] = trim($_POST['pwd']);

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^a-zA-Z\\d]).{8,}$/', $_POST['pwd'])) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y contener al menos un carácter especial.";
        }


        // Validar formato de email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido.";
        }

        // Validar subida de archivo de currículum
        if (empty($_FILES['curriculum']['name'])) {
            $errors[] = "El currículum es obligatorio.";
        } else {

            // Crear un objeto de finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            // Obtener el tipo MIME del archivo subido
            $file_type = $finfo->file($_FILES['curriculum']['tmp_name']);

            // Verificar si el tipo MIME corresponde a 'application/pdf'
            if ($file_type != 'application/pdf') {
                $errors[] = "El currículum debe ser un archivo PDF.";
            }
        }

        // Validar que el nombre y apellidos contengan solo letras
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];

        if (!preg_match("/^[a-zA-Z ]*$/", $nombre)) {
            $errors[] = "Solo se permiten letras y espacios en blanco en el nombre.";
        }
        if (!preg_match("/^[a-zA-Z ]*$/", $apellidos)) {
            $errors[] = "Solo se permiten letras y espacios en blanco en los apellidos.";
        }

        // Validar que el año de finalización de estudios no sea anterior a 1950
        $anio_finalizacion_est = $_POST['afinest'];

        if ($anio_finalizacion_est < 1950) {
            $errors[] = "El año de finalización de estudios no puede ser anterior a 1950.";
        } elseif ($anio_finalizacion_est > date('Y')) {
            $errors[] = "El año de finalización de estudios no puede ser superior al año actual";
        }

        // Si todo ok procesar y redirigir
        if (empty($errors)) {
            // Procesar los datos del formulario y almacenar en la base de datos
            // Conectar a la base de datos
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO alumno (dni,  nombre, apellidos, direccion, email, tlf, afinest, empresa_fct, trabaja, curriculum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sssssiisss', $dni, $nombre, $apellidos, $direccion, $email, $tlf, $afinest, $empresa_fct, $trabaja, $curriculum);

            // Asignar los valores a las variables
            $dni = $_POST['dni'];
            $pwd = password_hash($_POST['pwd'], PASSWORD_BCRYPT);
            $nombre = $_POST['nombre'];
            $apellidos = $_POST['apellidos'];
            $direccion = $_POST['direccion'];
            $email = $_POST['email'];
            $tlf = $_POST['tlf'];
            $afinest = $_POST['afinest'];
            $empresa_fct = $_POST['empresa_fct'];
            $trabaja = $_POST['trabaja'];
            $tipo = "Alumno";

            //Manejar almacenamiento curriculum
            $dir_destino = "../curriculum/";
            $filename = $_FILES['curriculum']['name'];
            $archivo_destino = $dir_destino . $_FILES['curriculum']['name'];
            move_uploaded_file($_FILES['curriculum']['tmp_name'], $archivo_destino);
            $nombre_nuevo = "../curriculum/" . $_POST['dni'] . "--" . $_FILES['curriculum']['name'];
            rename($archivo_destino, $nombre_nuevo);
            $curriculum = "../curriculum/" . $_POST['dni'] . "--" . $_FILES['curriculum']['name'];

            // Ejecutar la sentencia
            mysqli_stmt_execute($stmt);

            // Cerrar la sentencia y la conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);

            //Crear usuario
            // Conectar a la base de datos
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO usuarios (documento,password,tipo) VALUES (?,?,?)");

            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sss', $dni, $pwd, $tipo);

            //Ejecutar sentencia
            mysqli_stmt_execute($stmt);

            //Cerrar sentencia y conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);


            //Manejo almacenamiento fichero histórico
            copy($curriculum, "../historico_cur/" . date('H-i-s--Y-m-d') . "--" . $_POST['dni'] . "--" . $_FILES['curriculum']['name']);

            //Conexión DB
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO historico_curriculum (fecha,dni,nombre,apellidos,curriculum) VALUES (?,?,?,?,?)");

            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sssss', $date, $dni, $nombre, $apellidos, $curriculum);

            //Ejecutar sentencia
            mysqli_stmt_execute($stmt);

            //Cerrar sentencia y conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);

            //Creamos la sesion
            $_SESSION['tipo_usuario'] = 'alumno';
            $_SESSION['dni'] = $_POST['dni'];


            header('Location: success_page.php');
            exit;
        } else {
            // Repoblar los campos del formulario
            $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
            $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
            $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
            $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $tlf = isset($_POST['tlf']) ? $_POST['tlf'] : '';
            $afinest = isset($_POST['afinest']) ? $_POST['afinest'] : '';
            $empresa_fct = isset($_POST['empresa_fct']) ? $_POST['empresa_fct'] : '';
            $trabaja = isset($_POST['trabaja']) ? $_POST['trabaja'] : '';
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
        }
    } else {
        $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
        $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
        $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
        $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $tlf = isset($_POST['tlf']) ? $_POST['tlf'] : '';
        $afinest = isset($_POST['afinest']) ? $_POST['afinest'] : '';
        $empresa_fct = isset($_POST['empresa_fct']) ? $_POST['empresa_fct'] : '';
        $trabaja = isset($_POST['trabaja']) ? $_POST['trabaja'] : '';
        $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta alumno</title>
    <style>
        /* Quitar flechas en campos de numero para firefox */
        input[type="number"] {
            -moz-appearance: textfield;
        }

        /* Quitar flechas en campos de numero para otros navegadores */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
    <script src="../jquery/jquery.js"></script>
    <script>
        $(document).ready(function () {
            // Detectar la entrada del usuario en el campo de dirección
            $('#direccion').on('input', function () {
                var inputVal = $(this).val();
                if (inputVal.length > 3) { //Nº caracteres antes de empezar a hacer consultas
                    $.ajax({
                        url: 'https://api.geoapify.com/v1/geocode/autocomplete',
                        type: 'GET',
                        data: {
                            text: inputVal,
                            apiKey: '4a4565290b18475b963ce97c88d59827',
                            lang: 'es'
                        },
                        success: function (result) {
                            // Vaciar el menú desplegable existente
                            $('#direccion_dropdown').empty();
                            // Llenar el menú desplegable con las sugerencias de direcciones
                            $.each(result.features, function (index, result) {
                                console.log(result.properties.formatted)
                                $('#direccion_dropdown').append($('<option>', {
                                    value: result.properties.formatted,
                                    text: result.properties.formatted
                                }));
                            });
                        }
                    });
                }
            });

            // Manejar la selección de una dirección sugerida
            $('#direccion_dropdown').on('click', function () {
                // Actualizar el campo de dirección con la dirección seleccionada
                $('#direccion').val(this.value);
            });
        });
    </script>
</head>

<body>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Crear cuenta alumno</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>

    <body>
        <div class="container">
            <h1 class="mt-5 mb-4 text-center text-primary">Crear cuenta (Alumno)</h1>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="dni">DNI:</label>
                    <input type="text" class="form-control" id="dni" name="dni" <?php if (isset($dni)) {
                        echo "value='$dni'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="pwd">Contraseña:</label>
                    <input type="password" class="form-control" id="pwd" name="pwd">
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" <?php if (isset($nombre)) {
                        echo "value='$nombre'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" class="form-control" id="apellidos" name="apellidos" <?php if (isset($apellidos)) {
                        echo "value='$apellidos'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" <?php if (isset($direccion)) {
                        echo "value='$direccion'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <select class="custom-select" id="direccion_dropdown"></select>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" <?php if (isset($email)) {
                        echo "value='$email'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="tlf">Teléfono:</label>
                    <input type="tel" class="form-control" id="tlf" name="tlf" <?php if (isset($tlf)) {
                        echo "value='$tlf'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="afinest">Año de finalización de estudios:</label>
                    <input type="number" class="form-control" id="afinest" name="afinest" <?php if (isset($afinest)) {
                        echo "value='$afinest'";
                    } ?> required>
                </div>
                <div class="form-group">
                    <label for="empresa_fct">Empresa FCT:</label>
                    <select class="custom-select" id="empresa_fct" name="empresa_fct" required>
                        <?php foreach ($empresas_fct as $empresa): ?>
                            <option value="<?php echo htmlspecialchars($empresa); ?>" <?php echo (isset($_POST['empresa_fct']) && $_POST['empresa_fct'] == $empresa) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empresa); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>¿Trabaja actualmente?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="trabaja_si" name="trabaja" value="si" <?php
                        if (isset($trabaja) && $trabaja == "si") {
                            echo 'checked';
                        }
                        ?>>
           <label class="form-check-label" for="trabaja_si">Sí</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="trabaja_no" name="trabaja" value="no" <?php
                        if (isset($trabaja) && $trabaja == "no") {
                            echo 'checked';
                        }
                        ?>>
                    <label class="form-check-label" for="trabaja_no">No</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="curriculum">Currículum:</label>
                    <input type="file" class="form-control-file" id="curriculum" name="curriculum" accept=".pdf"
                        required>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea class="form-control" id="observaciones" name="observaciones"><?php if (isset($observaciones)) {
                        echo htmlspecialchars($observaciones);
                    } ?></textarea>
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
            <a href="login_alu.php" class="btn btn-secondary mt-3">Volver</a>
        </div>


    </body>

    </html>