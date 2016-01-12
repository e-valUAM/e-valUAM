<?php
	include 'funciones.php';
	
	$error_captcha = FALSE;

	// Si llegamos a la página con el POST, es que se ha rellenado ya el formulario
	if (isset($_POST['email']) && isset($_POST['g-recaptcha-response']) {
		require '/var/www/db_pass/recaptcha.php';
		
		// Verificamos el captcha
		$ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('secret' => $secret, 'response' => $_POST['g-recaptcha-response']));

		$result = curl_exec($ch);

		curl_close($ch);

		if ($result) {
			// Hemos validado el captcha correctamente
			$con = connect() or die ('Error. Prueba de nuevo más tarde');

			if ($con) {
				// Comprbamos en la base de datos que hay un usuario con ese email
				$result =  pg_query_params($con, 'SELECT * FROM alumnos WHERE nombre =  $1', array($_SESSION['email']))
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
					
					// Y mandamos un mensaje con él
					$message = '<html><head><title>Recuperación de contraseña</title></head>
							<body>
								<p>Recientemente has solicitado una nueva contraseña para tu cuenta de e-valUAM.</p>
								<p>En el siguiente enlace podrás recuperar tu contraseña: 
								<a href="https://e-valuam.ii.uam.es/recuperarContrasenya.php?token=' . urlencode($token) . '">
								https://e-valuam.ii.uam.es/recuperarContrasenya.php?token=' . urlencode($token) . '</a></p>
								<p>Si no has sido tú, sencillamente ignora este mensaje.</p>
							</body>
						    </html>';
					$headers = 'MIME-Version: 1.0' . "\r\n" .
							'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
							' Reply-To: pablo.molins@uam.es' . "\r\n" .
							'X-Mailer: PHP/' . phpversion();

					mail($_POST['email'], 'Recuperación de contraseña', $message, $headers);
				} 
			}
		} else {
			$error_captcha = TRUE;
		}			
	} else if (isset($_GET['token'])) { // Llamada para cambiar, definitivamente, la contraseña 
		
	} else if (isset($_POST['token']) && isset($_POST[''])) {
		
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
					<form action="recuperarContrasanye.php" method="post">
						<div class="form-group">
							<label for="exampleInputEmail1">Dirección de email</label>
    							<input type="email" class="form-control" id="email" placeholder="Email">
						</div>
						<div class="g-recaptcha" data-sitekey="6LdlFxUTAAAAANXsSWJGN4EieWQTq0HiLNY9nH5L"></div>
					</form>
				</div>
			</div>
		</main>
	</body>
</html>
