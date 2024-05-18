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

// Consulta a la base de datos
$query = "SELECT * FROM ofertas_emp";
$result = mysqli_query($idcnx, $query);

// Consulta para obtener las demandas de empleo del usuario
$queryDemandas = "SELECT * FROM demanda_emp WHERE DNI = '" . $_SESSION['dni'] . "'";
$resultDemandas = mysqli_query($idcnx, $queryDemandas);
$demandas = mysqli_fetch_all($resultDemandas, MYSQLI_ASSOC);

// Almacenar el resultado de la consulta
$ofertas = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal de alumno</title>
    <link rel="stylesheet" href="../style/listas.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body class='m-3'>
    <div class="container text-center">
        <h1 class="mb-4 text-primary">BOLSA DE EMPLEO IES ALCÁNTARA</h1>
        <div class="row mb-4">
            <div class="col">
                <button class="btn btn-primary mr-2" onclick="window.location.href='../perfiles/perfil_alu.php'">Perfil
                    de usuario</button>
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalDemandas">Ver mis demandas
                    de empleo</button>
                <button class="btn btn-primary mr-2" onclick="window.location.href='crear_demanda.php'">Añadir demanda
                    de empleo</button>
                <button class="btn btn-danger" onclick="window.location.href='../auth/logoff.php'">Cerrar
                    sesión</button>
            </div>
        </div>

        <div class="row">
            <?php if ($ofertas): ?>
                <?php foreach ($ofertas as $index => $oferta): ?>
                    <div class="col-md-4 mb-3">
                        <button type="button" class="btn btn-dark btn-block" data-toggle="modal"
                            data-target="#modalOferta<?php echo $index; ?>">
                            <div class='card-body'>
                                <p class="card-text">CIF: <?php echo htmlspecialchars($oferta['CIF']); ?></p>
                                <p class="card-text">Denominación Empresa:
                                    <?php echo htmlspecialchars($oferta['denominacion_emp']); ?>
                                </p>
                                <p class="card-text">Dirección: <?php echo htmlspecialchars($oferta['direccion']); ?></p>
                                <p class="card-text">Email: <?php echo htmlspecialchars($oferta['email']); ?></p>
                                <p class="card-text">Teléfono de contacto:
                                    <?php echo htmlspecialchars($oferta['tlf_contacto']); ?>
                                </p>
                                <p class="card-text">Tipo de trabajo: <?php echo htmlspecialchars($oferta['tipo_trabajo']); ?>
                                </p>
                                <p class="card-text">Número de plazas: <?php echo htmlspecialchars($oferta['num_plazas']); ?>
                                </p>
                                <p class="card-text">Observaciones: <?php echo htmlspecialchars($oferta['observaciones']); ?>
                                </p>
                            </div>
                        </button>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="modalOferta<?php echo $index; ?>" tabindex="-1" role="dialog"
                        aria-labelledby="modalOfertaLabel<?php echo $index; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalOfertaLabel<?php echo $index; ?>">Detalles de la Oferta
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>CIF: <?php echo htmlspecialchars($oferta['CIF']); ?></p>
                                    <p>Denominación Empresa: <?php echo htmlspecialchars($oferta['denominacion_emp']); ?></p>
                                    <p>Dirección: <?php echo htmlspecialchars($oferta['direccion']); ?></p>
                                    <p>Email: <?php echo htmlspecialchars($oferta['email']); ?></p>
                                    <p>Teléfono de contacto: <?php echo htmlspecialchars($oferta['tlf_contacto']); ?></p>
                                    <p>Tipo de trabajo: <?php echo htmlspecialchars($oferta['tipo_trabajo']); ?></p>
                                    <p>Número de plazas: <?php echo htmlspecialchars($oferta['num_plazas']); ?></p>
                                    <p>Observaciones: <?php echo htmlspecialchars($oferta['observaciones']); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col">
                    <h1>No hay ofertas disponibles en este momento.</h1>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal para mostrar las demandas de empleo del usuario -->
        <div class="modal fade" id="modalDemandas" tabindex="-1" role="dialog" aria-labelledby="modalDemandasLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDemandasLabel">Mis demandas de empleo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body table-responsive">
                        <?php if ($demandas): ?>
                            <table class='table table-striped table-hover table-bordered'>
                                <thead>
                                    <tr>
                                        <th>DNI</th>
                                        <th>Nombre</th>
                                        <th>Apellidos</th>
                                        <th>Dirección</th>
                                        <th>Email</th>
                                        <th>Teléfono de contacto</th>
                                        <th>Habilidades ofertadas</th>
                                        <th>Observaciones</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($demandas as $demanda): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($demanda['DNI']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['apellidos']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['direccion']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['email']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['tlf_contacto']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['habilidades_ofertadas']); ?></td>
                                            <td><?php echo htmlspecialchars($demanda['observaciones']); ?></td>
                                            <!-- Botón de eliminación -->
                                            <td>
                                                <form action="../eliminar/eliminar_demanda.php" method="post">
                                                    <input type="hidden" name="id_demanda"
                                                        value="<?php echo $demanda['ID']; ?>">
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay demandas de empleo de este usuario.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>