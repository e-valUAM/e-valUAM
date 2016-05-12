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
		<script>
			function loadXMLDocAlumnos(num, ma_id) {
				var xmlhttp;
				if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else { // code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("seleccionAlumno").innerHTML = xmlhttp.responseText;
					}
				}

				xmlhttp.open("post", "profesorRequest.php", true);
		        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		        xmlhttp.send("tipo=alumnos&id=" + num +"&ma_id=" + ma_id);
			}

			function loadXMLDocDatos(num, name, time, idExamen, idMateria) {
				window.open('profesorRequest.php?tipo=datos&idExamen=' + idExamen + '&time=' + time + '&name=' + name + '&id=' + num + '&ma_id=' + idMateria).focus();
			}
		</script>
	</head>

	<body>
		<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Recuperar exámenes</h1>
					<p>En esta página se pueden ver las respuestas que ha dado cada alumno para cada examen.</p>
					<p>Primero deberas seleccionar un examen y a continuación, en la nueva tabla que aparecerá, un alumno.</p>
					<p>En una ventana nueva se abrirá su examen mostrando todas sus respuestas.</p>
					<p>Si quieres ver qué preguntas se han fallado más, <a href="./estadisticas.php">pulsa aquí.</a></p>
				</div>
			</div>
			<div class="row" id="seleccionExamen">
				<div class="col-md-6">
					<form>
						<h2>Exámenes disponibles</h2>
						<table class="table table-hover">
							<thead><tr>
								<th>Nombre examen</th>
								<th>Nombre materia</th>
								<th>Seleccionar</th>
							</tr></thead>
							<tbody>
							<?php
							if($_SESSION['admin'] == 't'){

								$result =  pg_query($con,
									'SELECT ex.nombre AS nombre_ex, ma.nombre AS nombre_ma, ex.id AS id, ma.id AS ma_id
									FROM examenes AS ex
									INNER JOIN materias AS ma ON ex.id_materia = ma.id
									INNER JOIN profesor_por_materia AS pm ON ma.id = pm.id_materia')
								or die('Error. Prueba de nuevo más tarde.');

							} else {

								$result =  pg_query_params($con,
									'SELECT ex.nombre AS nombre_ex, ma.nombre AS nombre_ma, ex.id AS id, ma.id AS ma_id
									FROM examenes AS ex
									INNER JOIN materias AS ma ON ex.id_materia = ma.id
									INNER JOIN profesor_por_materia AS pm ON ma.id = pm.id_materia
									WHERE pm.id_alumno = $1',
									array($_SESSION['idUsuario']))
								or die('Error. Prueba de nuevo más tarde.');
							}

								if (pg_num_rows($result) == 0) {
									echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td></tr>";
								} else {
									while ($examen = pg_fetch_array($result, null, PGSQL_ASSOC)) {
										echo "<tr><td>".$examen['nombre_ex']."</td><td>".$examen['nombre_ma']."</td><td><input type=\"radio\" name=\"idExamen\" value=\"".$examen['id']."\" onclick=\"loadXMLDocAlumnos(this.value, ".$examen['ma_id'].")\"></td></tr>";
									}
								}
							?>
							</tbody>
						</table>
					</form>02-09 11:22:41+01
				</div>
				<div class="col-md-6" id="seleccionAlumno">
				</div>
			</div>
		</main>
	</body>
</html>
