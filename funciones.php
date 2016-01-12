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
		global $db_string;	
		$con = pg_connect($db_string);
		if (!$con)
			return NULL;
		else
			return $con;
	} 

	function devurandom_rand() {
		$fp = fopen('/dev/urandom','rb');
		$bytes = '';
		if ($fp !== FALSE) {
			$bytes .= fread($fp, 4);        
			fclose($fp);
		}

		if ($bytes === false || strlen($bytes) != 4) {
			throw new RuntimeException("Unable to get 4 bytes");
		}

		return $bytes;
	}

?>
