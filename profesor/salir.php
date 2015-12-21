<?php

	include 'funciones_profesor.php';
	
	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	session_unset();

	header("Location: ./index.php");
   	exit;

?>
