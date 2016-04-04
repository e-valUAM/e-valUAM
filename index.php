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

<?php include 'funciones.php'; ?>
<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="estilo.css">
		<link rel="shortcut icon" href="favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>
		<?php mostrar_header_link(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Bienvenido a e-valUAM 2.0</h1>
					<p>Para acceder es necesario que dispongas de una cuenta de usuario y de una contraseña.</p>
					<p>Si no tienes una cuenta de usuario <a href="nuevaCuenta.php">pulsa aquí</a>.</p>
					<?php
						if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'si')
							echo "<div class=\"alert alert-danger\" id='cajon-datos' role=\"alert\"><p>Los datos introducidos no coinciden. Prueba de nuevo.</p></div>";
					?>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<form action="eleccionExamen.php" method="post">
						<div class="form-group" id="cajon-datos">
							<label for="nombre">Nombre de usuario</label>
							<input type="text" class="form-control" name="nombre" placeholder="Introduce tu nombre de usuario">
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="nombre">Contraseña</label>
							<input type="password" class="form-control" name="contrasenya" placeholder="Tu contraseña">
						</div>
						<button type="submit" class="btn btn-primary" value="Continuar">Continuar</button>
					</form>
				<br>	<p><a href="recuperarContrasenya.php">¿Has olvidado la contraseña?</a></p>
						<p><a href="nuevaCuenta.php">Crear una cuenta nueva </a></p>
				</div>
			</div>
			<?php mostrar_licencia(); ?>
		</main>
	</body>
</html>
