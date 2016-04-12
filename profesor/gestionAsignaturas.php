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


	if (isset($_REQUEST['nombreAsignatura'])) {
		$guardado = False;

		if (0) {

			//$feedback = ($_REQUEST['feedback'] == 't' ? 't' : 'f');

			pg_query("BEGIN;");

			$result = pg_query_params($con,
				'INSERT INTO materias (nombre, num_dificultades, num_respuestas) VALUES ($1, $2, $3) RETURNING id;',
				array($_REQUEST['nombreMateria'], $_REQUEST['numDificultades'], $_REQUEST['numPreguntas']));

			if ($result) {
				$row = pg_fetch_array($result, null, PGSQL_ASSOC);

				$result = pg_query_params($con,
					'INSERT INTO profesor_por_materia (id_materia, id_alumno) VALUES ($1, $2);',
					array($row['id'], $_SESSION['idUsuario']));

				if ($result) {
					$guardado = True;
					pg_query("COMMIT;");
				} else {
					pg_query("ROLLBACK;");
				}
			} else {
				pg_query("ROLLBACK;");
			}
		}
	}

?>

<!DOCTYPE html>
<html>
<head>
	<title>e-valUAM 2.0 - Gestionar las Asignaturas</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<link rel="shortcut icon" href="favicon.png" type="image/png"/>

	<!-- bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
</head>
<body>
	<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>

	<main class="container-fluid">

	<!-- Mensaje OK / Error -->
	<?php if (isset($guardado) && $guardado) { ?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-success" role="alert"><p>¡Asignatura correctamente guardada! <a class="alert-link" href="gestionMaterias.php">Comienza a añadir materias.</a></p></div>
			</div>
		</div>
	<?php } else if (isset($guardado)) { ?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-danger" role="alert"><p>Se ha producido un error. Vuelve a probar más tarde.</p></div>
			</div>
		</div>

	<?php } ?>
		<!-- Scripts -->
		<script type="text/javascript">
			$(function () {$('[data-toggle="popover"]').popover()})
		</script>


		<!-- Registro asignaturas -->
		<div class="row">
			<div class="col-md-6">
				<h2>Añadir una nueva asignatura</h2>
				<form action="gestionAsignaturas.php" role="form" method="post">

					<div class="form-group">
						<label class="control-label" for="nombreAsignatura">Nombre de la asignatura: <span class="glyphicon glyphicon-question-sign" data-toggle="popover" title="Asignaturas" 
						data-content="Las asignaturas permiten agrupar materias y permitir que alumnos se inscriban a ellas para visualizar los exámenes. Para regular el acceso es posible establecer contraseñas" data-trigger="click hover">
						<span class="sr-only">Información</span>
						</span> </label>
						<input class="form-control" type="text" name="nombreAsignatura" size="20" placeholder="Nombre de la asignatura" required>
					</div>

					<div class="form-group">
						<label class="control-label" for="descripcionAsignatura">Breve descripcion pública: </label>
				<textarea class="form-control" name="descripcionAsignatura" row="3" style="resize:vertical" placeholder="Descripcion de la asignatura" required></textarea/>
					</div>

					<div class="form-group">
						<label class="control-label" for="passAsignatura">Contraseña (Opcional) </label>
						<input class="form-control" type="password" name="passAsignatura" size="20" placeholder="Contraseña">
					</div>

					<div class="form-group">
						<label class="control-label" for="passAsignatura2">Repita la contraseña </label>
						<input class="form-control" type="password" name="passAsignatura2" size="20" placeholder="Repita la contraseña">
					</div>

					<button type="submit" id="btn-load" class="btn btn-primary">Guardar</button>
				</form>
			</div>
			<div class="col-md-6">
				<h2>Editar Asignaturas</h2>

				<table class="table table-hover">
					<thead><tr>
						<th>Nombre de la asignatura</th>
						<th>Contraseña habilitada</th>
						<th>Número de participantes</th>
						<th>Opciones</th>
					</tr></thead>
					<tbody>
					<?php

						$result =  pg_query_params($con,
							'SELECT m.id AS id, m.nombre AS nombre, m.num_dificultades AS num_dificultades,
								m.num_respuestas AS num_respuestas, COUNT(*) AS count, m.acepta_feedback AS feedback
							FROM materias AS m
								INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
								LEFT JOIN preguntas AS p ON p.id_materia = m.id
							WHERE pm.id_alumno = $1
							GROUP BY m.id, m.nombre, m.num_dificultades, m.num_respuestas, m.acepta_feedback
							ORDER BY id DESC',
							array($_SESSION['idUsuario']))
						or die('Error. Prueba de nuevo más tarde.');

						if (pg_num_rows($result) == 0) {
							echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td><td></td><td></td></tr>";
						} else {
							while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
								echo "<tr><td>".$data['nombre']."</td>";
								echo "<td>".$data['num_dificultades']."</td>";

								if($data['num_respuestas'] != 1)
									echo "<td>".$data['num_respuestas']."</td>";
								else
									echo "<td>Respuesta abierta</td>";
								//echo "<td>".($data['count'] != "1" ? $data['count'] : 0)."</td>";
								/*
								if ($data['feedback'] == 't') {
									echo '<td><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></td>';
								} else {
									echo '<td><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></td>';
								}
								*/

								echo "<td>";
									echo "<button onClick=\"editarMateria(".$data['id'].")\" type=\"button\" class=\"btn btn-primary btn-warning\" data-toggle=\"modal\" data-target=\"#myModal\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></button>";
									// if ($data['count'] > 0) {
									// 	echo "<div data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"No podrás borrar esta materia hasta que no borres todas su preguntas\">";
									// 	echo "<button type=\"button\" onClick=\"borrarPregunta(".$data['id'].")\" class=\"btn btn-danger\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></button>";
									// 	echo "</div>";
									// } else {
									// 	echo "<button type=\"button\" class=\"btn btn-danger\">Borrar</button>";
									// }
								echo "</td></tr>";
							}
						}
					?>
					</tbody>
				</table>

		<script type="text/javascript">
				var nueva1 = $("input[name='passAsignatura']");
				var nueva2 = $("input[name='passAsignatura2']");

				function checkContrasenyas() {

					if (nueva1.val() == nueva2.val()) {
						$("button[value='Continuar']").removeAttr("disabled");
						if (!$("#mensaje").hasClass("hidden"))
							$("#mensaje").addClass("hidden");
					} else {
						if (!$("button[value='Continuar']").attr("disabled"))
							$("button[value='Continuar']").attr("disabled", "disabled");
						$("#mensaje").removeClass("hidden");
					}
				}

				nueva1.keyup(checkContrasenyas);
				nueva2.keyup(checkContrasenyas);

				$(function(){
					var $btn = $(':button');
					$btn.click(function(){
						var $this = $(this);
						$this.attr('disabled', 'disabled').html("Cargando...");
						setTimeout(function () {
							$this.removeAttr('disabled').html('Continuar');
						}, 3000)
					});
				})
		</script>

				<script type="text/javascript">
					function editarMateria(id) {

						var jqxhr = $.ajax("materiasRequest.php", {data: {idMateria: id}, type: "POST"})
							.done(function(msg) {
								$("#modal-body").html(msg);
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error. Prueba de nuevo más tarde.");
								$('#myModal').modal('hide');
						});
					};

					function guardarMateria() {
						var idMateria = $('#form_edicion > #idMateria').val();
						var nombreMateria = $('#form_edicion #nombreMateria').val();
						var numDificultades = $('#form_edicion #numDificultades').val();
						var numPreguntas = $('#form_edicion #numPreguntas').val();

						$.ajax("materiasRequest.php", {data:
								{idMateria: idMateria,
									nombreMateria: nombreMateria,
									numDificultades: numDificultades,
									numPreguntas: numPreguntas
								}, type: "POST"})
							.done(function(msg) {
								if (msg == 'Ok')
									location.reload();
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error al guardar los cambios. Prueba de nuevo más tarde.");
								$('#myModal').modal('hide');
						});
					};
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
				        <button type="button" class="btn btn-success" onclick="guardarMateria()">Guardar cambios</button>
				      </div>
				    </div>
				  </div>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
