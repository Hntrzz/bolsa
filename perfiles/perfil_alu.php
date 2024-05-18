<?php
require "../db/db_connect.php";
require "../dni/dni_val.php";
session_start();

// Comprobar si el usuario ya ha iniciado sesión
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'alumno') {
    header('Location: login.php');
    exit();
}

// Obtener el DNI del alumno desde la sesión
$dni = $_SESSION['dni'];

// Conectar a la base de datos
$idcnx = db_connect();

// Obtener los datos del alumno
$query = "SELECT * FROM alumno WHERE DNI = '$dni'";
$result = mysqli_query($idcnx, $query);
$alumno = mysqli_fetch_assoc($result);

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
    $bien = array();
    $update_fields = array();

    // Validar y actualizar campos modificados
    $fields_to_check = array('nombre', 'apellidos', 'direccion', 'email', 'tlf', 'afinest', 'empresa_fct', 'trabaja', 'observaciones');
    foreach ($fields_to_check as $field) {
        if (empty($_POST[$field])) {
            unset($_POST[$field]);
        } else {
            $update_fields[$field] = $_POST[$field];
        }
    }

    // Añadir validación de contraseña en la sección de validación del formulario
    if (!empty($_POST['pwd'])) {
        $pwd = trim($_POST['pwd']);
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $pwd)) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres y contener al menos un carácter especial, una letra mayúscula, una letra minúscula y un número.";
        } else {
            // Encriptar la contraseña antes de guardarla en la base de datos
            $update_fields['pwd'] = password_hash($pwd, PASSWORD_DEFAULT);
            $bien[] = "La contraseña se ha actualizado";
        }
    }


    // Añadir validación de nombre y apellidos
    if (!empty($_POST['nombre']) && $_POST['nombre'] != $alumno['nombre']) {
        if (!preg_match("/^[a-zA-Z ]*$/", $_POST['nombre'])) {
            $errors[] = "Solo se permiten letras y espacios en blanco en el nombre.";
        } else {
            $bien[] = "Nombre actualizado";
        }
    }
    if (!empty($_POST['apellidos']) && $_POST['apellidos'] != $alumno['apellidos']) {
        if (!preg_match("/^[a-zA-Z ]*$/", $_POST['apellidos'])) {
            $errors[] = "Solo se permiten letras y espacios en blanco en los apellidos.";
        } else {
            $bien[] = "Apellidos actualizados";
        }
    }

    // Añadir validación de formato de email
    if (!empty($_POST['email']) && $_POST['email'] != $alumno['email']) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido.";
        } else {
            $bien[] = "Email actualizado";
        }
    }

    if (!empty($_POST['direccion']) && $_POST['direccion'] != $alumno['direccion']) {
        $bien[] = "Campo dirección actualizado";
    }

    if (!empty($_POST['tlf']) && $_POST['tlf'] != $alumno['tlf']) {
        $bien[] = "Campo de teléfono actualizado";
    }

    // Añadir validación de año de finalización de estudios
    if (!empty($_POST['afinest']) && $_POST['afinest'] != $alumno['afinest']) {
        $anio_finalizacion_est = $_POST['afinest'];
        $anio_actual = date('Y');
        if ($anio_finalizacion_est < 1950) {
            $errors[] = "El año de finalización de estudios no puede ser anterior a 1950.";
        } elseif ($anio_finalizacion_est > $anio_actual) {
            $errors[] = "El año de finalización de estudios no puede ser superior al año actual.";
        } else {
            $bien[] = "Año de finalización de estudios actualizado";
        }
    }

    if (!empty($_POST['$empresa_fct']) && $_POST['empresa_fct'] != $alumno['empresa_fct']) {
        $bien[] = "Empresa de FCT actualizada";
    }

    if (!empty($_POST['trabaja']) && $_POST['trabaja'] != $alumno['trabaja']) {
        $bien[] = "Estado de trabajo actual actualizado";
    }

    if (!empty($_POST['observaciones']) && $_POST['observaciones'] != $alumno['observaciones']) {
        $bien[] = "Observaciones actualizada";
    }

    // Validar y actualizar el currículum si se ha subido uno nuevo. Almacenar histórico.
    if (!empty($_FILES['curriculum']['name'])) {
        // Validar que el archivo sea PDF
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $file_type = $finfo->file($_FILES['curriculum']['tmp_name']);
        if ($file_type == 'application/pdf') {
            // Actualizar la ruta del currículum
            if (!empty($alumno['curriculum'])) {
                unlink($alumno['curriculum']);
            }
            $curriculum_path = "../curriculum/" . $dni . "--" . $_FILES['curriculum']['name'];
            move_uploaded_file($_FILES['curriculum']['tmp_name'], $curriculum_path);
            $update_fields['curriculum'] = $curriculum_path;
            $bien[] = "Curriculum actualizado";

            //Manejo almacenamiento fichero histórico
            $historico_path = "../historico_cur/" . date('H-i-s--Y-m-d') . "--" . $_SESSION['dni'] . "--" . $_FILES['curriculum']['name'];
            copy($curriculum_path, $historico_path);


            //Conexión DB
            $idcnx = db_connect();

            // Preparar la sentencia SQL
            $stmt = mysqli_prepare($idcnx, "INSERT INTO historico_curriculum (fecha,dni,nombre,apellidos,curriculum) VALUES (?,?,?,?,?)");

            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:s');
            // Vincular los parámetros a la sentencia SQL
            mysqli_stmt_bind_param($stmt, 'sssss', $date, $_SESSION['dni'], $alumno['nombre'], $alumno['apellidos'], $historico_path);

            //Ejecutar sentencia
            mysqli_stmt_execute($stmt);

            //Cerrar sentencia y conexión
            mysqli_stmt_close($stmt);
            mysqli_close($idcnx);
        } else {
            $errors[] = "El currículum debe ser un archivo PDF.";
        }
    }


    // Actualizar los datos en la base de datos si hay cambios
    if (empty($errors) && count($update_fields) > 0) {
        $idcnx = db_connect();
        $query = "UPDATE alumno SET ";
        foreach ($update_fields as $field => $value) {
            $query .= "$field = '$value', ";
        }
        $query = rtrim($query, ', ');
        $query .= " WHERE DNI = '$dni'";
        mysqli_query($idcnx, $query);
        mysqli_close($idcnx);
        // Recargar la página para mostrar los datos actualizados
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Alumno</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
    <div class="container">
        <h1 class="mt-4 mb-4 text-primary text-center">Perfil del Alumno</h1>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group row">
                <label for="dni" class="col-sm-2 col-form-label">DNI:</label>
                <div class="col-sm-10">
                    <span id="dni" name="dni" class="form-control"><?php echo $alumno['dni']; ?></span>
                </div>
            </div>
            <div class="form-group row">
                <label for="pwd" class="col-sm-2 col-form-label">Contraseña:</label>
                <div class="col-sm-10">
                    <input type="password" id="pwd" name="pwd" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label for="nombre" class="col-sm-2 col-form-label">Nombre:</label>
                <div class="col-sm-10">
                    <input type="text" id="nombre" name="nombre" class="form-control"
                        value="<?php echo $alumno['nombre']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="apellidos" class="col-sm-2 col-form-label">Apellidos:</label>
                <div class="col-sm-10">
                    <input type="text" id="apellidos" name="apellidos" class="form-control"
                        value="<?php echo $alumno['apellidos']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="direccion" class="col-sm-2 col-form-label">Dirección:</label>
                <div class="col-sm-10">
                    <input type="text" id="direccion" name="direccion" class="form-control"
                        value="<?php echo $alumno['direccion']; ?>">
                    <select name="direccion_dropdown" id="direccion_dropdown" class="form-control mt-2"></select>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email:</label>
                <div class="col-sm-10">
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?php echo $alumno['email']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="tlf" class="col-sm-2 col-form-label">Teléfono:</label>
                <div class="col-sm-10">
                    <input type="tel" id="tlf" name="tlf" class="form-control" value="<?php echo $alumno['tlf']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="afinest" class="col-sm-2 col-form-label">Año de finalización de estudios:</label>
                <div class="col-sm-10">
                    <input type="number" id="afinest" name="afinest" class="form-control"
                        value="<?php echo $alumno['afinest']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="empresa_fct" class="col-sm-2 col-form-label">Empresa FCT:</label>
                <div class="col-sm-10">
                    <select id="empresa_fct" name="empresa_fct" class="form-control">
                        <?php foreach ($empresas_fct as $empresa): ?>
                            <option value="<?php echo htmlspecialchars($empresa); ?>" <?php echo (isset($_POST['empresa_fct']) && $_POST['empresa_fct'] == $empresa) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empresa); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-2">¿Trabaja actualmente?</label>
                <div class="col-sm-10">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="trabaja_si" name="trabaja" value="si" <?php if ($alumno['trabaja'] == 'si') {
                            echo 'checked';
                        } ?>>
                        <label class="form-check-label" for="trabaja_si">Sí</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="trabaja_no" name="trabaja" value="no" <?php if ($alumno['trabaja'] == 'no') {
                            echo 'checked';
                        } ?>>
                        <label class="form-check-label" for="trabaja_no">No</label>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="curriculum" class="col-sm-2 col-form-label">Subir nuevo currículum:</label>
                <input type="file" id="curriculum" name="curriculum" accept=".pdf">
                <div class="col-sm-10">
                    <?php if (!empty($alumno['curriculum'])): ?>
                        <a href="<?php echo $alumno['curriculum']; ?>" download>Descargar curriculum actual</a> <br>
                    <?php endif; ?>

                </div>
            </div>
            <div class="form-group row">
                <label for="observaciones" class="col-sm-2 col-form-label">Observaciones:</label>
                <div class="col-sm-10">
                    <textarea id="observaciones" name="observaciones"
                        class="form-control"><?php echo $alumno['observaciones']; ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" value="Actualizar perfil" class="btn btn-primary">
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location.href='../paneles/panel_alu.php'">Volver</button>
                </div>
            </div>
            <!-- Mostrar errores -->
            <?php if (!empty($errors)): ?>
                <div class="row">
                    <div class="col-sm-10 offset-sm-2" style="color: red;">
                        <?php foreach ($errors as $error): ?>
                            <?php echo $error; ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($bien)):
                $bien[] = "<h1>VUELVA AL PANEL Y ENTRE AL PERFIL DE NUEVO PARA VER LOS CAMBIOS</h1>" ?>
                <div class="row">
                    <div class="col-sm-10 offset-sm-2" style="color: green;">
                        <?php foreach ($bien as $cambio): ?>
                            <?php echo $cambio; ?><br>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>