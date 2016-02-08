<?php

	include 'funciones_profesor.php';

	session_start();

	if (!isset($_SESSION['profesor'])) {
		header("Location: ./index.php");
			exit;
	}

	$con = connect()
		or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	
	if (isset($_REQUEST['idMateria'])) {
		$guardado = False;
		$imagen = $_REQUEST['imagenPrincipal'] != '' ? $_REQUEST['imagenPrincipal'] : NULL;
		$audio = $_REQUEST['audioPrincipal'] != '' ? $_REQUEST['audioPrincipal'] : NULL;
			
		pg_query("BEGIN;");

		$result = pg_query_params($con,
			'INSERT INTO preguntas (dificultad, texto, id_materia, imagen, audio) VALUES ($1, $2, $3, $4, $5) RETURNING id;',
			array(intval($_REQUEST['dificultad']), $_REQUEST['titulo'], intval($_REQUEST['idMateria']), $imagen, $audio));
		
		if ($result) {
			$row = pg_fetch_array($result, null, PGSQL_ASSOC);

			$imagen = $_REQUEST['respuestaImagen1'] != '' ? $_REQUEST['respuestaImagen1'] : NULL;
			$audio = $_REQUEST['respuestaAudio1'] != '' ? $_REQUEST['respuestaAudio1'] : NULL;

			$correcto = pg_query_params($con,
				'INSERT INTO respuestas (texto, correcta, id_pregunta, imagen, audio) VALUES ($1, true, $2, $3, $4);',
				array($_REQUEST['respuesta1'], $row['id'], $imagen, $audio));

			$imagen = $_REQUEST['respuestaImagen2'] != '' ? $_REQUEST['respuestaImagen2'] : NULL;
			$audio = $_REQUEST['respuestaAudio2'] != '' ? $_REQUEST['respuestaAudio2'] : NULL;
			
			if ($correcto && isset($_REQUEST['respuesta2'])) {

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
			}

			if ($correcto) {
				$guardado = True;
				pg_query("COMMIT;");
			} else {
				pg_query("ROLLBACK;");
			}
		} else {
			pg_query("ROLLBACK;");
		}
	}
	
?>

<!DOCTYPE html>
<html>
<head>
	<title>e-valUAM 2.0 - Gestionar las preguntas</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<link rel="shortcut icon" href="favicon.png" type="image/png"/>

	<!-- bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../scripts/vendor/bootstrap-confirmation.js"></script>
</head>
<body>
	<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>

	<main class="container-fluid">

	<?php if (isset($guardado) && $guardado) { ?>
		<script type="text/javascript">
			// Borar los campos
		</script>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-success" role="alert"><p>¡Pregunta correctamente guardada!</p></div>
			</div>
		</div>
	<?php } else if (isset($guardado)) { ?>
		<div class="row">
			<div class="col-md-12">
				<div class="alert alert-danger" role="alert"><p>Se ha producido un error. Vuelve a probar más tarde.</p></div>
			</div>
		</div>
	<?php } ?>

		<div class="row">
			<div class="col-md-12">
				<form>
					<div class="form-group">
						<label class="control-label" for="idMateria">Elige una materia: </label>
						<select class="form-control" name="idMateria" onchange="updateIdMateria()">
							<?php
								$result =  pg_query_params($con, 
									'SELECT m.id AS id, m.nombre AS nombre, m.num_dificultades AS num_dificultades, m.num_respuestas AS num_respuestas
									FROM materias AS m 
										INNER JOIN profesor_por_materia AS pm ON m.id = pm.id_materia
									WHERE pm.id_alumno = $1
									ORDER BY id DESC',
									array($_SESSION['idUsuario']))
								or die('La consulta fallo: ' . pg_last_error());

								$numDificultades = array();
								$numRespuestas = array();

								while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
									if (isset($_REQUEST['idMateria']) && $_REQUEST['idMateria'] == $data['id'])
										echo "<option value=\"".$data['id']."\" selected>".$data['nombre']."</option>";
									else
										echo "<option value=\"".$data['id']."\">".$data['nombre']."</option>";
									$numDificultades[$data['id']] = $data['num_dificultades'];
									$numRespuestas[$data['id']] = $data['num_respuestas'];
								}
								
							?>
							
						</select>
						<script type="text/javascript">
							function updateIdMateria() {
								var input = $('select[name="idMateria"]  option:selected');
								$("#idMateria").val(input.val());
								$('#mostrarPreguntas').attr('href', './preguntasMostrar.php?idMateria=' + input.val() + '&nombreMateria=' + input.text());							
							}
							
						</script>
						<br>
						<a id="mostrarPreguntas" class="btn btn-primary" href="./preguntasMostrar.php" targe="_blank" role="button">Ver todas las preguntas</a>
					</div>
				</form>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-6">
				<h2>Añadir una nueva pregunta</h2>
				<form action="gestionPreguntas.php" role="form" method="post" id="mainForm" enctype="multipart/form-data">
					<input type="hidden" id="idMateria" name="idMateria">

					<div class="form-group">
						<label class="control-label" for="dificultad">Elige la dificultad: </label>
						<select class="form-control" name="dificultad">
						</select>
						<script type="text/javascript">
							<?php
								echo "var numDificultades = ". json_encode($numDificultades) . ";\n";
							?>

							var input = $('select[name="idMateria"]');

							function cambiarSelectNumDificultades()  {
								var select = $('select[name="dificultad"]');
								select.empty();
								var options = '';
								switch (parseInt(numDificultades[input.val()])) {
									case 4:
										options = "<option value=\"4\">4</option>" + options;
									case 3:
										options = "<option value=\"3\">3</option>" + options;
									case 2:
										options = "<option value=\"2\">2</option>" + options;
									case 1:
										options = "<option value=\"1\">1</option>" + options;
								}

							

								select.html(options);
							}

							function disablecheckbox()  {

								if(parseInt(numRespuestas[input.val()]) === 1){
									$('#imagenRespuestasCheckbox').removeClass('show').addClass('hidden');
									$('#audioCheckbox').removeClass('show').addClass('hidden');
									$('#imagenRespuestasCheckbox input').val(false);
									$('#audioCheckbox input').val(false);
									$('#imagenRespuestasCheckbox').attr('disabled',true);
									$('#audioCheckbox').attr('disabled',true);
									$('#divimagenRespuestasCheckbox').addClass('hidden');
									$('#divaudioCheckbox').addClass('hidden');
									$('#divparametros').removeClass('hidden');

								} else{

									$('#imagenRespuestasCheckbox').addClass('show').removeClass('hidden');
									$('#audioCheckbox').addClass('show').removeClass('hidden');
									$('#imagenRespuestasCheckbox').removeAttr('disabled');
									$('#audioCheckbox').removeAttr('disabled');
									$('#divimagenRespuestasCheckbox').removeClass('hidden');
									$('#divaudioCheckbox').removeClass('hidden');
									$('#divparametros').addClass('hidden');

								}
							
							}


							input.change(cambiarSelectNumDificultades);

							input.change(disablecheckbox);

							cambiarSelectNumDificultades();

							updateIdMateria();
							
						</script>
					</div>	

							

					<div class="checkbox">
						<label>
							<input type="checkbox" id="imagenPrincipalCheckbox" value="t">
							¿La pregunta tiene una imagen principal?
						</label>
					</div>
					
						<div class="checkbox" id="divimagenRespuestasCheckbox">
							<label>
								<input type="checkbox" id="imagenRespuestasCheckbox" value="t" >
								¿Las respuestas son imágenes?
							</label>
						</div>

						<div class="checkbox" id="divaudioCheckbox">
						  <label>
							<input type="checkbox" id="audioCheckbox" value="t">
							¿La pregunta va acompañada de audio?
						  </label>
						</div>

						
						<div class="checkbox" id="divparametros">
						  <label>
							<input type="checkbox" id="parametros" value="t" disabled>
							¿La pregunta tiene parámetros?
						  </label>
						</div>

						
					<script type="text/javascript">
						$("#imagenRespuestasCheckbox").change(function() {
							if(this.checked) {
								$('.imagenRespuesta').addClass('show')
								$('.imagenRespuesta').removeClass('hidden');
							} else {
								$('.imagenRespuesta').removeClass('show');
								$('.imagenRespuesta').addClass('hidden');
								$('.imagenRespuesta input').val('');
							}
						});

						$("#audioCheckbox").change(function() {
							if(this.checked) {
								$('.audio').addClass('show')
								$('.audio').removeClass('hidden');
							} else {
								$('.audio').removeClass('show');
								$('.audio').addClass('hidden');
								$('.audio input').val('');
							}
						});

						$("#imagenPrincipalCheckbox").change(function() {
							if(this.checked) {
								$('#imagen').addClass('show')
								$('#imagen').removeClass('hidden');
							} else {
								$('#imagen').removeClass('show');
								$('#imagen').addClass('hidden');
								$('#imagen input').val('');
							}
						});

					</script>

					<div class="form-group" id="titulo">
						<label class="control-label" for="titulo">Pregunta: </label>
						<input class="form-control" type="text" name="titulo" placeholder="Texto de la pregunta">
					</div>

					<!-- Imagen -->

					<div class="form-group hidden" id="imagen">
						<label class="control-label" for="imagenPrincipal">Imagen: </label>
						<input class="form-control" type="text" name="imagenPrincipal" placeholder="Nombre del fichero con la imagen">
					</div>

					<!-- Audio -->

					<div class="form-group audio hidden">
						<label class="control-label" for="audioPrincipal">Audio de la pregunta: </label>
						<input class="form-control" type="text" name="audioPrincipal" placeholder="Nombre del fichero con el audio">
					</div>


					<script type="text/javascript">
						<?php
							echo "var numRespuestas = ". json_encode($numRespuestas) . ";\n";
						?>

						var input = $('select[name="idMateria"]');

						function respuestaNum(num) {
							// Texto de la respuesta
							var ret = '<div class="form-group show textoRespuesta">';
							if (num == 1) {
								ret += '<label class="control-label" for="respuesta' + num + '">Respuesta correcta: </label>';
							} else {
								ret += '<label class="control-label" for="respuesta' + num + '">Respuesta #' + num + ': </label>';
							}

							ret += '<input class="form-control" type="text" name="respuesta' + num + '">';
							ret += '</div>';

							// Texto con el nombre de la imagen
							ret += '<div class="form-group hidden imagenRespuesta">';
							if (num == 1) {
								ret += '<label class="control-label" for="respuestaImagen' + num + '">Imagen respuesta correcta: </label>';
							} else {
								ret += '<label class="control-label" for="respuestaImagen' + num + '">Imagen respuesta #' + num + ': </label>';
							}
							ret += '<input class="form-control" type="text" name="respuestaImagen' + num + '" placeholder="Nombre del fichero con la imagen">';
							ret += '</div>';

							// Texto con el nombre del audio
							ret += '<div class="form-group hidden audio">';
							if (num == 1) {
								ret += '<label class="control-label" for="respuestaAudio' + num + '">Audio respuesta correcta: </label>';
							} else {
								ret += '<label class="control-label" for="respuestaAudio' + num + '">Audio respuesta #' + num + ': </label>';
							}
							ret += '<input class="form-control" type="text" name="respuestaAudio' + num + '" placeholder="Nombre del fichero con el audio">';
							ret += '</div>';

							return ret;
						}

						function cambiarZonaRespuestas()  {
							var padre = $('#zona-respuestas');
							//padre.empty();
							var html = '';
							switch (parseInt(numRespuestas[input.val()])) {
								case 5:
									html = respuestaNum(5) + html;
								case 4:
									html = respuestaNum(4) + html;
								case 3:
									html = respuestaNum(3) + html;
								case 2:
									html = respuestaNum(2) + html;
								case 1:
									html = respuestaNum(1) + html;
							}

							padre.html(html);
						}

						input.change(cambiarZonaRespuestas);

						$(document).ready(cambiarZonaRespuestas);
					</script>

					<div class="form-group" id="zona-respuestas">

					</div>


					<button type="submit" class="btn btn-primary">Guardar</button>

					<script type="text/javascript">

						$("#mainForm").submit(function( event ) {

							$("#zona-respuestas").removeClass("has-error");
							$("#titulo").removeClass("has-error");
							
							for (var i = 1; i <= numRespuestas[input.val()]; i++) {
								if ($("input[name=respuesta" + i + "]").val() === "") {
									$("#zona-respuestas").addClass("has-error");
									event.preventDefault();
								}
							}

							if ($("input[name=titulo]").val() === "") {
								$("#titulo").addClass("has-error");
								event.preventDefault();
							}
							
							return;
						});

					</script>
				</form>
			</div>
			<div class="col-lg-6">
				<h2>Editar las preguntas</h2>

				<script type="text/javascript">

					function borrarPregunta(id) {
						var r = confirm("Si borras una pregunta, dejará de estar disponible y no podrás recuperarla. ¿Deseas borrarla?");
						if (r == true) {
							$.ajax("borrarPregunta.php", {data: {idPregunta: id}, type: "POST"})
								.done(function(msg) {
									cargarTablaPreguntas();
								})
								.fail(function(jqXHR, textStatus) {
									alert("Se ha producido un error al borrar la pregunta. Prueba de nuevo más tarde.");
							});
						}
					}

					function cargarTablaPreguntas() {

						var jqxhr = $.ajax("preguntasRequest.php", {data: {idMateria: input.val()}, type: "POST"})
							.done(function(msg) {
								$("#preguntas").html(msg);
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error al cargar la tabla con las preguntas. Prueba de nuevo más tarde.");
						});
					};

					input.change(cargarTablaPreguntas);
					$(document).ready(cargarTablaPreguntas);

					function editarPregunta(id) {

						var jqxhr = $.ajax("preguntasEditarRequest.php", {data: {idMateria: input.val(), idPregunta: id}, type: "POST"})
							.done(function(msg) {
								$("#modal-body").html(msg);
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error. Prueba de nuevo más tarde.");
								$('#myModal').modal('hide');
						});
					};

					function guardarPregunta() {

						var data = {};

						data.idMateria = $('#form_edicion > #idMateria').val();
						data.idPregunta = $('#form_edicion > #idPregunta').val();
						data.dificultad = $('#form_edicion #dificultad').val();
						data.titulo = $('#form_edicion #tituloText').val();
						data.imagenPrincipal = $('#form_edicion #imagenPrincipal').val();
						data.audioPrincipal = $('#form_edicion #audioPrincipal').val();

						data.respuesta1 = $('#form_edicion #respuesta1').val();
						data.respuestaImagen1 = $('#form_edicion #respuestaImagen1').val();
						data.respuestaAudio1 = $('#form_edicion #respuestaAudio1').val();

						if ($('#form_edicion #respuesta2').length) {
						data.respuesta2 = $('#form_edicion #respuesta2').val();
						data.respuestaImagen2 = $('#form_edicion #respuestaImagen2').val();
						data.respuestaAudio2 = $('#form_edicion #respuestaAudio2').val();

						if ($('#form_edicion #respuesta3').length) {
							data.respuesta3 = $('#form_edicion #respuesta3').val();
							data.respuestaImagen3 = $('#form_edicion #respuestaImagen3').val();
							data.respuestaAudio3 = $('#form_edicion #respuestaAudio3').val();

							if ($('#form_edicion #respuesta4').length) {
								data.respuesta4 = $('#form_edicion #respuesta4').val();
								data.respuestaImagen4 = $('#form_edicion #respuestaImagen4').val();
								data.respuestaAudio4 = $('#form_edicion #respuestaAudio4').val();

								if ($('#form_edicion #respuesta5').length) {
									data.respuesta5 = $('#form_edicion #respuesta5').val();
									data.respuestaImagen5 = $('#form_edicion #respuestaImagen5').val();
									data.respuestaAudio5 = $('#form_edicion #respuestaAudio5').val();
									}
								}
							}
						}

						$.ajax("preguntaEditar.php", {data: data, type: "POST"})
							.done(function(msg) {
								if (msg == '1') {
									cargarTablaPreguntas();
									$('#myModal').modal('hide');
								}
							})
							.fail(function(jqXHR, textStatus) {
								alert("Se ha producido un error al guardar los cambios. Prueba de nuevo más tarde.");
								$('#myModal').modal('hide');
						});
					};

					function changeImagenRespuestaEdit(cb) {
						if(cb.checked) {
							$('.imagenRespuestaEdit').addClass('show')
							$('.imagenRespuestaEdit').removeClass('hidden');
						} else {
							$('.imagenRespuestaEdit').removeClass('show');
							$('.imagenRespuestaEdit').addClass('hidden');
							$('.imagenRespuestaEdit input').val('');
						}
					};

					function changeAudioEdit(cb) {
						if(cb.checked) {
							$('.audioEdit').addClass('show')
							$('.audioEdit').removeClass('hidden');
						} else {
							$('.audioEdit').removeClass('show');
							$('.audioEdit').addClass('hidden');
							$('.audioEdit input').val('');
						}
					};

					function changeImagenEdit(cb) {
						if(cb.checked) {
							$('#imagenEdit').addClass('show')
							$('#imagenEdit').removeClass('hidden');
						} else {
							$('#imagenEdit').removeClass('show');
							$('#imagenEdit').addClass('hidden');
							$('#imagenEdit input').val('');
						}
					};

				</script>

				<table id="preguntas" class="table table-hover">
				</table>

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
						<button type="button" class="btn btn-success" onclick="guardarPregunta()">Guardar</button>
					  </div>
					</div>
				  </div>
				</div>
			</div>
		</div>
	</main>	
</body>
</html>

