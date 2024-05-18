<?php
session_start();
if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] == 'alumno') {
        unset($_SESSION['dni']);
        unset($_SESSION['tipo_usuario']);
        session_destroy();
        header('Location: ../index.php');
        return;
    } elseif ($_SESSION['tipo_usuario'] == 'empresa') {
        unset($_SESSION['cif']);
        unset($_SESSION['tipo_usuario']);
        session_destroy();
        header('Location: ../index.php');
        return;
    } else {
        unset($_SESSION['username']);
        unset($_SESSION['tipo_usuario']);
        session_destroy();
        header('Location: ../index.php');
        return;
    }
} else {
    header('Location: ../errors/error_logoff.php');
}