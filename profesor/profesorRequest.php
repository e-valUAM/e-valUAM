<?php

	include 'funciones_profesor.php';

	session_start();

	if (!isset($_SESSION['profesor'])) {
		header("Location: ./index.php");
   		exit;
	}

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde. Si ves al técnico dile que "'. pg_last_error().'"');
	
	if ($_REQUEST['tipo'] == 'alumnos') 
	{

	    $result =  pg_query_params(
			$con, 
			'SELECT 1=1 
			FROM profesor_por_materia AS pm NATURAL JOIN examenes AS e
			WHERE pm.id_alumno = $1 AND e.id = $2',
			array($_SESSION['idUsuario'], intval($_REQUEST['id'])))
		or die('La consulta fallo: ' . pg_last_error());

		if (pg_num_rows($result) == 1) {
			$result =  pg_query_params(
				$con, 
				'SELECT a.nombre AS nombre, a.id AS id_a, ape.timestamp AS tim, ape.id AS id, nota
				FROM alumnos AS a INNER JOIN alumnos_por_examen AS ape ON a.id = ape.id_alumno 
				WHERE id_examen = $1 
				ORDER BY tim DESC',
				array(intval($_REQUEST['id'])))
			or die('La consulta fallo: ' . pg_last_error());

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

		$result = pg_query_params(
			$con,
			'SELECT nota
			FROM alumnos_por_examen
			WHERE id = $1',
			array(intval($_REQUEST['id'])))
		or die('La consulta fallo: ' . pg_last_error());

		$res = pg_fetch_array($result, null, PGSQL_ASSOC);
		$nota = $res['nota'];

		$result =  pg_query_params(
			$con, 
			'SELECT r2.duda AS duda, p.texto AS preg, r2.correcta AS cor, r2.texto AS res, r2.timestamp AS time, p.imagen AS img, id_materia
			FROM preguntas AS p INNER JOIN 
			(respuestas AS r INNER JOIN respuestas_por_alumno AS rpa ON r.id = rpa.id_respuesta) AS r2 ON p.id = r2.id_pregunta 
			WHERE r2.id_alumno_examen = $1
			ORDER BY time',
			array(intval($_REQUEST['id'])))
		or die('La consulta fallo: ' . pg_last_error());

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
				echo "<p>La nota es ".$nota.".</p>";
				echo "<p>A continuación aparecerán las respuestas. Aparecerán en rojo aquellas que sean incorrectas.</p>";
				echo "</div></div>";

				for ($i = 1; $res = pg_fetch_array($result, null, PGSQL_ASSOC); $i++) {
					echo "<section class=\"row\"><div class=\"col-md-12\">";

						echo "<h3>[Preg. #".$i."] ".$res['preg'].":</h3>";
						if (strlen($res['img']) >= 5) {
								echo "<img id=\"imagen\" src=\"../multimedia/".$res['id_materia']."/".$res['img']."\"/>"; //ID EXAMEN
						}

						if ($res['duda'] == 't') {
							echo "<div class=\"alert alert-info\" role=\"alert\"><p>Dudó</p></div>";
						}

						if ($res['cor'] == 't') {
							echo "<p class=\"correcta\">".$res['res'].".</p>";
						} else {
							echo "<p class=\"incorrecta\">".$res['res'].".</p>";
						}

					echo "</div></section>";
				}
			}

			?>
	</body>
</html>



