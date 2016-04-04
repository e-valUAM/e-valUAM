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

			if(isset($_POST['contrasenya']) && isset($_POST['contrasenya2'])){

				if(	strcmp($_POST['contrasenya'],$_POST['contrasenya2'])){
					set_mensaje('error', 'Las contraseñas no coinciden, por favor inténtelo de nuevo');

				} else {
					$con = connect();
					if(!$con){
						set_mensaje('error', 'Error al conectarse con la base de datos');
						header("Location: ./nuevaCuenta.php");
						exit;
					}
			
					// Miramos si el correo no está repetido
					$result =  pg_query_params($con,
						'(SELECT nombre FROM alumnos WHERE nombre = $1)',
							array($_POST['email']));

				// Caso correo utilizado
				if (pg_num_rows($result) != 0) {
					set_mensaje('error', 'El correo introducido ya esta en uso, si ya tiene una cuenta y ha olvidado la contraseña
								pinche <a href="recuperarContrasenya.php">aquí</a>');
					header("Location: ./nuevaCuenta.php");
					exit;
				}
				}
			}
		}
	}

?>
<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Nueva Cuenta</title>
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
					<h1>Crear nueva Cuenta</h1>
					<p>La dirección de correo electrónico será utilizada para entrar en la página.</p>
					<p>Por favor asegurate que tienes acceso a ella. Todos los campos son obligatorios.</p>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<form action="nuevaCuenta.php" method="post">
						<div class="form-group" id="cajon-datos">
							<label for="email">Dirección de correo electrónico</label>
							<input type="email" class="form-control" name="email" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="email">Contraseña</label>
							<input type="password" class="form-control" name="contrasenya" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="email">Confirmar contraseña</label>
							<input type="password" class="form-control" name="contrasenya2" required>
						</div>
						<?php imprimir_captcha();	?>
						<button type="submit" class="btn btn-primary" value="Enviar">Enviar</button>
					</form>
				</div>
			</div>
		</main>
	</body>
</html>
