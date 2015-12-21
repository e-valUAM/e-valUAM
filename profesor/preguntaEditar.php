<?php 
	
	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (!isset($_REQUEST['idMateria'])) {
		echo "01";
		die();
	}

	if (isset($_REQUEST['idPregunta'])) { // Actualización de una pregunta
		$guardado = False;
		$imagen = $_REQUEST['imagenPrincipal'] != '' ? $_REQUEST['imagenPrincipal'] : NULL;
		$audio = $_REQUEST['audioPrincipal'] != '' ? $_REQUEST['audioPrincipal'] : NULL;
			
		pg_query("BEGIN;");

		//print_r(array(intval($_REQUEST['dificultad']), $_REQUEST['titulo'], intval($_REQUEST['idMateria']), $imagen, $audio, intval($_REQUEST['idPregunta'])));

		$result = pg_query_params($con,
			'INSERT INTO preguntas (dificultad, texto, id_materia, imagen, audio, id_antigua) VALUES ($1, $2, $3, $4, $5, $6) RETURNING id;',
			array(intval($_REQUEST['dificultad']), $_REQUEST['titulo'], intval($_REQUEST['idMateria']), $imagen, $audio, intval($_REQUEST['idPregunta'])));
		
		if ($result) {
			$row = pg_fetch_array($result, null, PGSQL_ASSOC);

			$imagen = $_REQUEST['respuestaImagen1'] != '' ? $_REQUEST['respuestaImagen1'] : NULL;
			$audio = $_REQUEST['respuestaAudio1'] != '' ? $_REQUEST['respuestaAudio1'] : NULL;

			$correcto = pg_query_params($con,
				'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, true, $2, $3, $4);',
				array($_REQUEST['respuesta1'], $row['id'], $imagen, $audio));

			$imagen = $_REQUEST['respuestaImagen2'] != '' ? $_REQUEST['respuestaImagen2'] : NULL;
			$audio = $_REQUEST['respuestaAudio2'] != '' ? $_REQUEST['respuestaAudio2'] : NULL;

			$result2 = pg_query_params($con,
				'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, false, $2, $3, $4);',
				array($_REQUEST['respuesta2'], $row['id'], $imagen, $audio));

			$correcto = $correcto && $result2;

			if ($correcto && isset($_REQUEST['respuesta3'])) {

				$imagen = $_REQUEST['respuestaImagen3'] != '' ? $_REQUEST['respuestaImagen3'] : NULL;
				$audio = $_REQUEST['respuestaAudio3'] != '' ? $_REQUEST['respuestaAudio3'] : NULL;

				$result3 = pg_query_params($con,
					'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, false, $2, $3, $4);',
					array($_REQUEST['respuesta3'], $row['id'], $imagen, $audio));

				$correcto = $result3;

				if ($correcto && isset($_REQUEST['respuesta4'])) {

					$imagen = $_REQUEST['respuestaImagen4'] != '' ? $_REQUEST['respuestaImagen4'] : NULL;
					$audio = $_REQUEST['respuestaAudio4'] != '' ? $_REQUEST['respuestaAudio4'] : NULL;

					$result4 = pg_query_params($con,
						'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, false, $2, $3, $4);',
						array($_REQUEST['respuesta4'], $row['id'], $imagen, $audio));

					$correcto = $result4;

					if ($correcto && isset($_REQUEST['respuesta5'])) {

						$imagen = $_REQUEST['respuestaImagen5'] != '' ? $_REQUEST['respuestaImagen5'] : NULL;
						$audio = $_REQUEST['respuestaAudio5'] != '' ? $_REQUEST['respuestaAudio5'] : NULL;

						$result5 = pg_query_params($con,
							'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, false, $2, $3, $4);',
							array($_REQUEST['respuesta5'], $row['id'], $imagen, $audio));

						$correcto = $result5;
					}
				}
			}

			if ($correcto) {
				$guardado = True;
					$result =  pg_query_params($con, 
					'UPDATE preguntas
					SET borrada = TRUE
					WHERE id = $1',
					array($_REQUEST['idPregunta']));
				
				if (!$result) {
					echo "02";
					pg_query("ROLLBACK;");
					die();
				}

				echo "1";
				pg_query("COMMIT;");
				die();

			} else {
				pg_query("ROLLBACK;");
				echo "03";
			}
		} else {
			pg_query("ROLLBACK;");
			echo "04";
		}
	}
?>