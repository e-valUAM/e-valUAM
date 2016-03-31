<?php
	require '/var/www/db_pass/db_string.php';

	session_start();


	// Funciones para ayudar al desarrollo del front-end

	// Hay que incluir <script src='https://www.google.com/recaptcha/api.js'></script> en el header de la página
	function imprimir_captcha() {
		echo '<div class="form-group">';
			echo '<div class="g-recaptcha" data-sitekey="6LdlFxUTAAAAANXsSWJGN4EieWQTq0HiLNY9nH5L"></div>';
		echo '</div>';
	}

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

	function mostrar_licencia() {
		echo '<footer id="licencia" class="container-fluid">';
			echo '<div class="col-md-12">';
				echo '<p>Software desarrollado en la Universidad Autónoma de Madrid bajo una licencia <a href="http://www.gnu.org/licenses/agpl.html">GNU Affero General Public License</a>.</p>';
				echo '<p>¿Quieres una copia del código o más información? <a href="contacto.php">Pulsa aquí.</a></p>';
			echo '</div>';
		echo '</footer>';
	}

	// Funciones back-end

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

	function verificar_captcha() {
		require '/var/www/db_pass/recaptcha.php';

		// Verificamos el captcha
		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('secret' => $secret, 'response' => $_POST['g-recaptcha-response']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$result = json_decode(curl_exec($ch));
		curl_close($ch);

		return $result->{'success'};
	}

	function enviar_email($asunto, $destinatario, $mensaje, $html=FALSE, $mensaje_plano=NULL)
	{
		require __DIR__ . '/vendor/autoload.php';
		require '/var/www/db_pass/mail.php';

		$mail = new PHPMailer;

		$mail->isSMTP();    // Set mailer to use SMTP
		$mail->Host = 'smtpinterno.uam.es';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $usuario_mail;              // SMTP username
		$mail->Password = $clave_mail;                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to

		$mail->setFrom($usuario_mail, 'Administrador e-valUAM');
		$mail->addAddress($destinatario);     // Add a recipient
		$mail->isHTML($html);                                  // Set email format to HTML
		$mail->Subject = $asunto;
		$mail->Body    = $mensaje;
		if ($html)
			$mail->AltBody = $mensaje_plano;
		$mail->CharSet = 'UTF-8';

		return $mail->send();
	}

?>
