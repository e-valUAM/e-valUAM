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

	session_start();

	if (!isset($_SESSION['profesor'])) {
		header("Location: ./index.php");
			exit;
	}

	$con = connect()
		or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

?>
<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Gestión ficheros multimedia</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="../favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>

	<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>
	<main class="container-fluid">
		<?php if (isset($_REQUEST['exito'])) { ?>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-success" role="alert"><p>¡Fichero subido con éxito!</p></div>
				</div>
			</div>
		<?php } else if (isset($_REQUEST['error'])) { ?>
			<div class="row">
				<div class="col-md-12">
					<div class="alert alert-danger" role="alert"><p>Se ha producido un error. Vuelve a probar más tarde.</p></div>
				</div>
			</div>
		<?php } ?>

		<div class="row">
			<div class="col-md-12">
				<h1>Subida de ficheros</h1>
				<p>Si has creado preguntas que lleven audio o imágenes, tendrás que subir los ficheros antes de que las preguntas se puedan mostrar correctamente.</p>
				<p>Elige a qué materia pertenece el fichero y el propio fichero.</p>
				<p><strong>Si ya existe un fichero con el mismo nombre en la materia elegida, el fichero anterior se borrará.</strong></p>
				<p>Puedes subir imágenes con extensión <samp>gif</samp>, <samp>png</samp>, <samp>jpeg</samp> o <samp>jpg</samp>. Puedes subir audio con extensión <samp>mp3</samp>.</p>
				<p><strong>Evita usar caracteres o símbolos no pertencientes al alfabeto inglés al poner nombre a tus ficheros. La presencia de ñ, acentos, ü, ç o cualquier otro hará que tu fichero no se muestre correctamente.</strong></p>
				<br>

				<form action="multimediaRequest.php" role="form" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label class="control-label" for="idMateria">Elige una materia: </label>
						<select class="form-control" name="idMateria">
							<?php
								$result =  pg_query_params($con,
									'SELECT m.id AS id, m.nombre AS nombre, m.num_dificultades AS num_dificultades, m.num_respuestas AS num_respuestas
									FROM materias AS m
										INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
									WHERE pm.id_alumno = $1
									ORDER BY id DESC',
									array($_SESSION['idUsuario']))
								or die('Error. Prueba de nuevo más tarde.');

								while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
									echo "<option value=\"".$data['id']."\">".$data['nombre']."</option>";
								}
							?>
						</select>
					</div>

					<div class="form-group">
						<label class="control-label" for="fichero">Fichero a subir: </label>
						<input type="file" name="fichero" accept="image/png,image/gif,image/jpeg,audio/mpeg">
					</div>

					<button type="submit" class="btn btn-primary">Subir</button>
				</form>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12">
				<h1>Visor de ficheros</h1>
				<p>Elige una materia y más abajo aparecerán todos los ficheros multimedia asociados con dicha materia.</p>
				<!-- <p>Si borras alguno, tendrás que volver a subirlo si quieres que vuelva a estar disponible.</p> -->
				<form>
					<div class="form-group">
						<label class="control-label" for="idMateria2">Elige una materia: </label>
						<select class="form-control" name="idMateria2" onchange="updateContent()">
							<?php
								$result =  pg_query_params($con,
									'SELECT m.id AS id, m.nombre AS nombre
									FROM materias AS m
										INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
									WHERE pm.id_alumno = $1
									ORDER BY id DESC',
									array($_SESSION['idUsuario']))
								or die('Error. Prueba de nuevo más tarde.');

								while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
									echo "<option value=\"".$data['id']."\">".$data['nombre']."</option>";
								}
							?>
						</select>
					</div>
				</form>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div id="content">
				</div>
			</div>
		</div>

		<script type="text/javascript">
			function updateContent () {
				console.log($('select[name=idMateria2] option:selected').val());

				var jqxhr = $.ajax("multimediaView.php", {data: {idMateria: $('select[name=idMateria2] option:selected').val()}, type: "POST"})
					.done(function(msg) {
						$("#content").html(msg);
					})
					.fail(function(jqXHR, textStatus) {
						alert("Se ha producido un error al cargar los archivos. Prueba de nuevo más tarde.");
				});
			}
			updateContent();
		</script>
	</main>
	</body>
</html>
