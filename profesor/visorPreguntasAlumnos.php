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

	$con = connect()
    	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (isset($_REQUEST['alumno'])) { // Se trata de la llamada AJAX
		$result =  pg_query_params($con,
			'SELECT texto, respuesta1, respuesta2, respuesta3, respuestaok, imagen, dificultad
			FROM preguntas_alumnos
			WHERE id_alumno = $1
			ORDER BY dificultad',
                        array($_REQUEST['alumno']))
		or die('Error. Prueba de nuevo más tarde.')

		if (pg_num_rows($result) == 0) {
			echo "<p>No hay datos para mostrar.</p>";
                } else {
			while ($pregunta = pg_fetch_array($result, null, PGSQL_ASSOC)) {
				echo '<div class="row">';
				echo '<div class="col-md-12">';
				echo '<h1>' . $pregunta['texto'] . '</h1>';
				if ($pregunta['imagen'] != NULL) {
					echo '<img src="../multimedia/alumnos/' . $_REQUEST['alumno'] . '/' . rawurlencode($pregunta['imagen']) . '" class="img-responsive" alt="Imagen asociada a la pregunta">';
				}
				echo '<p><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><span class="sr-only">Correcta:</span> ' . $pregunta['respuestaok'] . '</p>';
				echo '<p><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="sr-only">Incorrecta:</span> ' . $pregunta['respuesta1'] . '</p>';
				echo '<p><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="sr-only">Incorrecta:</span> ' . $pregunta['respuesta2'] . '</p>';
				echo '<p><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="sr-only">Incorrecta:</span> ' . $pregunta['respuesta3'] . '</p>';
 				echo '<p><strong>Dificultad ' . $pregunta['dificultad'] . '</strong></p>';
				echo '</div></div>';
			}
		}
		exit;
	}
?>


<html>
	<head>
		<title>e-valUAM 2.0 - Zona del profesor</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script type="text/javascript">
			function cargarPreguntas(id) {
				$('#errorMessage').removeClass('show').addClass('hidden');
				$.ajax('visorPreguntasAlumnos.php?alumno=' + id)
				.done(function(data) {
					$('#preguntasAlumno').html(data);
				})
				.fail(function() {
					$('#errorMessage').removeClass('hidden').addClass('show');
				})
			}
		</script>
	</head>

	<body>
		<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Visor preguntas</h1>
					<p>En esta página se pueden ver las preguntas que ha mandado cada alumno.</p>
					<p>Selecciona el nombre de un alumno y a continuación verás sus preguntas.</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 hidden" id="errorMessage"><div class="alert alert-danger" role="alert"><strong>Ups...</strong> Algo ha salido mal. Prueba de nuevo más tarde.</div></div>
			</div>
			<div class="row" id="seleccionAlumno">
				<div class="col-md-6">
					<form>
						<h2>Alumnos que han enviado preguntas</h2>
						<table class="table table-hover">
							<thead><tr>
								<th>Nombre alumno</th>
								<th>Seleccionar</th>
							</tr></thead>
							<tbody>
							<?php

								$result =  pg_query_params($con,
									'SELECT DISTINCT(a.nombre) AS nombre, a.id
									FROM alumnos AS a
									INNER JOIN preguntas_alumnos AS pa ON pa.id_alumno = a.id
									ORDER BY a.id',
									array())
								or die('Error. Prueba de nuevo más tarde.')

								if (pg_num_rows($result) == 0) {
									echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td></tr>";
								} else {
									while ($alumno = pg_fetch_array($result, null, PGSQL_ASSOC)) {
										echo "<tr><td>".$alumno['nombre']."</td><td><input type=\"radio\" name=\"idAlumno\" value=\"".$alumno['id']."\" onclick=\"cargarPreguntas(".$alumno['id'].")\"></td></tr>";
									}
								}
							?>
							</tbody>
						</table>
					</form>
				</div>
				<div class="col-md-6" id="preguntasAlumno">
				</div>
			</div>
		</main>
	</body>
</html>
