<?php

	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (!isset($_REQUEST['idMateria'])) {
		echo "0";
		die();
	}

	// Visualizado de la tabla con todas las preguntas de una materia

	$result =  pg_query_params($con, 
		'SELECT texto, imagen, dificultad, id
		FROM preguntas
		WHERE id_materia = $1 AND borrada = FALSE
		ORDER BY dificultad,id',
		array($_REQUEST['idMateria']))
	or die('La consulta fallo: ' . pg_last_error());

	echo "<thead><tr>";
	echo "<th>Id</th><th>Dificultad</th><th>Enunciado</th><th>Imagen</th><th>Opciones</th>";

	echo "</tr></thead>";


	if (pg_num_rows($result) == 0) {
		echo "<tr><td>Aún no hay datos para mostrar.</td><td></td><td></td><td></td><td></td></tr>";
	} else {
		while ($data = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			echo "<tr><td>".$data['id']."</td>";
			echo "<td>".$data['dificultad']."</td>";
			echo "<td>".$data['texto']."</td>";
			if ($data['imagen'] == 'null' || $data['imagen'] == '')
				echo "<td></td>";
			else {
				$src = "../multimedia/".$_REQUEST['idMateria']."/".$data['imagen'];
				echo "<td><a target=\"_blank\" href=\"".$src."\"><img class=\"mini_imagen\" src=\"$src\" alt=\"Imagen de la pregunta ".$data['id']."\"></a></td>";
			}
			echo "<td>";
			echo "<button type=\"button\" onClick=\"editarPregunta(".$data['id'].")\" class=\"btn btn-primary btn-warning\" data-toggle=\"modal\" data-target=\"#myModal\"><span class=\"glyphicon glyphicon-edit\" aria-hidden=\"true\"></span></button>";
			echo "<button type=\"button\" onClick=\"borrarPregunta(".$data['id'].")\" class=\"btn btn-danger\"><span class=\"glyphicon glyphicon-trash\" aria-hidden=\"true\"></span></button>";
			echo "</td></tr>";		
		}
	}
	

	
?>
