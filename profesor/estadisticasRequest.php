<?php

	include 'funciones_profesor.php';

	check_login();

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');


    $result =  pg_query_params(
		$con, 
		'SELECT * FROM profesor_por_materia WHERE id_alumno = $1 AND id_materia = $2',
		array($_SESSION['idUsuario'], intval($_REQUEST['id'])))
	or die('La consulta fallo: ' . pg_last_error());
	

	if (pg_num_rows($result) == 1) {
		
		$result = pg_query_params(
			$con,
			'SELECT num_respuestas FROM materias WHERE id = $1',
			array(intval($_REQUEST['id'])))
		or die('La consulta fallo al buscar numero de respuestas');
		$nresp = pg_fetch_array($result, null, PGSQL_ASSOC);

		if($nresp['num_respuestas']==1){
			$result =  pg_query_params(
				$con, 
				'SELECT * FROM ratio_fallo_por_pregunta_abierta($1, $2) NATURAL JOIN preguntas;',
				array(intval($_REQUEST['min']), intval($_REQUEST['id'])))
			or die('La consulta fallo al calcular el ratio de fallo en preguntas abiertas');
		} else {

		$result =  pg_query_params(
			$con, 
			'SELECT * FROM ratio_fallo_por_pregunta($1, $2) NATURAL JOIN preguntas;',
			array(intval($_REQUEST['min']), intval($_REQUEST['id'])))
		or die('La consulta fallo al calcular el ratio de fallo por pregunta');

		}

		echo "<h2>Resultados</h2>";

		if (pg_num_rows($result) > 0) {
			echo "<table class=\"table table-hover\">";
				echo "<thead><tr>";
					echo "<th>Id pregunta</th>";
					echo "<th>Dificultad</th>";
					echo "<th>Ratio fallos</th>";
					echo "<th>Texto</th>";
				echo "</tr></thead><tbody>";

				if (pg_num_rows($result) == 0) {
					echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td><td></td></th>";
				} else {
					while ($examen = pg_fetch_array($result, null, PGSQL_ASSOC)) {
						echo "<tr><td>".$examen['id']."</td><td>".$examen['dificultad']."</td>";
						echo "<td>".$examen['ratio']."%</td><td>'".$examen['texto'].":'</td></tr>";
					}
				}

			echo "</tr></thead><tbody>";
		} else {
			echo "<p>No hay datos suficientes para esa búsqueda.</p>";
		}
	} else {
		echo "<div class=\"alert alert-danger\" role=\"alert\"><p>No tienes permisos para acceder a la información solicitada.</p></div>";
	}
	
	
?>
