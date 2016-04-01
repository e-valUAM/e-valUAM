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

	include 'funciones.php';

	session_start();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');
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
                        <!-- Alertas de estado -->
                	<div id="alerta_Ok" class="row hidden"><div class="col-md-12">
			<div class="alert alert-success" role="alert">
        	                <strong>Tus preguntas se han guardado.</strong> Ya puedes volver a la <a href="./eleccionExamen.php" class="alert-link">página princial.</a>
	                </div>
			</div></div>

                	<div id="alerta_MalUser" class="row hidden"><div class="col-md-12">
                	<div class="alert alert-warning" role="alert">
        	                <strong>¡Ups!</strong> Te falta algún campor por rellenar. Por favor, revisa las preguntas y vuelve a enviarlas.
	                </div>
			</div></div>

                	<div id="alerta_MalServer" class="row hidden"><div class="col-md-12">
                	<div class="alert alert-danger" role="alert">
        	                <strong>Algo ha salido mal...</strong> No hemos podido guardar tus preguntas. Vuelve a intentarlo más tarde.
	                </div>
			</div></div>

			<div class="row">
				<div class="col-md-12">
					<h1>Envío de preguntas</h1>
					<p>Para poder continuar usando e-valUAM, tienes que enviar tres preguntas, cada una de ellas de un tema distinto.</p>
					<p>En el formulario siguiente podrás subir tus preguntas. Santiago las revisará.</p>
				</div>

			</div>

			<form id="formulario" method="post" enctype="multipart/form-data">
				<div class="row">
					<!-- Primera pregunta-->
					<div class="col-md-4">
						<h1>Pregunta número 1</h1>
						<div class="form-group">
							<label class="control-label" for="enunciado1">Enunciado</label>
 							<input type="text" class="form-control" name="enunciado1" id="enunciado1" placeholder="Texto del enunciado">
 						</div>
						<div class="form-group">
							<label class="control-label" for="fichero1">Imagen (opcional): </label>
							<input type="file" name="fichero1" accept="image/png,image/gif,image/jpeg">
						</div>
						<div class="form-group">
							<label class="control-label" for="respuestaOk1">Respuesta correcta</label>
                                                        <input type="text" class="form-control" name="respuestaOk1" placeholder="Texto de la respuesta correcta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal1_1">Respuesta incorrecta #1</label>
                                                       	<input type="text" class="form-control" name="respuestaMal1_1" placeholder="Texto de la primera respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal1_2">Respuesta incorrecta #2</label>
                                                        <input type="text" class="form-control" name="respuestaMal1_2" placeholder="Texto de la segunda respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal1_3">Respuesta incorrecta #3</label>
                                                        <input type="text" class="form-control" name="respuestaMal1_3" placeholder="Texto de la tercera respuesta incorrecta">
                                                </div>
					</div>

					<!-- Segunda pregunta -->
                                        <div class="col-md-4">
                                                <h1>Pregunta número 2</h1>
                                                <div class="form-group">
                                                        <label class="control-label" for="enunciado2">Enunciado</label>
                                                        <input type="text" class="form-control" name="enunciado2" placeholder="Texto del enunciado">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="fichero2">Imagen (opcional): </label>
                                                        <input type="file" name="fichero2" accept="image/png,image/gif,image/jpeg">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaOk2">Respuesta correcta</label>
                                                        <input type="text" class="form-control" name="respuestaOk2" placeholder="Texto de la respuesta correcta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal2_1">Respuesta incorrecta #1</label>
                                                        <input type="text" class="form-control" name="respuestaMal2_1" placeholder="Texto de la primera respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal2_2">Respuesta incorrecta #2</label>
                                                        <input type="text" class="form-control" name="respuestaMal2_2" placeholder="Texto de la segunda respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal2_3">Respuesta incorrecta #3</label>
                                                        <input type="text" class="form-control" name="respuestaMal2_3" placeholder="Texto de la tercera respuesta incorrecta">
                                                </div>
                                        </div>

					<!-- Tercera pregunta -->
                                       	<div class="col-md-4">
                                               	<h1>Pregunta número 3</h1>
                                               	<div class="form-group">
                                                       	<label class="control-label" for="enunciado3">Enunciado</label>
                                                       	<input type="text" class="form-control" name="enunciado3" placeholder="Texto del enunciado">
                                               	</div>
                                                <div class="form-group">
                                                        <label class="control-label" for="fichero3">Imagen (opcional): </label>
                                                        <input type="file" name="fichero3" accept="image/png,image/gif,image/jpeg">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaOk3">Respuesta correcta</label>
                                                        <input type="text" class="form-control" name="respuestaOk3" placeholder="Texto de la respuesta correcta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal3_1">Respuesta incorrecta #1</label>
                                                        <input type="text" class="form-control" name="respuestaMal3_1" placeholder="Texto de la primera respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal3_2">Respuesta incorrecta #2</label>
                                                        <input type="text" class="form-control" name="respuestaMal3_2" placeholder="Texto de la segunda respuesta incorrecta">
                                                </div>
                                                <div class="form-group">
                                                        <label class="control-label" for="respuestaMal3_3">Respuesta incorrecta #3</label>
                                                        <input type="text" class="form-control" name="respuestaMal3_3" placeholder="Texto de la tercera respuesta incorrecta">
                                                </div>
                	                </div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<button type="submit" id="boton" class="btn btn-primary">Guardar</button>
					</div>
				</div>
			</form>
		</main>

		<script>
			$('#formulario').submit(function () {
				// Borramos las alertas
			        $('#alerta_MalUser').removeClass("show");
                                $('#alerta_MalServer').removeClass("show");
                                $('#alerta_Ok').removeClass("show");

				$('#alerta_MalUser').addClass("hidden");
				$('#alerta_MalServer').addClass("hidden");
				$('#alerta_Ok').addClass("hidden");

				// Avisamos si hay campos vacíos
				$('input:text').parent().removeClass("has-warning");
				var vacios = $('input:text').filter(function() { return this.value == ""; });
				console.log(vacios);

				if (vacios.length != 0) {
					$('#alerta_MalUser').toggleClass("show hidden");
					vacios.parent().addClass("has-warning");
					return false;
				}

				var formData = new FormData(this);

                                $.ajax("preguntasAlumnosRequest.php", {
					data: formData,
		                        type: "POST",
					processData: false,
					contentType: false})
                                .done(function(msg) {
                                	if (msg == "0") {
                                        	$('#alerta_Ok').addClass("show");
	                                        $('#alerta_Ok').removeClass("hidden");
						$('#boton').prop("disabled", true);
                                        } else {
                                                $('#alerta_MalServer').addClass("show");
                                                $('#alerta_MalServer').removeClass("hidden");
					}
				})
                               .fail(function(jqXHR, textStatus) {
                                        $('#alerta_MalServer').addClass("show");
					$('#alerta_MalServer').removeClass("hidden");
                               });
				return false;
			});
		</script>
	</body>
</html>
