<?php
require "../db/db_connect.php";
session_start();

// Comprobar si el usuario ya ha iniciado sesión
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'empresa') {
    header('Location: login.php');
    exit();
}

// Obtener el CIF de la empresa desde la sesión
$cif = $_SESSION['cif'];

// Conectar a la base de datos
$idcnx = db_connect();

// Obtener los datos de la empresa
$query = "SELECT * FROM empresa WHERE CIF = '$cif'";
$result = mysqli_query($idcnx, $query);
$empresa = mysqli_fetch_assoc($result);

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);

// Validar el envío del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = array();
    $bien = array();
    $update_fields = array();

    // Validar y actualizar campos modificados
    $fields_to_check = array('denominacion', 'nombre_gerente', 'nif_gerente', 'direccion', 'email', 'telefono_contacto', 'persona_contacto', "practicas_pasado", "participa_practicas", 'observaciones');
    foreach ($fields_to_check as $field) {
        if (empty($_POST[$field])) {
            unset($_POST[$field]);
        } else {
            $update_fields[$field] = $_POST[$field];
        }
    }

    // Añadir validación de nombre de gerente y denominación
    if (!empty($_POST['nombre_gerente']) && $_POST['nombre_gerente'] != $empresa['nombre_gerente']) {
        if (!preg_match("/^[a-zA-Z ]*$/", $_POST['nombre_gerente'])) {
            $errors[] = "Solo se permiten letras y espacios en blanco en el nombre del gerente.";
        } else {
            $bien[] = "Nombre del gerente actualizado";
        }
    }
    if (!empty($_POST['denominacion']) && $_POST['denominacion'] != $empresa['denominacion']) {
        if (!preg_match("/^[a-zA-Z0-9 ]*$/", $_POST['denominacion'])) {
            $errors[] = "Solo se permiten letras, números y espacios en blanco en la denominación.";
        } else {
            $bien[] = "Denominación actualizada";
        }
    }

    // Añadir validación de NIF de gerente
    if (!empty($_POST['nif_gerente' && $_POST['nif_gerente'] != $empresa['nif_gerente']])) {
        if (!preg_match("/^[0-9]{8}[A-Z]$/", $_POST['nif_gerente'])) {
            $errors[] = "El NIF del gerente no es válido.";
        } else {
            $bien[] = "NIF del gerente actualizado";
        }
    }

    // Añadir validación de formato de email
    if (!empty($_POST['email']) && $_POST['email'] != $empresa['email']) {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email no es válido.";
        } else {
            $bien[] = "Email actualizado";
        }
    }

    if (!empty($_POST['direccion']) && $_POST['direccion'] != $empresa['direccion']) {
        $bien[] = "Campo dirección actualizado";
    }

    if (!empty($_POST['tlf_contacto']) && $_POST['tlf_contacto'] != $empresa['tlf_contacto']) {
        $bien[] = "Campo de teléfono actualizado";
    }

    if (!empty($_POST['persona_contacto']) && $_POST['persona_contacto'] != $empresa['persona_contacto']) {
        $bien[] = "Persona de contacto actualizada";
    }

    if (!empty($_POST['practicas_pasado']) && $_POST['practicas_pasado'] != $empresa['practicas_pasado']) {
        $bien[] = "Estado de participación pasada en el programa de pr&aacute;cticas laborales actualizado";
    }

    if (!empty($_POST['participa_practicas']) && $_POST['participa_practicas'] != $empresa['participa_practicas']) {
        $bien[] = "Estado de participación actual en el programa de pr&aacute;cticas laborales actualizado";
    }

    if (!empty($_POST['observaciones']) && $_POST['observaciones'] != $empresa['observaciones']) {
        $bien[] = "Observaciones actualizada";
    }

    // Actualizar los datos en la base de datos si hay cambios
    if (empty($errors) && count($update_fields) > 0) {
        $idcnx = db_connect();
        $query = "UPDATE empresa SET ";
        foreach ($update_fields as $field => $value) {
            $query .= "$field = '$value', ";
        }
        $query = rtrim($query, ', ');
        $query .= " WHERE CIF = '$cif'";
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
    <title>Perfil de Empresa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="../jquery/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        <h1 class="mt-4 mb-4 text-center text-primary">Perfil de Empresa</h1>
        <form method="post">
            <div class="form-group row">
                <label for="cif" class="col-sm-2 col-form-label">CIF:</label>
                <div class="col-sm-10">
                    <span id="cif" name="cif" class="form-control"><?php echo $empresa['CIF']; ?></span>
                </div>
            </div>
            <div class="form-group row">
                <label for="denominacion" class="col-sm-2 col-form-label">Denominación:</label>
                <div class="col-sm-10">
                    <input type="text" id="denominacion" name="denominacion" class="form-control"
                        value="<?php echo $empresa['denominacion']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="nombre_gerente" class="col-sm-2 col-form-label">Nombre de gerente:</label>
                <div class="col-sm-10">
                    <input type="text" id="nombre_gerente" name="nombre_gerente" class="form-control"
                        value="<?php echo $empresa['nombre_gerente']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="nif_gerente" class="col-sm-2 col-form-label">NIF de gerente:</label>
                <div class="col-sm-10">
                    <input type="text" id="nif_gerente" name="nif_gerente" class="form-control"
                        value="<?php echo $empresa['nif_gerente']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="direccion" class="col-sm-2 col-form-label">Dirección:</label>
                <div class="col-sm-10">
                    <input type="text" id="direccion" name="direccion" class="form-control"
                        value="<?php echo $empresa['direccion']; ?>">
                    <select name="direccion_dropdown" id="direccion_dropdown"
                        class="form-control mt-2 direccion_dropdown"></select>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-2 col-form-label">Email:</label>
                <div class="col-sm-10">
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?php echo $empresa['email']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="tlf_contacto" class="col-sm-2 col-form-label">Teléfono de contacto:</label>
                <div class="col-sm-10">
                    <input type="tel" id="tlf_contacto" name="tlf_contacto" class="form-control"
                        value="<?php echo $empresa['tlf_contacto']; ?>">
                </div>
            </div>
            <div class="form-group row">
                <label for="persona_contacto" class="col-sm-2 col-form-label">Persona de contacto:</label>
                <div class="col-sm-10">
                    <input type="text" id="persona_contacto" name="persona_contacto" class="form-control"
                        value="<?php echo $empresa['persona_contacto']; ?>">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-2">¿Ha participado en el programa de pr&aacute;cticas laborales con
                    anterioridad?</label>
                <div class="col-sm-10">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="practicas_pasado_si" name="practicas_pasado"
                            value="si" <?php if ($empresa['practicas_pasado'] == 'si') {
                                echo 'checked';
                            } ?>>
                        <label class="form-check-label" for="trabaja_si">Sí</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="practicas_pasado_no" name="practicas_pasado"
                            value="no" <?php if ($empresa['practicas_pasado'] == 'no') {
                                echo 'checked';
                            } ?>>
                        <label class="form-check-label" for="practicas_pasado">No</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-2">¿Desea participar en el programa de pr&aacute;cticas laborales?</label>
                <div class="col-sm-10">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="participa_practicas_si"
                            name="participa_practicas" value="si" <?php if ($empresa['participa_practicas'] == 'si') {
                                echo 'checked';
                            } ?>>
                        <label class="form-check-label" for="trabaja_si">Sí</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="participa_practicas_no"
                            name="participa_practicas" value="no" <?php if ($empresa['participa_practicas'] == 'no') {
                                echo 'checked';
                            } ?>>
                        <label class="form-check-label" for="trabaja_no">No</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="observaciones" class="col-sm-2 col-form-label">Observaciones:</label>
                <div class="col-sm-10">
                    <textarea id="observaciones" name="observaciones"
                        class="form-control"><?php echo $empresa['observaciones']; ?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Actualizar perfil</button>
                    <button type="button" class="btn btn-secondary"
                        onclick="window.location.href='../paneles/panel_empresa.php'">Volver</button>
                </div>
                <!-- Mostrar errores -->
                <?php if (!empty($errors)): ?>
                    <div style="color: red;">
                        <?php foreach ($errors as $error): ?>
                            <?php echo $error; ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($bien)):
                    $bien[] = "<h1>VUELVA AL PANEL Y VUELVA A ENTRAR AL PERFIL PARA VER LOS CAMBIOS</h1>"
                        ?>
                    <div style="color: green;">
                        <?php foreach ($bien as $cambio): ?>
                            <?php echo $cambio; ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</body>

</html>