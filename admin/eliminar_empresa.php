<?php
session_start();
require_once '../db/db_connect.php'; // Asegúrate de tener el archivo de conexión a la base de datos
$idcnx = db_connect(); // Esta función debe establecer la conexión a la base de datos

if (isset($_POST['cif_empresa'])) {
    $cif = $_POST['cif_empresa'];

    // Paso 1: Eliminar entrada de la tabla "usuarios"
    $query_delete_usuario = "DELETE FROM usuarios WHERE documento = '$cif'";
    mysqli_query($idcnx, $query_delete_usuario);

    // Paso 2: Eliminar entrada de la tabla "empresa"
    $query_delete_empresa = "DELETE FROM empresa WHERE CIF = '$cif'";
    mysqli_query($idcnx, $query_delete_empresa);
}

mysqli_close($idcnx);
header('Location: panel_admin.php'); // Ajusta la ubicación según corresponda
exit();
