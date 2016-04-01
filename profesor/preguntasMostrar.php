<!--
		e-valUAM: An adaptive questionnaire environment.
		e-valUAM: Un entorno de questionarios adaptativos.

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

	include 'funciones_profesor.php';

	check_login();

	if (!isset($_REQUEST['idMateria']) || !isset($_REQUEST['nombreMateria'])) {
		die();
	}

	$con = connect()
		or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');


	$result = pg_query_params($con,
		'SELECT *
		FROM profesor_por_materia
		WHERE id_alumno = $1 AND id_materia = $2',
		array(intval($_SESSION['idUsuario']), intval($_REQUEST['idMateria'])));

	if (pg_num_rows($result) != 1) {
		die(pg_num_rows($result));
	}


	$result = pg_query_params($con,
		'SELECT *
		FROM preguntas
		WHERE id_materia = $1 AND borrada = FALSE',
		array(intval($_REQUEST['idMateria'])));

	$preguntas = [];

	while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		$preguntas[] = [
			'id' => $data['id'],
			'texto' => $data['texto'],
			'dificultad' => $data['dificultad'],
			'audio' => $data['audio'],
			'imagen' => $data['imagen']
		];
	}

	$result = pg_query_params($con,
		'SELECT r.*
		FROM respuestas AS r
		INNER JOIN preguntas AS p ON r.id_pregunta = p.id
		WHERE p.id_materia = $1 AND p.borrada = FALSE',
		array(intval($_REQUEST['idMateria'])));

	$respuestas = [];

	while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		$respuestas[] = [
			'id' => $data['id'],
			'idPregunta' => $data['id_pregunta'],
			'texto' => $data['texto'],
			'correcta' => $data['correcta'],
			'audio' => $data['audio'],
			'imagen' => $data['imagen']
		];
	}

	//echo print_r($respuestas);

?>

<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Visor de examen</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="../favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>
		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Preguntas de la materia #<?php echo $_REQUEST['idMateria']; ?> - <?php echo $_REQUEST['nombreMateria']; ?></h1>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div id="content"></div>
				</div>
			</div>

			<script type="text/javascript">
				var idMateria = <?php echo $_REQUEST['idMateria']; ?>;

				function formatoPregunta(pregunta) {
					var ret = "<h1>[Preg. #" + pregunta.id + " - Dificultad " + pregunta.dificultad + "] " + pregunta.texto + "</h1>";

					if (pregunta.imagen) {
						ret += ("<img class=\"img-responsive\"  id=\"imagen\" src=\"../multimedia/" + idMateria + "/"+pregunta.imagen+"\"/>");
					}

					if (pregunta.audio) {
						ret += '<audio controls preload="auto">';
						ret += ('<source src="../multimedia/' + idMateria + '/' + pregunta.audio + '" controls></source>');
						ret += 'Tu navegador no soporta audio. Por favor, actualiza <a href="http://browsehappy.com/">a un navegador más moderno.</a></audio>';
					}

					return ret;
				}

				function formatoRespuestas(id, respuestas) {
					var buenas = respuestas.filter(function (r) {return r.idPregunta === id;});

					console.log(id, respuestas, buenas);

					var ret = "";

					var arrayLength = buenas.length;

					for (var i = 0; i < arrayLength; i++) {
						var r = buenas[i];

						ret += '<h3>';

						if (r.correcta == 't')
							ret += '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ';
						else
							ret += '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> ';

						ret += r.texto + '</h3>';

						if (r.imagen) {
							ret += "<img class=\"img-responsive\"  id=\"imagen\" src=\"../multimedia/" + idMateria + "/"+r.imagen+"\"/>";
						}

						if (r.audio) {
							ret += '<audio controls>';
							ret += ('<source src="../multimedia/' + idMateria + '/' + r.audio + '" type="audio/mpeg"></source>');
							ret += 'Tu navegador no soporta audio. Por favor, actualiza <a href="http://browsehappy.com/">a un navegador más moderno.</a></audio>';
						}
					}

					return ret;
				}

				var preguntas = <?php echo json_encode($preguntas); ?>;
				var respuestas = <?php echo json_encode($respuestas); ?>;

				var html = "";

				preguntas.sort(function (a, b) {return a.id - b.id;});

				var arrayLength = preguntas.length;

				for (var i = 0; i < arrayLength; i++) {
				    html += formatoPregunta(preguntas[i]);
				    html += formatoRespuestas(preguntas[i].id, respuestas);
				}

				$('#content').html(html);

			</script>

		</main>
	</body>
</html>
