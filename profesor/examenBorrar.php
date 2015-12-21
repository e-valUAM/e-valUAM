<?php

	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (!isset($_REQUEST['idExamen'])) {
		echo "0";
		die();
	}	

	$result =  pg_query_params($con, 
		'UPDATE examenes
		SET borrado = TRUE
		WHERE id = $1',
		array(intval($_REQUEST['idExamen'])));
	
	if (!$result) {
		echo "0";
		die();
	}

	echo "1";
	
?>