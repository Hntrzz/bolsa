<?php
session_start();
require_once '../db/db_connect.php'; // Asegúrate de tener el archivo de conexión a la base de datos
$idcnx = db_connect(); // Esta función debe establecer la conexión a la base de datos

if (isset($_POST['dni_alumno'])) {
    $dni = $_POST['dni_alumno'];

    // Paso 1: Eliminar entrada de la tabla "usuarios"
    $query_delete_usuario = "DELETE FROM usuarios WHERE documento = '$dni'";
    mysqli_query($idcnx, $query_delete_usuario);

    // Paso 2: Eliminar curriculum del usuario
    $query_curriculum = "SELECT curriculum FROM alumno WHERE dni = '$dni'";
    $result_curriculum = mysqli_query($idcnx, $query_curriculum);
    if ($row = mysqli_fetch_assoc($result_curriculum)) {
        $curriculum_path = $row['curriculum'];
        if (file_exists($curriculum_path)) {
            unlink($curriculum_path);
        }
    }

    // Paso 3: Eliminar ficheros de curriculum histórico
    $query_historico = "SELECT curriculum FROM historico_curriculum WHERE dni = '$dni'";
    $result_historico = mysqli_query($idcnx, $query_historico);
    while ($row = mysqli_fetch_assoc($result_historico)) {
        $historico_curriculum_path = $row['curriculum'];
        if (file_exists($historico_curriculum_path)) {
            unlink($historico_curriculum_path);
        }
    }

    // Paso 4: Eliminar entrada de la tabla alumno
    $query_delete_alumno = "DELETE FROM alumno WHERE dni = '$dni'";
    mysqli_query($idcnx, $query_delete_alumno);
}

mysqli_close($idcnx);
header('Location: panel_admin.php');
exit();
