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
		<title>e-valUAM 2.0 - Gestión de los exámenes</title>
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
			<?php if (isset($_REQUEST['res']) && $_REQUEST['res'] == '1') { ?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-success" role="alert"><p>¡Examen correctamente guardado!</p></div>
					</div>
				</div>
			<?php } else if (isset($_REQUEST['res'])) { ?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger" role="alert"><p>Se ha producido un error. Vuelve a probar más tarde.</p></div>
					</div>
				</div>
			<?php } ?>

			<div class="row">
				<div class="col-md-6">
					<h2>Añadir un nuevo examen</h2>
					<form action="examenGuardar.php" role="form" method="post">
						<div class="form-group">
							<label class="control-label" for="nombre">Escribe el nombre del examen: </label>
							<input class="form-control" type="text" name="nombre" size="20" placeholder="Nombre del examen">
						</div>
						<div class="checkbox">
						  <label>
						    <input type="checkbox" name="disponible" value="t" checked>
						    <strong>¿Está el examen visible?</strong>
						  </label>
						</div>
						<div class="form-group">
							<label class="control-label" for="idMateria">Elige la materia de la que saldrán las preguntas: </label>
							<select class="form-control" name="idMateria">
								<?php
									$result =  pg_query_params($con,
										'SELECT m.id AS id, m.nombre AS nombre
										FROM materias AS m
											INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
										WHERE pm.id_alumno = $1
										ORDER BY id DESC',
										array($_SESSION['idUsuario']))
									or die('Error. Prueba de nuevo más tarde.')

									while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
										echo "<option value=\"".$data['id']."\">".$data['nombre']."</option>";
									}
								?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label" for="numPreguntas">Número de preguntas que tendrá el examen: </label>
							<input type="number" name="numPreguntas" min="1" value="20">
						</div>
						<div class="form-group">
							<label class="control-label" for="duracion">Tiempo máximo para resolver el examen (en minutos): </label>
							<input type="number" name="duracion" min="1" value="30">
						</div>
						<div class="checkbox">
						  <label>
						    <input type="checkbox" name="duda" value="t">
						    <strong>¿El examen acepta duda?</strong>
						  </label>
						</div>
						<div class="checkbox">
						  <label>
						    <input type="checkbox" name="feedbackExamen" value="t">
						    <strong>¿El examen muestra feedback?</strong>
						  </label>
						</div>
						<div class="form-group">
						    <label class="control-label" for="mostrarResultados">Mostrar los resultados al final: </label>
							<select class="form-control" name="mostrarResultados">
								<option value="completo">Informe detallado con la respuesta a cada pregunta y la nota final</option>
								<option value="parcial">Solo la nota</option>
								<option value="no">No</option>
							</select>
						</div>

						<button type="submit" class="btn btn-primary">Guardar</button>
					</form>
				</div>

				<div class="col-md-6">
					<h2>Editar los exámenes</h2>

					<table class="table table-hover">
						<thead><tr>
							<th>Nombre</th>
							<th>¿Visible?</th>
							<th>Materia</th>
							<th>Num preguntas</th>
							<th>Tiempo</th>
							<th>¿Duda?</th>
							<th>Resultado</th>
							<th>Opciones</th>
						</tr></thead>
						<tbody>
						<?php

							$result =  pg_query_params($con,
								'SELECT e.id AS id, e.nombre AS nombre, e.disponible AS visible, e.duracion AS tiempo,
									m.nombre AS nombre_materia, e.num_preguntas AS num_preguntas,
									e.acepta_duda AS duda, e.mostrar_resultados AS mostrar_resultados
								FROM examenes AS e
								INNER JOIN materias AS m ON e.id_materia = m.id
								INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
								WHERE pm.id_alumno = $1 AND e.borrado = FALSE
								ORDER BY e.id DESC',
								array($_SESSION['idUsuario']))
							or die('Error. Prueba de nuevo más tarde.')

							if (pg_num_rows($result) == 0) {
								echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
							} else {

								while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {

									echo "<tr><td>".$data['nombre']."</td>";

									if ($data['visible'] == 't') {
										echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>';
									} else {
										echo '<td><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>';
									}

									echo "<td>".$data['nombre_materia']."</td>";
									echo "<td>".$data['num_preguntas']."</td>";
									echo "<td>".$data['tiempo']."</td>";

									if ($data['duda'] == 't') {
										echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>';
									} else {
										echo '<td><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>';
									}

									echo "<td>".$data['mostrar_resultados']."</td>";

									echo "<td>";
										//echo "<button onClick=\"editarMateria(".$data['id'].")\" type=\"button\" class=\"btn btn-primary btn-warning\" data-toggle=\"modal\" data-target=\"#myModal\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></button>";
									echo "<button type=\"button\" onClick=\"borrarExamen(".$data['id'].")\" class=\"btn btn-danger\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></button>";
									echo "</td></tr>";

								}
							}
						?>
						</tbody>
					</table>
				</div>
			</div>

			<script type="text/javascript">
				function borrarExamen(id) {
					var r = confirm("Si borras un examen, dejará de estar disponible y no podrás recuperarlo. ¿Deseas borrarlo?");
					if (r == true) {
						$.ajax("examenBorrar.php", {data: {idExamen: id}, type: "POST"})
							.done(function(msg) {
								if (msg == '1')
									location.replace("./gestionExamen.php");
								else
									alert("Se ha producido un error. Prueba de nuevo más tarde.");
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error. Prueba de nuevo más tarde.");
						});
					}
				}
			</script>

			<!-- Modal -->
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			  <div class="modal-dialog">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			        <h4 class="modal-title" id="myModalLabel">Editar</h4>
			      </div>
			      <div class="modal-body" id="modal-body">
			      <p>Funcionalidad aún no implementada</p>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar sin guardar</button>
			        <button type="button" class="btn btn-success" onclick="guardarExamen()">Guardar cambios</button>
			      </div>
			    </div>
			  </div>
			</div>
		</main>
	</body>
</html>
