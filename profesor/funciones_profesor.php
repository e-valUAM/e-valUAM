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

	require '../funciones.php';

	function mostrar_header_profesor() {
		echo "<header class=\"container-fluid\">";
			echo "<div class=\"row\">";
				echo "<div class=\"col-md-3\">";
					echo "<img class=\"img-responsive\" id=\"logo_uam\" src=\"../multimedia/logos/uam.jpg\">";
				echo "</div>";
				echo "<div class=\"col-md-3 col-md-offset-6\">";
					echo "<img class=\"img-responsive\" id=\"logo_ope\" src=\"../multimedia/logos/ope.bmp\">";
				echo "</div>";
			echo "</div>";
		echo "</header>";
	}

	function check_login() {
		session_start();

		if (!isset($_SESSION['profesor'])) {
			header("Location: ./index.php");
	   		exit;
		}
	}

	function mostrar_navegacion_profesor($file) {

		echo "<nav class=\"container-fluid\">";
			echo "<ul class=\"nav nav-tabs nav-justified\">";
				echo "<li role=\"presentation\" ". ($file == 'gestionMaterias.php' ? "class=\"active\"" : "") ."><a href=\"gestionMaterias.php\">Materias</a></li>";
				echo "<li role=\"presentation\" ". ($file == 'gestionPreguntas.php' ? "class=\"active\"" : "") ."><a href=\"gestionPreguntas.php\">Preguntas</a></li>";
				echo "<li role=\"presentation\" ". ($file == 'gestionExamenes.php' ? "class=\"active\"" : "") ."><a href=\"gestionExamenes.php\">Exámenes</a></li>";
				echo "<li role=\"presentation\" ". ($file == 'gestionMultimedia.php' ? "class=\"active\"" : "") ."><a href=\"gestionMultimedia.php\">Ficheros multimedia</a></li>";
				echo "<li role=\"presentation\" ". ($file == 'recuperarExamenes.php' ? "class=\"active\"" : "") ."><a href=\"recuperarExamenes.php\">Recuperar examenes</a></li>";
				//echo "<li role=\"presentation\" ". ($file == 'estadisticas.php' ? "class=\"active\"" : "") ."><a href=\"estadisticas.php\">Estadísticas</a></li>";
				echo "<li role=\"presentation\" ><a href=\"../cambiarContrasenya.php\">Cambiar la contraseña</a></li>";
				echo "<li role=\"presentation\" ". ($file == 'ayuda.php' ? "class=\"active\"" : "") ."><a href=\"ayuda.php\">Ayuda y novedades <span class=\"text-info glyphicon glyphicon-bell\"  aria-hidden=\"true\"></span></a></li>";
				echo "<li role=\"presentation\" ><a href=\"salir.php\">Salir</a></li>";
			echo "</ul>";
		echo "</nav>";
	}

?>
