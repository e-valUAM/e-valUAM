<?php

	include 'funciones.php';
	
	session_start();

	$nota = (10.0 / ($_SESSION['num_preguntas'] * (2/3))) * ($_SESSION['numCorrectas'] - ($_SESSION['num_preguntas'] * (1/3)));

	if ($nota < 0) 
		$nota = 0;

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde. Si ves al técnico dile que "'. pg_last_error().'"');

    pg_query_params($con,
		'UPDATE alumnos_por_examen SET nota = $1 WHERE id = $2;',
		array($nota, $_SESSION['idAlumnoExamen']))
	or die('La actualizacion falló: '.pg_last_error());

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
			if (isset($_SESSION['feedback'])) {
				if ($_SESSION['correcta']) { 
		?>
			<div class="container-fluid">
			<div class="alert alert-success" role="alert">
			<button type="button" class="close" data-dismiss="alert">
			  <span aria-hidden="true">&times;</span>
			  <span class="sr-only">Cerrar</span>
			</button><p>¡Correcto! <?php echo $_SESSION['feedback'];?></p></div>
			</div>
		<?php 
				} else {
		?>
			<div class="container-fluid">
			<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert">
			  <span aria-hidden="true">&times;</span>
			  <span class="sr-only">Cerrar</span>
			</button><p>No… <?php echo $_SESSION['feedback'];?></p></div>
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
					or die('La busqueda falló: '.pg_last_error());

					$row = pg_fetch_array($result, null, PGSQL_ASSOC);

					if ($row['mostrar_resultados'] == 'parcial') {
						echo "<div class=\"row\"><div class=\"col-md-12\"><p>Tu nota es ".$nota.".</p></div></div>";
					} else if ($row['mostrar_resultados'] == 'completo') {
						$result =  pg_query_params(
							$con, 
							'SELECT p.texto AS preg, r2.correcta AS cor, r2.texto AS res, r2.timestamp AS time, p.imagen AS img
							FROM preguntas AS p INNER JOIN 
							(respuestas AS r INNER JOIN respuestas_por_alumno AS rpa ON r.id = rpa.id_respuesta) AS r2 ON p.id = r2.id_pregunta 
							WHERE r2.id_alumno_examen = $1
							ORDER BY time',
							array(intval($_SESSION['idAlumnoExamen'])))
						or die('La consulta fallo: ' . pg_last_error());

						echo "<div class=\"row\"><div class=\"col-md-12\"><h1>Resultados:</h1>";
						//echo "<p>Tu nota es ".number_format($nota, 2).".</p>";
						echo "<p>A continuación verás tus respuestas. Aparecerán en rojo aquellas que sean incorrectas.</p></div></div>";

						for ($i = 1; $res = pg_fetch_array($result, null, PGSQL_ASSOC); $i++) {
							echo "<div class=\"row\"><div class=\"col-md-12\"><section class=\"respuestas\">";
								echo "<p class=\"lead\">[Preg. #".$i."] ".$res['preg'].":</p>";
								if (strlen($res['img']) >= 5) {
										echo "<img id=\"imagen\" src=\"./multimedia/".$_SESSION['materias_id']."/".$res['img']."\"/>"; //ID EXAMEN
									}
								if ($res['cor'] == 't') {
									echo "<p class=\"correcta\">".$res['res']."</p>";
								} else {
									echo "<p class=\"incorrecta\">".$res['res']."</p>";
								}
							echo "</section></div></div>";
						}
					}
				?>
		</main>
		<footer class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<a class="btn btn-primary" href="index.php" role="button">Terminar</a>
				</div>
			</div>
		</footer>
	</body>
</html>


<?php
	unset($_REQUEST['idExamen']);
	unset($_SESSION['sigueExamen?']);
	unset($_SESSION['idExamen']);
	session_destroy();
?>