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
	borrar_mensaje();

	// Calculamos la nota
	if($_SESSION['num_respuestas'] <= 1)
		$prob_fallo = 0;
	else
		$prob_fallo = 1.0 / ($_SESSION['num_respuestas'] -1);

	$valor_pregunta = 10.0 / $_SESSION['num_preguntas'];
	$num_fallos = $_SESSION['num_preguntas'] - $_SESSION['numCorrectas'];

	$nota = $valor_pregunta * ($_SESSION['numCorrectas'] - $prob_fallo * $num_fallos);

	// Puede salir negativa, porque compensamos al poder responder al azar, pero 0 es el mínimo
	if ($nota < 0)
		$nota = 0;

	// Se guarda en la base de datos
	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

    pg_query_params($con,
		'UPDATE alumnos_por_examen SET nota = $1 WHERE id = $2;',
		array($nota, $_SESSION['idAlumnoExamen']))
	or die('Error. Prueba de nuevo más tarde.');

	// Miramos si debe actualizarse el saco

	if($_SESSION['tipo_examen']=='saco'){
		$result = pg_query_params($con,
		'SELECT nota FROM alumnos_por_examen WHERE id_alumno = $1 AND id_examen = $2;',
		array($_SESSION['idUsuario'], $_SESSION['idExamen']))
		or die('Error. Prueba de nuevo más tarde.');

		// Miramos cuandos 9 ha habido
		$nueves = 0;
		while ($res = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			if ($res['nota'] >= 9)
				$nueves++;
		}

		pg_free_result($result);


		// Si hay 0 o 1 nueve, está en el saco 1. Si 2 o 3, saco 2. 4 o 5, saco 3.
		if (($_SESSION['saco'] < 3) && (($_SESSION['saco'] * 2) < $nueves)) {
			pg_query_params($con,
			'UPDATE saco_por_examen SET num_saco = $1 WHERE id_alumno = $2 and id_examen = $3;',
			array($_SESSION['saco'] + 1, $_SESSION['idUsuario'], $_SESSION['idExamen']))
			or die('Error. Prueba de nuevo más tarde.');
		}
	}


?>

<!DOCTYPE html>

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

		<?php
			//Dejamos el feedback antiguo sin ver pregunta anterior
			if ($_SESSION['acepta_feedback'] || isset($_SESSION['feedback'])) {
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
			<?php }  else { ?>
				<p>Respuesta correcta.</p>
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
			</button><p>Respuesta incorrecta.</p></div>
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
					<h1>Fin del examen.</h1>
				</div>
			</div>
				<?php
					$result = pg_query_params($con,
						'SELECT mostrar_resultados FROM examenes WHERE id = $1;',
						array($_SESSION['idExamen']))
					or die('Error. Prueba de nuevo más tarde.');

					$row = pg_fetch_array($result, null, PGSQL_ASSOC);

					if ($row['mostrar_resultados'] == 'parcial') {
						echo "<div class=\"row\"><div class=\"col-md-12\"><p>Tu nota es ".$nota.".</p></div></div>";
					} else if ($row['mostrar_resultados'] == 'completo') {

							if($_SESSION['num_respuestas'] != 1 ){
								$result =  pg_query_params(
								$con,
								'SELECT p.texto AS preg, r2.correcta AS cor, r2.texto AS res, r2.timestamp AS time, p.imagen AS img
								FROM preguntas AS p INNER JOIN
								(respuestas AS r INNER JOIN respuestas_por_alumno AS rpa ON r.id = rpa.id_respuesta) AS r2 ON p.id = r2.id_pregunta
								WHERE r2.id_alumno_examen = $1
								ORDER BY time',
								array(intval($_SESSION['idAlumnoExamen'])))
							or die('Error. Prueba de nuevo más tarde.');
						} else{

						//Query antigua
						/*	$result =  pg_query_params(
								$con,
					'SELECT p.texto AS preg, resp.correcta AS cor, resp.texto AS res, resp.timestamp AS time, p.imagen AS img, resp.respuesta AS rpa
								FROM preguntas AS p INNER JOIN
								(SELECT * FROM respuestas AS r NATURAL JOIN respuestas_abiertas AS rpa  WHERE id_alumno_examen = $1 ) AS resp 									ON p.id = resp.id_pregunta
								ORDER BY time;',
								array(intval($_SESSION['idAlumnoExamen'])))
							or die('Error. Prueba de nuevo más tarde.');
						*/
							$result =  pg_query_params($con,
							'SELECT id,texto AS preg, respuesta_correcta = respuesta AS cor, parametros,
							 respuesta_correcta AS res, timestamp AS time, imagen AS img, respuesta AS rpa
							 FROM preguntas INNER JOIN respuestas_abiertas
							 ON id = id_pregunta 
							 WHERE id_alumno_examen = $1;',
							array(intval($_SESSION['idAlumnoExamen'])))
							or die('Error. Prueba de nuevo más tarde.');
						}

						echo "<div class=\"row\"><div class=\"col-md-12\"><h1>Resultados:</h1>";
						echo "<p>Tu nota es ".number_format($nota, 2).".</p>";
						echo "<p>A continuación verás tus respuestas. Aparecerán en rojo aquellas que sean incorrectas.</p></div></div>";


						for ($i = 1; $res = pg_fetch_array($result, null, PGSQL_ASSOC); $i++) {




							if($res['parametros']=='t'){ //Parametrica
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
									$res['preg'] = str_replace("$".$i, $parametros['valor'], $res['preg']);
								}
							}


							echo "<div class=\"row\"><div class=\"col-md-12\"><section class=\"respuestas\">";
								echo "<p class=\"lead\">[Preg. #".$i."] ".$res['preg'].":</p>";
								if (strlen($res['img']) >= 5) {
										echo "<img id=\"imagen\" src=\"./multimedia/".$_SESSION['materias_id']."/".$res['img']."\"/>"; //ID EXAMEN
									}
								if($_SESSION['num_respuestas'] != 1 ){//Tipo test
									if ($res['cor'] == 't') {
										echo "<p class=\"correcta\">".$res['res']."</p>";
									} else {
										echo "<p class=\"incorrecta\">".$res['res']."</p>";
									}
								}else { //Respuesta abierta
									if (strcmp($res['res'],$res['rpa']) == 0) {
										echo "<p class=\"correcta\"> ".$res['rpa']."</p>";
									} else {
										echo "<p class=\"incorrecta\"> ".$res['rpa']."</p>";

										if($res['parametros']=='t'){
											echo '<p>Respuesta correcta: '.$res['res'].'</p>';
										}

									}
								}
							echo "</section></div></div>";
						}
					}
				?>
		</main>
		<footer class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<a class="btn btn-primary" href="eleccionExamen.php" role="button">Terminar</a>
				</div>
			</div>
		</footer>
	</body>
</html>


<?php
	unset($_SESSION['tipo_examen']);
	unset($_SESSION['sigueExamen?']);
	unset($_SESSION['idExamen']);
	unset($_SESSION['id_pregunta_anterior']);
	unset($_SESSION['id_pregunta_anteanterior']);
	//session_destroy();
?>
