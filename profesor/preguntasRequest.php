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
	or die('Error. Prueba de nuevo más tarde.')

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
