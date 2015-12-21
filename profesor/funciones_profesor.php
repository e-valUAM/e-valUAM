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
