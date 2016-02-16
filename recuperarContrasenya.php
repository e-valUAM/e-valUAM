<?php
	include 'funciones.php';
	
	$error_captcha = FALSE;

	// Si llegamos a la página con el POST, es que se ha rellenado ya el formulario
	if (isset($_POST['email']) && isset($_POST['g-recaptcha-response'])) {
		require '/var/www/db_pass/recaptcha.php';
		
		// Verificamos el captcha
		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('secret' => $secret, 'response' => $_POST['g-recaptcha-response']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		$result = json_decode(curl_exec($ch));
		curl_close($ch);
		//echo $result;

		if ($result->{'success'}) {
			// Hemos validado el captcha correctamente
			$con = connect() or die ('Error. Prueba de nuevo más tarde');

			if ($con) {
				// Comprbamos en la base de datos que hay un usuario con ese email
				$result =  pg_query_params($con, 'SELECT * FROM alumnos WHERE nombre =  $1', array($_POST['email']))
				or die('Error. Prueba de nuevo más tarde.');

				if (pg_num_rows($result) == 1) {
					// Si el usuario está en la base de datos, generamos un token aleatorio 
					$token = md5(devurandom_rand() . $_POST['email'] . time());

					// Lo guardamos en la BD
					$result = pg_query_params($con, 
						'UPDATE alumnos SET token = $1, token_creation = now() WHERE nombre = $2', 
						array($token,$_POST['email'] ));
	
					if (pg_affected_rows($result) != 1) {
						die('Error. Prueba de nuevo más tarde.');
					}

					require __DIR__ . '/vendor/autoload.php';	
					require '/var/www/db_pass/mail.php';					

					$message = '<html><head><title>Recuperación de contraseña</title></head>
							<body>
								<p>Recientemente has solicitado una nueva contraseña para tu cuenta de e-valUAM.</p>
								<p>En el siguiente enlace podrás recuperar tu contraseña: 
								<a href="https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '">
								https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '</a></p>
								<p>Si no has sido tú, sencillamente ignora este mensaje.</p>
							</body>
						    </html>';

					$mail = new PHPMailer;

					$mail->isSMTP();    // Set mailer to use SMTP
					$mail->Host = 'smtpinterno.uam.es';  // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                               // Enable SMTP authentication
					$mail->Username = $usuario_mail;              // SMTP username
					$mail->Password = $clave_mail;                           // SMTP password
					$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 587;                                    // TCP port to connect to

					$mail->setFrom($usuario_mail, 'Administrador e-valUAM');
					$mail->addAddress($_POST['email']);     // Add a recipient
					$mail->isHTML(true);                                  // Set email format to HTML
					$mail->Subject = 'Recuperación de contraseña e-valUAM';
					$mail->Body    = $message;
					$mail->AltBody = 'Recientemente has solicitado una nueva contraseña para tu cuenta de e-valUAM. En el siguiente enlace podrás recuperar tu contraseña: https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '&mail=' . urlencode($_POST['email']);
					$mail->CharSet = 'UTF-8';

					if($mail->send()) {
						set_mensaje('ok', 'Se te ha mandado un correo electrónico con instrucciones. Comprueba tu bandeja de entrada.');
						header('Location: index.php');
						exit;
					}
				} 
			}
		} 
		set_mensaje('error', 'Se ha producido un error. Prueba de nuevo.');
	}

?>
<!DOCTYPE html>

<html>
        <head>
                <title>e-valUAM 2.0 - Recuperar contraseña</title>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <link rel="stylesheet" type="text/css" href="estilo.css">
                <link rel="shortcut icon" href="favicon.png" type="image/png"/>
                <!-- bootstrap -->
                <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
                <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
                <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        	<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>

        <body>
                <?php mostrar_header(); ?>
		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Recuperación de contraseña</h1>
					<p>Para obtener una nueva contraseña, escribe tu dirección de correo electrónico, pulsa el botón de "No soy un robot" y pulsa continuar.</p>
					<form action="recuperarContrasenya.php" method="post">
						<div class="form-group" id="cajon-datos">
							<label for="email">Dirección de email</label>
    							<input type="email" class="form-control" name="email">
						</div>
						<div class="form-group">
							<div class="g-recaptcha" data-sitekey="6LdlFxUTAAAAANXsSWJGN4EieWQTq0HiLNY9nH5L"></div>
						</div>
						<button type="submit" class="btn btn-primary" value="Continuar">Continuar</button>
					</form>
				</div>
			</div>
		</main>
	</body>
</html>
