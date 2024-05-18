<?php
session_start();

// Comprobar si el usuario ya ha iniciado sesión y si es del tipo 'admin'
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 'admin') {
    header('Location: ../index.php');
    exit();
}

// Conexión a la base de datos
require_once '../db/db_connect.php';
$idcnx = db_connect();

// Consultas a la base de datos para obtener los datos necesarios
$queryAlumnos = "SELECT * FROM alumno";
$resultAlumnos = mysqli_query($idcnx, $queryAlumnos);
$alumnos = mysqli_fetch_all($resultAlumnos, MYSQLI_ASSOC);

$queryEmpresas = "SELECT * FROM empresa";
$resultEmpresas = mysqli_query($idcnx, $queryEmpresas);
$empresas = mysqli_fetch_all($resultEmpresas, MYSQLI_ASSOC);

$queryDemandas = "SELECT * FROM demanda_emp";
$resultDemandas = mysqli_query($idcnx, $queryDemandas);
$demandas = mysqli_fetch_all($resultDemandas, MYSQLI_ASSOC);

$queryOfertas = "SELECT * FROM ofertas_emp";
$resultOfertas = mysqli_query($idcnx, $queryOfertas);
$ofertas = mysqli_fetch_all($resultOfertas, MYSQLI_ASSOC);

// Cerrar la conexión a la base de datos
mysqli_close($idcnx);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body class='m-3'>
    <h1 class="text-center text-primary mt-4 mb-4">Panel de Administración</h1>
    <div class="botones text-center">
        <!-- Botones para abrir los modales correspondientes -->
        <button data-toggle="modal" data-target="#modalAlumnos">Ver todos los alumnos</button>
        <button data-toggle="modal" data-target="#modalEmpresas">Ver todas las empresas</button>
        <button data-toggle="modal" data-target="#modalDemandas">Ver todas las demandas</button>
        <button data-toggle="modal" data-target="#modalOfertas">Ver todas las ofertas</button>
        <br>
        <button onclick="window.location.href='../auth/logoff.php'">Cerrar sesi&oacute;n</button>
    </div>

    <!-- Modales para mostrar la información de alumnos, empresas, demandas y ofertas -->
    <!-- Modal Alumnos -->
    <div class="modal fade" id="modalAlumnos" tabindex="-1" role="dialog" aria-labelledby="modalAlumnosLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAlumnosLabel">Listado de Alumnos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($alumno['dni']); ?></td>
                                    <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($alumno['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($alumno['email']); ?></td>
                                    <td>
                                        <form action="eliminar_alumno.php" method="post">
                                            <input type="hidden" name="dni_alumno" value="<?php echo $alumno['dni']; ?>" />
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Empresas -->
    <div class="modal fade" id="modalEmpresas" tabindex="-1" role="dialog" aria-labelledby="modalEmpresasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmpresasLabel">Listado de Empresas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>CIF</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empresas as $empresa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($empresa['CIF']); ?></td>
                                    <td><?php echo htmlspecialchars($empresa['denominacion']); ?></td>
                                    <td><?php echo htmlspecialchars($empresa['email']); ?></td>
                                    <td>
                                        <form action="eliminar_empresa.php" method="post">
                                            <input type="hidden" name="cif_empresa" value="<?php echo $empresa['CIF']; ?>">
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Demandas -->
    <div class="modal fade" id="modalDemandas" tabindex="-1" role="dialog" aria-labelledby="modalDemandasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDemandasLabel">Listado de Demandas de Empleo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Email</th>
                                <th>Teléfono de Contacto</th>
                                <th>Habilidades Ofertadas</th>
                                <th>Observaciones</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($demandas as $demanda): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($demanda['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['email']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['tlf_contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['habilidades_ofertadas']); ?></td>
                                    <td><?php echo htmlspecialchars($demanda['observaciones']); ?></td>
                                    <td>
                                        <form action="../eliminar/eliminar_demanda.php" method="post">
                                            <input type="hidden" name="id_demanda" value="<?php echo $demanda['ID']; ?>">
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Ofertas -->
    <div class="modal fade" id="modalOfertas" tabindex="-1" role="dialog" aria-labelledby="modalOfertasLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-xl">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalOfertasLabel">Listado de Ofertas de Empleo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Denominación Empresa</th>
                                <th>Dirección</th>
                                <th>Email</th>
                                <th>Teléfono de Contacto</th>
                                <th>Tipo de Trabajo</th>
                                <th>Número de Plazas</th>
                                <th>Observaciones</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ofertas as $index => $oferta): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($oferta['ID']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['denominacion_emp']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['direccion']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['email']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['tlf_contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['tipo_trabajo']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['num_plazas']); ?></td>
                                    <td><?php echo htmlspecialchars($oferta['observaciones']); ?></td>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>