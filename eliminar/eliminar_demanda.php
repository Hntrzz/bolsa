<?php
session_start();
require_once '../db/db_connect.php';
$idcnx = db_connect();

if (isset($_POST['id_demanda'])) {
    $idDemanda = $_POST['id_demanda'];
    $query = "DELETE FROM demanda_emp WHERE id = $idDemanda";
    mysqli_query($idcnx, $query);
}

mysqli_close($idcnx);
header('Location: ../paneles/panel_alu.php');
exit();