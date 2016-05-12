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

	if ($_REQUEST['tipo'] == 'alumnos')
	{

	    $result =  pg_query_params(
			$con,
			'SELECT 1=1
			FROM profesor_por_materia AS pm NATURAL JOIN examenes AS e
			WHERE pm.id_alumno = $1 AND e.id = $2',
			array($_SESSION['idUsuario'], intval($_REQUEST['id'])))
		or die('Error. Prueba de nuevo más tarde.');

		if (pg_num_rows($result) == 1 || $_SESSION['admin'] == 't') {
			$result =  pg_query_params(
				$con,
				'SELECT a.nombre AS nombre, a.id AS id_a, ape.timestamp AS tim, ape.id AS id, nota
				FROM alumnos AS a INNER JOIN alumnos_por_examen AS ape ON a.id = ape.id_alumno
				WHERE id_examen = $1
				ORDER BY tim DESC',
				array(intval($_REQUEST['id'])))
			or die('Error. Prueba de nuevo más tarde.');

				echo "<h2>Alumnos</h2>";
				echo "<form>";
					echo "<table class=\"table table-hover\">";
						echo "<thead><tr>";
							echo "<th>Id alumno</th>";
							//echo "<th>Nombre alumno</th>";
							echo "<th>Nota</th>";
							echo "<th>Fecha y hora</th>";
							echo "<th>Seleccionar</th>";
						echo "</tr></thead><tbody>";

						while ($examen = pg_fetch_array($result, null, PGSQL_ASSOC)) {
							$nota = (!is_null($examen['nota']) ? number_format($examen['nota'], 2) : "No terminado");
							//sprintf($nota, "%.2f", $nota);
							//echo "<tr><td>".$examen['id_a']."</td><td>".$examen['nombre']."</td><td>".$nota."</td><td>".$examen['tim']."</td>";
							echo "<tr><td>".$examen['id_a']."</td><td>".$nota."</td><td>".$examen['tim']."</td>";
							echo "<td><input type=\"radio\" name=\"idExamenAlumno\" value=\"".$examen['id']."\" onclick=\"loadXMLDocDatos(this.value, '".$examen['id_a']."', '".$examen['tim']."', ".$_REQUEST['id'].", ".$_REQUEST['ma_id'].")\"></td></tr>";
						}

					echo "</tr></thead><tbody>";
				echo "</form>";
		} else {
			echo "<div class=\"alert alert-danger\" role=\"alert\"><p>No tienes permisos para acceder a la información solicitada.</p></div>";
		}
	}
	else if ($_REQUEST['tipo'] == 'datos')
	{
		//Buscamos el numero de respuestas que tiene el examen
		$result =  pg_query_params(
				$con,
				'SELECT mat.num_respuestas as num_resp
				FROM (alumnos_por_examen AS ape  INNER JOIN examenes AS ex ON ape.id_examen = ex.id)
				INNER JOIN materias as mat ON ex.id_materia = mat.id
				WHERE ape.id = $1',
				array(intval($_REQUEST['id'])))
			or die('Error. Prueba de nuevo más tarde.');


			$res = pg_fetch_array($result, null, PGSQL_ASSOC);
			$num_resp = $res['num_resp'];

		$result = pg_query_params(
			$con,
			'SELECT nota
			FROM alumnos_por_examen
			WHERE id = $1',
			array(intval($_REQUEST['id'])))
		or die('Error. Prueba de nuevo más tarde.');

		$res = pg_fetch_array($result, null, PGSQL_ASSOC);
		$nota = $res['nota'];

		if($num_resp != 1){

			$result =  pg_query_params(
				$con,
				'SELECT r2.duda AS duda, p.texto AS preg, r2.correcta AS cor, r2.texto AS res, r2.timestamp AS time, p.imagen AS img, id_materia
				FROM preguntas AS p INNER JOIN
				(respuestas AS r INNER JOIN respuestas_por_alumno AS rpa ON r.id = rpa.id_respuesta) AS r2 ON p.id = r2.id_pregunta
				WHERE r2.id_alumno_examen = $1
				ORDER BY time',
				array(intval($_REQUEST['id'])))
			or die('Error. Prueba de nuevo más tarde.');

		} else {

		$result =  pg_query_params(
			$con,
			'SELECT p.texto AS preg, resp.texto AS resc, resp.timestamp AS time, p.imagen AS img, resp.respuesta AS res, duda
				FROM preguntas AS p INNER JOIN
				(SELECT * FROM respuestas AS r NATURAL JOIN respuestas_abiertas AS rpa  WHERE id_alumno_examen = $1 )
				AS resp	ON p.id = resp.id_pregunta
				ORDER BY time',
			array(intval($_REQUEST['id'])))
		or die('Error. Prueba de nuevo más tarde.');


		}

		//<!-- <h1>Examen de <?php echo $_REQUEST['nombre']; ></h1> !-->
	?>


<html>
	<head>
		<title>Visor de exámenes</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="../favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>
	<body>
		<?php /* mostrar_header(); */ ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h2><?php echo $_REQUEST['time']; ?></h2>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
			<?php

				echo "<h1>Resultados de #".$_REQUEST['name'].":</h1>";
				echo "<p>La nota es ".number_format($nota, 2).".</p>";
				echo "<p>A continuación aparecerán las respuestas. Aparecerán en rojo aquellas que sean incorrectas.</p>";
				echo "</div></div>";

				for ($i = 1; $res = pg_fetch_array($result, null, PGSQL_ASSOC); $i++) {
					echo "<section class=\"row\"><div class=\"col-md-12\">";

						echo "<h3>[Preg. #".$i."] ".$res['preg'].":</h3>";
						if (strlen($res['img']) >= 5) {
								echo "<img id=\"imagen\" src=\"../multimedia".$res['id_materia']."/".$res['img']."\"/>"; //ID EXAMEN
						}

						if ($res['duda'] == 't') {
							echo "<div class=\"alert alert-info\" role=\"alert\"><p>Dudó</p></div>";
						}

						if($num_resp == 1)
							$res['cor'] = (($res['res'] != $res['resc']) ? f : t);


						if ($res['cor'] == 't') {
							echo "<p class=\"correcta\">".$res['res']."</p>";
						} else {
							echo "<p class=\"incorrecta\">".$res['res']."</p>";
							if($num_resp == 1)
								echo "<p class=\"corrrecta\">Correcta: ".$res['resc']."</p>";
						}

					echo "</div></section>";
				}
			}

			?>
	</body>
</html>
