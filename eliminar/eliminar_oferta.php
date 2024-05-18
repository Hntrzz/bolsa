<?php
session_start();
require_once '../db/db_connect.php';
$idcnx = db_connect();

if (isset($_POST['id_oferta'])) {
    $idOferta = $_POST['id_oferta'];
    $query = "DELETE FROM ofertas_emp WHERE ID = $idOferta";
    mysqli_query($idcnx, $query);
}

mysqli_close($idcnx);
header('Location: ../paneles/panel_empresa.php');
exit();
