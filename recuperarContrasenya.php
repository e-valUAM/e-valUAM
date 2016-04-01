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
	include 'funciones.php';

	$error_captcha = FALSE;

	// Si llegamos a la página con el POST, es que se ha rellenado ya el formulario
	if (isset($_POST['email']) && isset($_POST['g-recaptcha-response'])) {
		if (verificar_captcha()) {
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



					$message = '<html><head><title>Recuperación de contraseña</title></head>
							<body>
								<p>Recientemente has solicitado una nueva contraseña para tu cuenta de e-valUAM.</p>
								<p>En el siguiente enlace podrás recuperar tu contraseña:
								<a href="https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '">
								https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '</a></p>
								<p>Si no has sido tú, sencillamente ignora este mensaje.</p>
							</body>
						    </html>';

					$mensaje_plano = 'Recientemente has solicitado una nueva contraseña para tu cuenta de e-valUAM.
						En el siguiente enlace podrás recuperar tu contraseña:
						https://e-valuam.ii.uam.es/token.php?token=' . urlencode($token) . '&mail=' . urlencode($_POST['email']);

					$resultado = enviar_email('Recuperar la contraseña de e-valUAM', $_POST['email'], $message, TRUE, $mensaje_plano);

					if($resultado) {
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
						<?php imprimir_captcha(); ?>
						<button type="submit" class="btn btn-primary" value="Continuar">Continuar</button>
					</form>
				</div>
			</div>
		</main>
	</body>
</html>
