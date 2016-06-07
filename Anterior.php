<!--
		e-valUAM: An adaptive questionnaire environment.
    Copyright (C) 2011-2016
		P. Molins, P. Marcos with P. Rodríguez, F. Jurado & G. M. Sacha.
		Contact email: pablo.molins@uam.es


		This file is part of e-valUAM.

    e-valUAM is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published
		by the Free Software Foundation, either version 3 of the License, or
    any later version.

    e-valUAM is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with e-valUAM.  If not, see <http://www.gnu.org/licenses/>.
-->

<?php

	include 'funciones.php';
	session_start();

	/* Caso de error */
	if(!isset($_SESSION['id_pregunta_anteanterior'])){
		set_mensaje('error', 'Error al mostrar pregunta respondida anteriormente');
		header("Location: ./eleccionExamen.php");
	   	exit;

	} else {

		$con = connect();
		// Query para buscar pregunta anterior
		$result =  pg_query_params($con,
			'(SELECT texto, imagen, audio, parametros FROM preguntas WHERE id = $1)',
			array($_SESSION['id_pregunta_anteanterior']));

		// Caso de Error en la Query o en la Conexion
		if (pg_num_rows($result) == 0) {
			set_mensaje('error', 'Error al mostrar pregunta respondida anteriormente');
			header("Location: ./eleccionExamen.php");
			exit;
		}

		$pregunta = pg_fetch_array($result, NULL, PGSQL_ASSOC);
		pg_free_result($result);

		// Query para buscar Respuesta anterior
		if($_SESSION['num_respuestas'] == 1){ //Respuesta Abierta

			if($pregunta['parametros']){ //Parametrica
				//Sustituimos los parametros que salieron en la pregunta

				$params =  pg_query_params($con,
					'SELECT valor FROM parametros_por_alumno AS pa 
					 INNER JOIN parametros AS p 
					 ON pa.id_parametro = p.id 
					 WHERE id_pregunta = $1 AND id_alumno_examen = $2
					 ORDER BY orden ASC;',
					array($_SESSION['id_pregunta_anteanterior'],$_SESSION['idAlumnoExamen']));


				//Sustituimos parametros salidos
				for ($i = 1; $parametros = pg_fetch_array($params, null, PGSQL_ASSOC); $i++) {
					$pregunta = str_replace("$".$i, $parametros['valor'], $pregunta);
				}

				$result =  pg_query_params($con,
					'SELECT respuesta, respuesta=respuesta_correcta AS correcta 
					 FROM respuestas_abiertas 
					 WHERE id_alumno_examen = $2 AND id_pregunta = $1;',
					array($_SESSION['id_pregunta_anteanterior'],$_SESSION['idAlumnoExamen']));

			} else { //Normal

				$result =  pg_query_params($con,
					'(SELECT respuesta, respuesta=texto AS correcta
						FROM respuestas_abiertas NATURAL JOIN respuestas
						WHERE id_pregunta = $1 and id_alumno_examen = $2)',
					array($_SESSION['id_pregunta_anteanterior'],$_SESSION['idAlumnoExamen']));


			}
			
		} else { // Respuesta Test

			$result =  pg_query_params($con,
				'(SELECT texto as respuesta, correcta
					FROM respuestas_por_alumno INNER JOIN respuestas ON id_respuesta = id
					WHERE id_pregunta = $1 AND id_alumno_examen = $2)',
				array($_SESSION['id_pregunta_anteanterior'],$_SESSION['idAlumnoExamen']));
		}

		if (pg_num_rows($result) == 0) { //Respuesta no encontrada

			$respuesta['respuesta'] = 'No se ha podido encontrar el texto de su respuesta';
			$respuesta['correcta'] = 'f';

		} else {

		$respuesta = pg_fetch_array($result, NULL, PGSQL_ASSOC);
		$tipo = ($respuesta['correcta']=='t') ? "correcta" : "incorrecta";

		}
	}
?>


<html>
	<head>
		<title>e-valUAM 2.0</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="estilo.css">
		<link rel="shortcut icon" href="favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>
		<?php mostrar_header(); ?>
		<main class="container-fluid">
			<div class="row">
				<!-- Pregunta -->
				<div class="col-md-12">
					<?php
						echo "<h1 class=\"activaAudioPrincipal\" id=\"textoPregunta\">".$pregunta['texto']."</h1>";

						if (strlen($pregunta['imagen']) >= 5) {
							echo "<img class=\"img-responsive activaAudioPrincipal\" id=\"imagen\" src=\"./multimedia/".$_SESSION['materias_id']."/".$pregunta['imagen']."\"/>";
						}

						if (isset($pregunta['audio'])) {
							echo "<audio controls preload=\"auto\" id=\"audioPrincipal\">";
							echo "<source src=\"./multimedia/".$_SESSION['materias_id']."/".$pregunta['audio']."\" type=\"audio/mpeg\">";
							echo "Tu navegador no soporta audio. Actualiza <a href=\"http://browsehappy.com/\">a un navegador más moderno.</a>";
							echo "</audio>";
						}
					?>
				</div>
				<!-- Respuesta -->
				<div class="col-md-12">
					<?php
						echo "<p class='".$tipo."'>Tu respuesta: ".$respuesta['respuesta']."</p>";
					?>
				</div>
				<div class="col-md-12">
					<button class="btn btn-primary" onclick="window.close();">Cerrar</button>
				</div>
			</div>
		</main>
	</body>
</html>
