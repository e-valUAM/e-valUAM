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
	date_default_timezone_set('Europe/Madrid');

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	// Este primer if es en caso de que sea el comienzo del examen
	if (isset($_REQUEST['idExamen'])) {
		// Nos ponemos como locos a inicializar todo
		$_SESSION['idExamen'] = $_REQUEST['idExamen'];
		$_SESSION['sigueExamen?'] = true;

		// Borramos posibles variables que pueden estar guardadas si
		// No se ha cerrado bien el examen anterior

		unset($_SESSION['id_pregunta_anterior']);
		unset($_SESSION['id_pregunta_anteanterior']);

	//Comprobamos que el examen este abierto y guardamos su tipo

		$result = pg_query_params($con, 'SELECT tipo_examen,feedback FROM examenes WHERE id = $1 AND disponible = true AND comienzo < now() AND now() < comienzo + tiempo_disponible AND borrado = false ORDER BY id', array($_SESSION['idExamen']))
		or die('Se ha producido un error al buscar el examen. Prueba de nuevo más tarde.');

		//Si el alumno llega aqui es porque el examen está cerrado o no existe
		if(pg_num_rows($result) == 0){
			header("Location: ./eleccionExamen.php");
	   		exit;
		}

		$line = pg_fetch_array($result, null, PGSQL_ASSOC);
		pg_free_result($result);
		$_SESSION['tipo_examen'] = $line['tipo_examen'];
		$_SESSION['feedback_examen'] = $line['feedback'];
		//Error al hacer la query
		if($_SESSION['tipo_examen'] == NULL){
			header("Location: ./eleccionExamen.php");
	   		exit;
		}



		// Ciertos parametros vienen de la base de datos.

		$result =  pg_query_params($con,
			'SELECT num_preguntas, num_dificultades, num_respuestas, duracion, materias.id as materias_id, acepta_duda, acepta_feedback
			FROM examenes, materias
			WHERE examenes.id =  $1 and id_materia = materias.id',
			array($_SESSION['idExamen']))
		or die('Se ha producido un error. Comprueba que el examen al que intentas acceder es correcto');

		$line = pg_fetch_array($result, null, PGSQL_ASSOC);

		$_SESSION['restante'] = $line['duracion'];
		$_SESSION['final'] =  time() + ($line['duracion'] * 60);
		$_SESSION['materias_id'] = $line['materias_id'];

		$_SESSION['num_preguntas'] = $line['num_preguntas'];
		$_SESSION['numRespondidas'] = 0;
		$_SESSION['numCorrectas'] = 0;

		$_SESSION['num_dificultades'] = $line['num_dificultades'];
		$_SESSION['num_respuestas'] = $line['num_respuestas'];
		$_SESSION['nivel'] = 1;
		$_SESSION['numEnNivel'] = 0;

		$_SESSION['acepta_duda'] = ($line['acepta_duda'] == 't' ? TRUE : FALSE);
		$_SESSION['acepta_feedback'] = ($line['acepta_feedback'] == 't' ? TRUE : FALSE);

		unset($_REQUEST['idExamen']);
		pg_free_result($result);

		//Por ultimo, guardamos en la base de datos que este examen comienza
		$time = time();


		pg_query_params($con,
			'INSERT INTO alumnos_por_examen VALUES ($1, $2, $3);',
			array($_SESSION['idUsuario'], $_SESSION['idExamen'], date(DATE_ISO8601)))
		or die('Error al guardar en la base de datos el inicio del examen. Intentalo más tarde.');

		$result =  pg_query_params($con,
			'SELECT id
			FROM alumnos_por_examen
			WHERE id_alumno = $1 and id_examen = $2 and timestamp = $3
			LIMIT 1',
			array($_SESSION['idUsuario'], $_SESSION['idExamen'], date(DATE_ISO8601, $time)))
		or die('Error al asignar un id para realizar el examen');

		$row = pg_fetch_array($result, null, PGSQL_ASSOC);
		$_SESSION['idAlumnoExamen'] = $row['id'];
		pg_free_result($result);


		// Sacamos el saco que le toca al alumno para este examen
		//Si se trata de un examen de saco

		if($_SESSION['tipo_examen'] == 'saco'){

			$result =  pg_query_params($con,
				'SELECT num_saco
				FROM saco_por_examen
				WHERE id_alumno = $1 and id_examen = $2
				LIMIT 1',
				array($_SESSION['idUsuario'], $_SESSION['idExamen']))
			or die('Error al asignar el saco de preguntas del examen');

			$row = pg_fetch_array($result, null, PGSQL_ASSOC);

			if (!$row) {
				pg_query_params($con,
					'INSERT INTO saco_por_examen (id_alumno, id_examen)
					VALUES ($1, $2)',
					array($_SESSION['idUsuario'], $_SESSION['idExamen']))
				or die('Error al insertar el saco de preguntas en la base de datos');
				$row['num_saco'] = 1;
			}

			$_SESSION['saco'] = $row['num_saco'];
			pg_free_result($result);
		}

	}

	//Caso resto de preguntas
	else if ($_SESSION['sigueExamen?'])
	{
		// Aquí se llega después de responder a una pregunta
		// Primero guardamos la respuesta

		$time = time();

		$_SESSION['restante'] = ceil(($_SESSION['final'] - $time) / 60);

		$duda = NULL;
		if ($_SESSION["acepta_duda"])
			$duda = ($_REQUEST['duda'] == 't' ? 't' : 'f');

		// Hacemos la query de inserción en funcion del examen

		if($_SESSION['num_respuestas'] == 1){ //Caso respuesta abierta

			/* limpiamos de espacios la respuesta */
			$respuestaAbierta = trim($_REQUEST['respuestaA']);

			pg_query_params($con,
				'INSERT INTO respuestas_abiertas VALUES ($1, $2, $3, $4, $5, $6,$7,$8);',
				array($_SESSION['idUsuario'], $_SESSION['id_pregunta_anterior'], $respuestaAbierta, date(DATE_ISO8601, $time), $_SESSION['idAlumnoExamen'], $duda,$_SESSION['correcta'],$_SESSION['parametros']))
			or die('Error. Prueba de nuevo más tarde.');

			// Guardada la respuesta, actualizamos las variables que definen el examen
			// Comprobamos si la respuesta ha sido correcta
			if (strcmp($respuestaAbierta, $_SESSION['correcta']) == 0) {
				$_SESSION['correcta'] = TRUE;
			} else {
				$_SESSION['correcta'] = FALSE;
			}
				$_SESSION['numRespondidas']++;


		}
		else{
			$idRespuesta = $_SESSION['respuestas'][$_REQUEST['respuesta']];
			pg_query_params($con,
				'INSERT INTO respuestas_por_alumno VALUES ($1, $2, $3, $4, $5);',
				array($_SESSION['idUsuario'], $idRespuesta, date(DATE_ISO8601, $time), $_SESSION['idAlumnoExamen'], $duda))
			or die('Error. Prueba de nuevo más tarde.');
			// Guardada la respuesta, actualizamos las variables que definen el examen
			// Comprobamos si la respuesta ha sido correcta
			if (strcmp($_REQUEST['respuesta'], $_SESSION['correcta']) == 0) {
				$_SESSION['correcta'] = TRUE;
			} else {
				$_SESSION['correcta'] = FALSE;
			}
				$_SESSION['numRespondidas']++;
		}



		// Si la respuesta es correcta, lo anotamos y comprobamos si salta de nivel
		if ($_SESSION['correcta']) {
			$_SESSION['numCorrectas']++;
			$_SESSION['numEnNivel']++;

			if ($_SESSION['numEnNivel'] == $_SESSION['num_preguntas'] / $_SESSION['num_dificultades']) {
				$_SESSION['nivel']++;
				$_SESSION['numEnNivel'] = 0;
			}
		}

		//Mostramos mensaje de feedback
		if($_SESSION['feedback_examen']){

			$mensaje = ($_SESSION['correcta']) ? "<p>Respuesta Correcta</p>" : "<p>Respuesta Incorrecta</p>";
			$mensaje = $mensaje."<p>".$_SESSION['feedback']."</p>";
			$mensaje = $mensaje."<p>Pulse <a target=\"_blank\" href='Anterior.php'>aqui</a> para ver la pregunta anterior</p>";

			set_mensaje(($_SESSION['correcta']) ? "ok" : "error", $mensaje);

		}

		// Sea correcta o no, debemos comprobar si es el fin del examen
		if ($_SESSION['numRespondidas'] == $_SESSION['num_preguntas']) {
			$_SESSION['sigueExamen?'] = false;
			header("Location: ./FinExamen.php");
			exit;
		}

	} else { // Dirigia a error.php que no existia
		header("Location: /eleccionExamen.php");
		exit;
	}

	//Al subir de saco te mandaba a enviar preguntas
	/*if ($_SESSION['tipo_examen'] == 'saco' && !$_SESSION['envio_preguntas'] && $_SESSION['saco'] > 1) {
		header("Location: ./enviarPreguntas.php");
		exit;
	}*/
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

		<script type="text/javascript">
			$(function () {
			  $('[data-toggle="popover"]').popover()
														})
			var minRes = <?php echo $_SESSION['restante']; ?>;
			var mostrar = getCookie('mostrar');

			if (mostrar == null)
				setCookie('mostrar', 'true', null);

			window.onbeforeunload = function(e) {
				return "Si sales de esta página sin dar una respuesta esta pregunta se contabilizará como una respuesta erronea.";
			};

			function goodExit() {
				window.onbeforeunload = null;
			}

			function countredirect(){
				if (minRes > 0){
					minRes -= 1;
					if (mostrar == 'true')
							document.getElementById("tiempo").innerHTML = minRes + " minutos restantes.";
				} else {
					goodExit();
					window.location = "./FinExamen.php?";
					return
				}

				setTimeout("countredirect()", 60000);
			}

			function cambiarTiempo(){
				if (mostrar == 'true')
					mostrar = 'false';
				else
					mostrar = 'true';

				setCookie('mostrar', mostrar, null);

				mostrarTiempo();
			}

			function mostrarTiempo() {
				if (mostrar == 'true') {
					document.getElementById("tiempo").innerHTML = minRes + " minutos restantes.";
					document.getElementById("botonMostrar").innerHTML = "Ocultar";
				} else {
					document.getElementById("tiempo").innerHTML = "";
					document.getElementById("botonMostrar").innerHTML = "Mostrar tiempo restante";
				}
			}

			function getCookie(c_name) {
				var c_value = document.cookie;
				var c_start = c_value.indexOf(" " + c_name + "=");
				if (c_start == -1)
				{
					c_start = c_value.indexOf(c_name + "=");
				}
				if (c_start == -1)
				{
					c_value = null;
				}
				else
				{
					c_start = c_value.indexOf("=", c_start) + 1;
					var c_end = c_value.indexOf(";", c_start);
					if (c_end == -1)
					{
						c_end = c_value.length;
					}
					c_value = unescape(c_value.substring(c_start,c_end));
				}
				return c_value;
			}

			function setCookie(c_name, value, exdays)
			{
				var exdate=new Date();
				exdate.setDate(exdate.getDate() + exdays);
				var c_value = escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
				document.cookie=c_name + "=" + c_value;
			}
		</script>
	</head>

	<body onload="countredirect();mostrarTiempo()">
		<?php mostrar_header(); ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<p class="text-center"><span id="tiempo"></span> <button class="btn btn-primary" id="botonMostrar" onclick="cambiarTiempo()"></button></p>
				</div>
			</div>
		</div>

		<?php
			//if desactualizado, nunca entrará en el
			if (($_SESSION['numRespondidas'] > 0 && ($_SESSION['acepta_feedback'] || isset($_SESSION['feedback'])))&& false) {
				if ($_SESSION['correcta']) {
		?>
			<div class="container-fluid">
			<div class="alert alert-success" role="alert">
			<button type="button" class="close" data-dismiss="alert">
			  <span aria-hidden="true">&times;</span>
			  <span class="sr-only">Cerrar</span>
			</button>

			
			<?php if (isset($_SESSION['feedback'])) { ?>
					<p><?php echo $_SESSION['feedback'];?></p></div>
					<?php if($_SESSION['feedback_examen']='t')
							echo "<p>Pulse <a target=\"_blank\" href='Anterior.php'>aqui</a> para ver la pregunta anterior</p>";?>

			<?php }  else { ?>
				<p>Respuesta correcta.</p>
				<?php if($_SESSION['feedback_examen']='t')
							echo "<p>Pulse <a target=\"_blank\" href='Anterior.php'>aqui</a> para ver la pregunta anterior</p>";?>
			<?php } ?>
			</div>
		<?php
				} else {
		?>
			<div class="container-fluid">
			<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert">
			  <span aria-hidden="true">&times;</span>
			  <span class="sr-only">Cerrar</span>
			</button><p>Respuesta incorrecta.</p>
			<?php if($_SESSION['feedback_examen']='t')
							echo "<p>Pulse <a target=\"_blank\" href='Anterior.php'>aqui</a> para ver la pregunta anterior</p>";?></div>
			</div>
		<?php
				}
			}
			unset($_SESSION['feedback']);
			unset($_SESSION['correcta']);
		?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<?php
						// La query varia en funcion del tipo de examen
						if($_SESSION['tipo_examen']=='clasico'){// Clasico

							if($_SESSION['num_respuestas'] == 1){//Abierta
								$result =  pg_query_params($con,
								'	(SELECT id, texto, imagen, audio, feedback,parametros,script
									FROM preguntas
									WHERE id_materia = $1 and dificultad = $2 and borrada = FALSE
									AND id NOT IN (SELECT f2.id FROM respuestas_abiertas AS f1,
									preguntas AS f2 WHERE f1.id_pregunta = f2.id AND id_alumno = $3 and id_alumno_examen = $4)
									ORDER BY RANDOM() LIMIT 1
									)
								',
								array($_SESSION['materias_id'], $_SESSION['nivel'], $_SESSION['idUsuario'], $_SESSION['idAlumnoExamen']))
								or die('Error obteniendo la pregunta. Prueba de nuevo más tarde.');
							}
							else{ //TEST

								$result =  pg_query_params($con,
								'	(SELECT id, texto, imagen, audio, feedback
									FROM preguntas
									WHERE id_materia = $1 and dificultad = $2 and borrada = FALSE
									AND id NOT IN
									(SELECT preguntas.id
									FROM (preguntas INNER JOIN respuestas ON preguntas.id = id_pregunta)
										INNER JOIN respuestas_por_alumno ON respuestas.id = id_respuesta
									WHERE id_alumno = $3 and id_alumno_examen = $4)
									)
								',
								array($_SESSION['materias_id'], $_SESSION['nivel'], $_SESSION['idUsuario'], $_SESSION['idAlumnoExamen']))
								or die('Error. Prueba de nuevo más tarde.');
						}
						}
						else{ //Caso examen Saco
							if($_SESSION['num_respuestas'] == 1){//Abierta
								$result =  pg_query_params($con,
								'	(SELECT id, texto, imagen, audio, feedback, parametros, script
									FROM preguntas
									WHERE id_materia = $1 AND dificultad = $2 AND borrada = FALSE AND saco = $3
									AND id NOT IN
									(SELECT preguntas.id
									FROM (preguntas INNER JOIN respuestas ON preguntas.id = id_pregunta)
										INNER JOIN respuestas_por_alumno ON respuestas.id = id_respuesta
									WHERE id_alumno = $4 and id_alumno_examen = $5)
									)
								',
								array($_SESSION['materias_id'], $_SESSION['nivel'], $_SESSION['saco'], $_SESSION['idUsuario'], $_SESSION['idAlumnoExamen']))
							or die('Error. Prueba de nuevo más tarde.');


							}
							else{ //Test
							$result =  pg_query_params($con,
								'	(SELECT id, texto, imagen, audio, feedback
									FROM preguntas
									WHERE id_materia = $1 AND dificultad = $2 AND borrada = FALSE AND saco = $3
									AND id NOT IN
									(SELECT preguntas.id
									FROM (preguntas INNER JOIN respuestas ON preguntas.id = id_pregunta)
										INNER JOIN respuestas_por_alumno ON respuestas.id = id_respuesta
									WHERE id_alumno = $4 and id_alumno_examen = $5)
									)
								',
								array($_SESSION['materias_id'], $_SESSION['nivel'], $_SESSION['saco'], $_SESSION['idUsuario'], $_SESSION['idAlumnoExamen']))
							or die('Error. Prueba de nuevo más tarde.');
							}
						}

						// Caso preguntas agotadas
						if (pg_num_rows($result) == 0) {
							$_SESSION['sigueExamen?'] = false;
							header("Location: ./FinExamen.php");
							exit;
						}


						$pregunta = pg_fetch_array($result, rand(0, pg_num_rows($result) - 1), PGSQL_ASSOC);


						do {
						$guardado = TRUE;
						//Caso parametrica, generamos parametros y sustituimos texto
						if($_SESSION['num_respuestas'] == 1){
							if($_SESSION['parametros']=='t'){

								//Iniciamos transaccion

								pg_query($con,'BEGIN;');

								$paramQuery =  pg_query_params($con,
									'SELECT id, orden, min, max 
									FROM parametros 
									WHERE id_pregunta = $1 AND borrada = FALSE
									ORDER BY orden ASC;',
									array($pregunta['id']))
								or die('Error. Prueba de nuevo más tarde.');


							

							//Generamos parametros y sustituimos en el texto
							for ($i = 1; $parametrosLimites = pg_fetch_array($paramQuery, null, PGSQL_ASSOC); $i++) {

								//De momento todos los parametros son de 3 cifras decimales
								$parametros[$i]= mt_rand(floor( $parametrosLimites['min']*1000),floor( $parametrosLimites['max']*1000))/1000;
								$pregunta['texto'] = str_replace("$".$i, $parametros[$i], $pregunta['texto']);

								//Guardamos los parámetros generados
								pg_query_params($con,
									'INSERT INTO parametros_por_alumno (id_parametro,id_alumno_examen,valor) VALUES ($1,$2,$3);',
									array( $parametrosLimites['id'],$_SESSION['idAlumnoExamen'] , $parametros[$i]));

							}
						}
					}


						$preg = "<h1 class=\"activaAudioPrincipal\" id=\"textoPregunta\">".$pregunta['texto']."</h1>";

						if (strlen($pregunta['imagen']) >= 5) {
							$preg = $preg. "<img class=\"img-responsive activaAudioPrincipal\"  id=\"imagen\" src=\"./multimedia/".$_SESSION['materias_id']."/".$pregunta['imagen']."\"/>";
						}

						if (isset($pregunta['audio'])) {
							$preg = $preg. "<audio controls preload=\"auto\" id=\"audioPrincipal\">";
							$preg = $preg. "<source src=\"./multimedia/".$_SESSION['materias_id']."/".$pregunta['audio']."\" type=\"audio/mpeg\">";
							$preg = $preg. "Tu navegador no soporta audio. Por favor, actualiza <a href=\"http://browsehappy.com/\">a un navegador más moderno.</a>";
							$preg = $preg. "</audio>";
						}
						$_SESSION['id_pregunta_anteanterior']= $_SESSION['id_pregunta_anterior'];
						$_SESSION['id_pregunta_anterior']=$pregunta['id'];
						$_SESSION['feedback'] = $pregunta['feedback'];
						$_SESSION['parametros'] = $pregunta['parametros'];
						$_SESSION['script'] = $pregunta['script'];
					?>
				</div>
			</div>
			<div class="row" id="respuestas">
					<?php
						$letras = array("A", "B", "C", "D", "E", "F", "G", "H");

						$result =  pg_query_params($con,
							'SELECT id, texto, correcta, imagen, audio
							FROM respuestas
							WHERE id_pregunta = $1
							ORDER BY RANDOM()',
							array($pregunta['id']))
						or die('Error. Prueba de nuevo más tarde.');

						switch ($_SESSION['num_respuestas']) {
							case 2:
								$class = "col-md-6";
								break;
							case 3:
								$class = "col-md-4";
								break;
							case 4:
								$class = "col-md-3";
								break;
							case 5:
							case 6:
								$class = "col-md-2";
								break;
							default:
								$class = "col-md-1";
						}

						if($_SESSION['num_respuestas'] == 1){ // Caso respuesta abierta
							//Caso preguntas parametricas
							if($_SESSION['parametros']=='t'){

								//Aqui se deberia llamar a matlab y generar la respuesta correcta

								$path = 'cd scriptPregunta/'.$_SESSION['materias_id'].'/;';

								$matlab = 'matlab -nodisplay -nojvm -r "'.before_last('.m', $_SESSION['script']).'(';



								for($j=1;$j<$i;$j++){

									if($j != 1)
										$matlab=$matlab.",";

									$matlab = $matlab.$parametros[$j];
								}
							

								$matlab = $matlab.'),quit;"';

		                        $solve = shell_exec($path.$matlab);


								$_SESSION['correcta']=trim(after("=",$solve));


								//Error en la llamada a matlab por alguna razon(permisos/sobrecarga...)
								if($_SESSION['correcta'] == null || $_SESSION['correcta']==''){

									pg_query($con,'ROLLBACK;');
									$guardado = FALSE;
									

								//Si ha ido todo bien cerramos la transaccion
								} else {
									pg_query($con,'COMMIT;');
									echo $preg;
								}

							
							} else { //Preguntas abiertas normales
								echo $preg;
								$respuesta = pg_fetch_array($result, null, PGSQL_ASSOC);
								$_SESSION['correcta'] = $respuesta['texto'];

							}
						}
						else{ // respuestas tipo test
							echo $preg;
							for ($i = 0; $respuestas = pg_fetch_array($result, null, PGSQL_ASSOC); $i++) {
								echo "<div class=\"$class\">";
								echo "<p class=\"lead\" id=\"respueta\">".$respuestas['texto']."</p>";

								if (isset($respuestas['imagen'])) {
									echo "<img class=\"img-responsive\" src=\"./multimedia/".$_SESSION['materias_id']."/".$respuestas['imagen']."\"/>";
								}

								if (isset($respuestas['audio'])) {
									echo "<audio controls preload=\"auto\" id=\"audioPrincipal\">";
									echo "<source src=\"./multimedia/".$_SESSION['materias_id']."/".$respuestas['audio']."\" type=\"audio/mpeg\">";
									echo "Tu navegador no soporta audio. Por favor, actualiza <a href=\"http://browsehappy.com/\">a un navegador más moderno.</a>";
									echo "</audio>";
								}
								echo "</div>";


								if ($respuestas['correcta'] == "t")
									$_SESSION['correcta'] = $letras[$i];

								$_SESSION['respuestas'][$letras[$i]] = $respuestas['id'];
							}
						}

					//Aqui el while del do while
					} while($guardado == FALSE );

					?>
			</div>
		</main>

		<footer class="container-fluid">
			<form action="Examen.php" method="post" onsubmit="goodExit()">
				<?php if ($_SESSION['acepta_duda']) { ?>
					<div class="row">
						<div class="col-md-12">
							<div class="checkbox">
							  <label>
							    <input name="duda" type="checkbox" value="t"> Dudo de la respuesta
							  </label>
							</div>
						</div>
					</div>
				<?php } ?>

					<?php
						if($_SESSION['num_respuestas'] == 1){ // Caso respuesta abierta

						echo '<form role="form">';
						echo '	<div class="form-group col-md-6 col-md-offset-1">';
						echo '		<label class="control-label" for="respuestaA">Respuesta: </label>';
						echo '		<input class="form-control" type="text" id="ex3" name="respuestaA" placeholder="Introduzca su respuesta" autocomplete="off">';
						echo '<button type="submit" class="btn btn-primary aria-label="Left Align">Enviar</button>';
						echo '	</div>';
						echo '</form>';
						echo '<span class="glyphicon glyphicon-question-sign" data-toggle="popover" title="Información" data-content="Utiliza . para separar decimales
Escribe tus respuestas en minúscula" data-trigger=" click hover">
								 <span class="sr-only">Información</span>
							</span>';

						}
						else{ //Respuestas tipo test
							echo '<div class="row">';
							switch ($_SESSION['num_respuestas']) {
								case 2:
									$class = "col-md-6";
									break;
								case 3:
									$class = "col-md-4";
									break;
								case 4:
									$class = "col-md-3";
									break;
								case 5:
								case 6:
									$class = "col-md-2";
									break;
								default:
									$class = "col-md-1";
							}

							for ($i = 0; $i < (0 + $_SESSION['num_respuestas']); $i++) {
								echo "<div class=\"$class\">";
									echo "<button name=\"respuesta\" value=\"$letras[$i]\" type=\"submit\" class=\"btn btn-primary btn-lg btn-block\">$letras[$i]</button>";
								echo "</div>";
							}
							echo '</div>';
						}
						pg_free_result($result);
					?>

			</form>
		</footer>
	</body>
</html>
