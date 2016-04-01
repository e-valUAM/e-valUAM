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
		header("Location: ./index.php?error=si");
		exit;
	}

	$con = connect()
		or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');


	if (isset($_REQUEST['nombre'])) {

		$disponible = ($_REQUEST['disponible'] == 't' ? 't' : 'f');
		$duda = ($_REQUEST['duda'] == 't' ? 't' : 'f');
		$feedExamen = ($_REQUEST['feedbackExamen'] == 't' ? 't' : 'f');

		//print_r(array($_REQUEST['nombre'], $disponible, intval($_REQUEST['duracion']), intval($_REQUEST['idMateria']), intval($_REQUEST['numPreguntas']), $_REQUEST['mostrarResultados'], $duda));

		$result = pg_query_params($con,
			'INSERT INTO examenes (nombre, disponible, duracion, id_materia, num_preguntas, num_por_nodo, mostrar_resultados, acepta_duda,feedback) VALUES ($1, $2, $3, $4, $5, 1, $6, $7,$8);',
			array($_REQUEST['nombre'], $disponible, intval($_REQUEST['duracion']), intval($_REQUEST['idMateria']), intval($_REQUEST['numPreguntas']), $_REQUEST['mostrarResultados'], $duda,$feedExamen));

		if ($result) {
			header("Location: ./gestionExamenes.php?res=1");
			exit;
		}
	}

	header("Location: ./gestionExamenes.php?res=0");

?>
