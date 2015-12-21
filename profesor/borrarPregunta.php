<?php

	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (!isset($_REQUEST['idPregunta'])) {
		echo "0";
		die();
	}	

	$result =  pg_query_params($con, 
		'UPDATE preguntas
		SET borrada = TRUE
		WHERE id = $1',
		array($_REQUEST['idPregunta']));
	
	if (!$result) {
		echo "0";
		die();
	}

	echo "1";
	
?>