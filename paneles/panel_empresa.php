<?php
session_start();

// Comprobar si el usuario ya ha iniciado sesión y si es del tipo 'empresa'
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'empresa') {
    // Si el tipo de usuario es 'alumno', redirigir al panel de alumno
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        header('Location: panel_alu.php');
        exit();
    }
    // Si no hay sesión o el tipo de usuario no es 'empresa', redirigir al login
    header('Location: ../index.php');
    exit();
}

// Conexión a la base de datos
require_once '../db/db_connect.php';
$idcnx = db_connect();

// Consulta a la base de datos
$query = "SELECT * FROM demanda_emp";
$result = mysqli_query($idcnx, $query);

// Consulta para obtener las ofertas de empleo del usuario
$queryOfertas = "SELECT * FROM ofertas_emp WHERE CIF = '" . $_SESSION['cif'] . "'";
$resultOfertas = mysqli_query($idcnx, $queryOfertas);
$ofertas = mysqli_fetch_all($resultOfertas, MYSQLI_ASSOC);

// Almacenar el resultado de la consulta
$demandas = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal de empresa</title>
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
                <button class="btn btn-primary mr-2" onclick="window.location.href='../perfiles/perfil_emp.php'">Perfil
                    de empresa</button>
                <button class="btn btn-primary mr-2" data-toggle="modal" data-target="#modalOfertas">Ver mis ofertas
                    de empleo</button>
                <button class="btn btn-primary mr-2" onclick="window.location.href='crear_oferta.php'">Añadir oferta
                    de empleo</button>
                <button class="btn btn-danger" onclick="window.location.href='../auth/logoff.php'">Cerrar
                    sesión</button>
            </div>
        </div>

        <div class="row">
            <?php if ($demandas): ?>
                <?php foreach ($demandas as $index => $demanda): ?>
                    <div class="col-md-4 mb-3">
                        <button type="button" class="btn btn-dark btn-block" data-toggle="modal"
                            data-target="#modalDemanda<?php echo $index; ?>">
                            <div class='card-body'>
                                <p class="card-text">DNI: <?php echo htmlspecialchars($demanda['DNI']); ?></p>
                                <p class="card-text">Nombre: <?php echo htmlspecialchars($demanda['nombre']); ?></p>
                                <p class="card-text">Apellidos: <?php echo htmlspecialchars($demanda['apellidos']); ?></p>
                                <p class="card-text">Dirección: <?php echo htmlspecialchars($demanda['direccion']); ?></p>
                                <p class="card-text">Email: <?php echo htmlspecialchars($demanda['email']); ?></p>
                                <p class="card-text">Teléfono de contacto:
                                    <?php echo htmlspecialchars($demanda['tlf_contacto']); ?>
                                </p>
                                <p class="card-text">Habilidades ofertadas:
                                    <?php echo htmlspecialchars($demanda['habilidades_ofertadas']); ?>
                                </p>
                                <p class="card-text">Observaciones: <?php echo htmlspecialchars($demanda['observaciones']); ?>
                                </p>
                            </div>
                        </button>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="modalDemanda<?php echo $index; ?>" tabindex="-1" role="dialog"
                        aria-labelledby="modalDemandaLabel<?php echo $index; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalDemandaLabel<?php echo $index; ?>">Detalles de la Demanda
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>DNI: <?php echo htmlspecialchars($demanda['DNI']); ?></p>
                                    <p>Nombre: <?php echo htmlspecialchars($demanda['nombre']); ?></p>
                                    <p>Apellidos: <?php echo htmlspecialchars($demanda['apellidos']); ?></p>
                                    <p>Dirección: <?php echo htmlspecialchars($demanda['direccion']); ?></p>
                                    <p>Email: <?php echo htmlspecialchars($demanda['email']); ?></p>
                                    <p>Teléfono de contacto: <?php echo htmlspecialchars($demanda['tlf_contacto']); ?></p>
                                    <p>Habilidades ofertadas: <?php echo htmlspecialchars($demanda['habilidades_ofertadas']); ?>
                                    </p>
                                    <p>Observaciones: <?php echo htmlspecialchars($demanda['observaciones']); ?></p>
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
                    <h1>No hay demandas disponibles en este momento.</h1>
                </div>
            <?php endif; ?>
        </div>

        <!-- Modal para mostrar las ofertas de empleo de la empresa -->
        <div class="modal fade" id="modalOfertas" tabindex="-1" role="dialog" aria-labelledby="modalOfertasLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalOfertasLabel">Mis ofertas de empleo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body table-responsive">
                        <?php if ($ofertas): ?>
                            <table class='table table-striped table-hover table-bordered'>
                                <thead>
                                    <tr>
                                        <th>CIF</th>
                                        <th>Denominación Empresa</th>
                                        <th>Dirección</th>
                                        <th>Email</th>
                                        <th>Teléfono de contacto</th>
                                        <th>Tipo de trabajo</th>
                                        <th>Número de plazas</th>
                                        <th>Observaciones</th>
                                        <th>Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ofertas as $oferta): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($oferta['CIF']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['denominacion_emp']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['direccion']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['email']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['tlf_contacto']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['tipo_trabajo']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['num_plazas']); ?></td>
                                            <td><?php echo htmlspecialchars($oferta['observaciones']); ?></td>
                                            <!-- Botón de eliminación -->
                                            <td>
                                                <form action="../eliminar/eliminar_oferta.php" method="post">
                                                    <input type="hidden" name="id_oferta" value="<?php echo $oferta['ID']; ?>">
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No hay ofertas de empleo de este usuario.</p>
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