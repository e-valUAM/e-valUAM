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

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	session_start();

	if (isset($_REQUEST['contrasenya'])) {

    		$nombre = $_REQUEST['nombre'];
	    	$contrasenya = $_REQUEST['contrasenya'];

		$result =  pg_query_params($con, 'SELECT pass, id, cambio_contrasenya, profesor, admin, envio_preguntas,verificada FROM alumnos WHERE nombre =  $1', array($nombre))
		or die('Error. Prueba de nuevo más tarde.');

		$pass = pg_fetch_result($result, 0, 0);

		if (crypt($contrasenya, $pass) != $pass) {
			header("Location: ./index.php?error=si");
   			exit;
		}

	    if (pg_fetch_result($result, 0, 6) == "f"){
		//Caso de error
			set_mensaje('error', 'Su cuenta no ha sido verificada, actívela siguiendo las instrucciones del mensaje que enviamos a su correo');
			header('Location: index.php');
			exit;
		}


		$_SESSION = array();

		$_SESSION['nombreUsuario'] = $nombre;
		$_SESSION['idUsuario'] = pg_fetch_result($result, 0, 1);

		if (pg_fetch_result($result, 0, 2) == "t") {
			header("Location: ./cambiarContrasenya.php");
	   		exit;
		}

	        if (pg_fetch_result($result, 0, 3) == "t")
        	        $_SESSION['profesor'] = True;

	        if (pg_fetch_result($result, 0, 4) == "t")
        	       	$_SESSION['admin'] = True;

		$_SESSION['envio_preguntas'] = (pg_fetch_result($result, 0, 5) == 't' ? TRUE : FALSE);


		pg_free_result($result);
	} else if (!isset($_SESSION['idUsuario'])) {
		header("Location: ./index.php?error=si");
                 exit;
	}
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
		<?php mostrar_header_link(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>e-valUAM, un sistema de ayuda al aprendizaje.</h1>

					<p>El objetivo de e-valUAM es crear un modelo de estudio y de evaluación que ayude a asegurar que el alumno adquiere los conocimientos en el orden más adecuado: primero los conocimientos más básicos para construir desde ellos conocimientos más avanzados.</p>
					<p>Para ello las pruebas que e-valUAM genera son pruebas tipo test donde cada nueva pregunta viene influida por las respuestas anteriores. Así, un alumno que responda correctamente preguntas básicas irá subiendo hacía preguntas más avanzadas.</p>
					<p>Cada vez que un alumno accede a una prueba, estas se generan de forma aleatoria, por lo que cada vez debería acceder a un reto nuevo, hasta que sea capaz de dominar el tema.</p>
					<p>Las pruebas no están diseñadas solo para la evaluación de cara a una nota final, sino que también se pensaron para que el alumno pueda hacer las pruebas por su cuenta de cara a realizar una auto evaluación que le permita conocer mejor cómo va desarrollando su proceso de aprendizaje.</p>
				</div>
			</div>

		<?php
			if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'si')
				echo "<div class=\"alert alert-danger\" id='cajon-datos' role=\"alert\">
						<p>Se ha producido un error en el examen</p>
					  </div>";
		?>

			<div class="row">
				<div class="col-md-12">
					<p class="lead">En la tabla siguiente encontrarás un listado de todas las pruebas disponibles actualmente. Selecciona una prueba y pulsa continuar. En ese momento empezará la prueba.</p>
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Asignatura</th>
								<th>Materia</th>
								<th>Duración</th>
								<th>Seleccionar</th>
							</tr>
						</thead>
						<tbody>

					<?php
						/*
							//Aquí estaba el link hacia el fichero con el iframe a google forms
							//Actualmente quitado
							<tr>
								<td> Cuestionario sobre hábitos en el uso de videojuegos</td>
								<td> e-valUAM </td>
								<td> Videojuegos </td>
								<td> - </td>
								<td><a class="btn btn-primary" href="gammingTest.php">Continuar</a></td>
							</tr>
						*/
					?>

							<?php
								$result = pg_query_params($con, 'SELECT e.id, e.nombre, e.duracion,m.nombre AS materia,a.nombre AS asignatura 
											FROM examenes AS e
											INNER JOIN materias as m ON m.id = id_materia
											INNER JOIN asignaturas AS a on id_asignatura = a.id
											WHERE disponible = true AND comienzo < now() AND now() < comienzo + tiempo_disponible 
											AND e.borrado = false AND a.borrada = false 
											AND a.id IN (SELECT s.id_asignatura FROM alumno_por_asignaturas AS s WHERE s.id_alumno = $1 
											AND activo=true)
											ORDER BY e.id', array($_SESSION['idUsuario']) )
								or die('Error. Prueba de nuevo más tarde.');

								// Imprimiendo los resultados en HTML
								while ($line = pg_fetch_array($result, null)) {
								    //if ($_SESSION['idUsuario'] >= 1213 && $_SESSION['idUsuario'] <= 1264 && ($line['id'] != 46 && $line['id'] != 49))
									//continue;

								    echo "\t<tr>\n";
								    echo "\t\t<td>".$line['nombre']."</td>\n";
								    echo "\t\t<td>".$line['asignatura']."</td>\n";
								    echo "\t\t<td>".$line['materia']."</td>\n";
								    echo "\t\t<td>".$line['duracion']."'</td>\n";
								    echo "\t\t<td><a class=\"btn btn-primary\" href=\"Examen.php?idExamen=".$line['id']."\">Continuar</a></td>";
								    echo "\t</tr>\n";
								}

								// Liberando el conjunto de resultados
								pg_free_result($result);
							?>
						</tbody>
					</table>
					<p>Puedes inscribirte a nuevas asignaturas <a href="./eleccionAsignaturas.php">aquí.</a></p>
					<p>Si quieres cambiar tu contraseña, pulsa <a href="./cambiarContrasenya.php">aquí.</a></p>
				</div>
			</div>
		</main>
		<?php mostrar_licencia(); ?>
	</body>
</html>
