<?php

	/*
	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');


	// Visualizado de la tabla con todas las preguntas de una materia

	$result =  pg_query($con, 
		'SELECT id, imagen, dificultad, texto, id_materia, audio, feedback
		FROM preguntas
		WHERE id_materia = 12 AND borrada = FALSE')
	or die('La consulta fallo: ' . pg_last_error());

	while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {

		$resultInsert = pg_query_params($con,
			'INSERT INTO preguntas (imagen, dificultad, texto, id_materia, audio, feedback)
			VALUES ($1, $2, $3, 14, $4, $5) RETURNING id',
			array($data['imagen'], $data['dificultad'], $data['texto'], $data['audio'], $data['feedback']));

		if (!$resultInsert) {
			echo pg_last_error();
			break;
		}

		$row = pg_fetch_array($resultInsert, null, PGSQL_ASSOC);
		$id = $row['id'];

		$resultRespuesta =  pg_query_params($con, 
			'SELECT *
			FROM respuestas
			WHERE id_pregunta = $1',
			array($data['id']))
		or die('La consulta fallo: ' . pg_last_error());

		while ($dataRespuesta = pg_fetch_array($resultRespuesta, null, PGSQL_ASSOC)) {

			$insert = pg_query_params($con,
			'INSERT INTO respuestas (texto, correcta, imagen, audio, id_pregunta)
			VALUES ($1, $2, $3, $4, $5)',
			array($dataRespuesta['texto'], $dataRespuesta['correcta'], $dataRespuesta['imagen'], $dataRespuesta['audio'], $id));

			if (!$insert) {
				echo pg_last_error();
				break;
			}
		
		}
	
	}
	*/
?>