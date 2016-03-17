<?php

	include 'funciones_profesor.php';

	session_start();

	if (!isset($_SESSION['profesor'])) {
		header("Location: ./index.php?error=si");
		exit;
	}

	$con = connect()
		or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	
	if (isset($_REQUEST['nombre'])) {
			
		$disponible = ($_REQUEST['disponible'] == 't' ? 't' : 'f');
		$duda = ($_REQUEST['duda'] == 't' ? 't' : 'f');
		$feedExamen = ($_REQUEST['feedbackExamen'] == 't' ? 't' : 'f');

		//print_r(array($_REQUEST['nombre'], $disponible, intval($_REQUEST['duracion']), intval($_REQUEST['idMateria']), intval($_REQUEST['numPreguntas']), $_REQUEST['mostrarResultados'], $duda));
	
		$result = pg_query_params($con,
			'INSERT INTO examenes (nombre, disponible, duracion, id_materia, num_preguntas, num_por_nodo, mostrar_resultados, acepta_duda,feedback) VALUES ($1, $2, $3, $4, $5, 1, $6, $7,$8);',
			array($_REQUEST['nombre'], $disponible, intval($_REQUEST['duracion']), intval($_REQUEST['idMateria']), intval($_REQUEST['numPreguntas']), $_REQUEST['mostrarResultados'], $duda,$feedExamen));
		
		if ($result) {
			header("Location: ./gestionExamenes.php?res=1");
			exit;
		}
	}

	header("Location: ./gestionExamenes.php?res=0");
	
?>
