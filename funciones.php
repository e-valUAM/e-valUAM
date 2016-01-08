<?php
	require '/var/www/db_pass/db_string.php';

	function mostrar_header() {
		echo "<header class=\"container-fluid\">";
			echo "<div class=\"row\">";
				echo "<div class=\"col-md-3\">";
					echo "<img class=\"img-responsive\" id=\"logo_uam\" src=\"./multimedia/logos/uam.jpg\">";
				echo "</div>";
				echo "<div class=\"col-md-3 col-md-offset-6\">";
					echo "<img class=\"img-responsive\" id=\"logo_ope\" src=\"./multimedia/logos/ope.bmp\">";
				echo "</div>";
			echo "</div>";
		echo "</header>";
	}

	function connect() {
		if (strtos($_SERVER['PHP_SELF'], "develop") === FALSE)
			$con = pg_connect($db_string);
		else
			$con = pg_connect($db_develop_string);
		
		if (!$con)
			return NULL;
		else
			return $con;
	}
?>
