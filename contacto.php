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
	require '/var/www/db_pass/mail.php';

	if (isset($_POST['email']) && isset($_POST['g-recaptcha-response'])) {
		if (verificar_captcha()) {
			$mensaje = "Nombre: {$_POST['nombre']}\nemail: {$_POST['email']}\nMensaje:\n{$_POST['mensaje']}";

			$resultado = enviar_email('Formulario de contacto en e-valUAM', $usuario_mail, $mensaje);

			if($resultado) {
				set_mensaje('ok', 'Tu mensaje se ha guardado. Gracias.');
				header('Location: index.php');
				exit;
			} else {
				set_mensaje('error', 'Ha habido un problema al guardar tu mensaje. Prubea de nuevo más tarde.');
			}
		}
	}

?>
<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Contacto</title>
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
		<?php mostrar_header_link(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Contacto</h1>
					<p>Si tienas alguna duda sobre la investigación, la página o cualquier otro tema, puedes utilizar el siguiente formulario.</p>
					<p>Utilizalo también si quieres recibir una copia del código o tienes alguna duda relativa a la licencia.</p>
					<?php
						if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'si')
							echo "<div class=\"alert alert-danger\" id='cajon-datos' role=\"alert\"><p>Los datos introducidos no coinciden. Prueba de nuevo.</p></div>";
					?>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<form action="contacto.php" method="post">
						<div class="form-group" id="cajon-datos">
							<label for="nombre">Tu nombre</label>
							<input type="text" class="form-control" name="nombre" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="email">Dirección de correo electrónico</label>
							<input type="email" class="form-control" name="email" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="mensaje">Mensaje</label>
							<textarea class="form-control" name="mensaje" rows="5" required></textarea>
						</div>
						<?php imprimir_captcha();	?>
						<button type="submit" class="btn btn-primary" value="Enviar">Enviar</button>
					</form>
					<br>
					<p><a href ="./index.php">Volver a la página principal</p>

				</div>
			</div>
		</main>
	</body>
</html>
