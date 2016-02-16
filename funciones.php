<?php
	require '/var/www/db_pass/db_string.php';

	session_start();

	function mostrar_mensaje() {
		if (isset($_SESSION['_mensaje'])) {
			$tipo = '';
			switch ($_SESSION['_mensaje']['tipo']) {
				case 'ok':
					$tipo = 'alert-success';
					break;
				case 'error':
					$tipo = 'alert-danger';
					break;
				case 'aviso':
					$tipo = 'alert-warning';
					break;
				case 'info':
					$tipo = 'alert-info';
					break;
			}

			echo '<div class="alert alert-dismissible ' . $tipo . ' fade in mensajes" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
  				<span aria-hidden="true">&times;</span>
				</button>
				<p>' . $_SESSION['_mensaje']['texto'] . '</p>
			      </div>';

			$_SESSION['_mensaje'] = NULL;
		}
	}

	function set_mensaje($tipo, $mensaje) {
		$_SESSION['_mensaje'] = array('tipo' => $tipo, 'texto' => $mensaje);
	}

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
			echo '<div class="row">';
				echo '<div class="col-md-6">';
					mostrar_mensaje();
				echo '</div>';
			echo '</div>';
		echo "</header>";
	}

	function mostrar_header_link() {
		echo "<header class=\"container-fluid\">";
			echo "<div class=\"row\">";
				echo "<div class=\"col-md-3\">";
					echo "<a href='http://www.uam.es/ss/Satellite/es/home/'>";
					echo "<img class=\"img-responsive\" id=\"logo_uam\" src=\"./multimedia/logos/uam.jpg\"></a>";
				echo "</div>";
				echo "<div class=\"col-md-3 col-md-offset-6\">";
					echo "<a href='https://www.uam.es/europea/'>";
					echo "<img class=\"img-responsive\" id=\"logo_ope\" src=\"./multimedia/logos/ope.bmp\"></a>";
				echo "</div>";
			echo "</div>";
			echo '<div class="row">';
				echo '<div class="col-md-6">';
					mostrar_mensaje();
				echo '</div>';
			echo '</div>';
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
