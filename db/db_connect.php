<?php

function db_connect()
{
	$HOSTNAME = "localhost";//SERVIDOR
	$USERNAME = "root";                //USUARIO 
	$PASSWORD = "";                //CONTRASE�A
	$DATABASE = "bolsa";        //BASE DE DATOS

	$idcnx = mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD) or die();

	mysqli_select_db($idcnx, $DATABASE);
	mysqli_set_charset($idcnx, "utf8");

	/* else
						 {			 
							 die(mysqli_error($idcnx));
						 } */
	return $idcnx;
}

