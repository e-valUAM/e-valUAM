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

<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Zona del profesor</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="../favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>

		<script type="text/javascript">
			$(function () {$('[data-toggle="popover"]').popover()})
		</script>

	<?php include 'funciones_profesor.php'; mostrar_header_profesor(); ?>

		<section class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Bienvenido a e-valUAM 2.0 - Zona del profesor</h1>
					<p>Para acceder es necesario que dispongas de una cuenta de usuario y de una contraseña.</p>
					<p>Si no tienes una cuenta de usuario <a href="../nuevaCuenta.php">pulsa aquí</a>.  
					<span class="glyphicon glyphicon-question-sign" data-toggle="popover" title="Cuentas" 
						data-content="En e-valUAM todas las cuentas permiten tanto realizar exámenes, como gestionar asignaturas propias, dependiendo si se accede desde la zona del alumno o del profesor" data-trigger="click hover">
						<span class="sr-only">Información</span>
						</span>
					</p>
					<p> Si quiere realizar exámenes debe acceder desde la <a href="../index.php">zona para alumnos</a>.</p>
				</div>
			</div>

			<?php
			if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'si') {
			?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger" id=cajon-datos role="alert">
							<p class="error">Los datos introducidos no son válidos. Prueba de nuevo.</p>
						</div>
					</div>
				</div>
			<?php
			}
			?>
			<div class="row">
				<div class="col-md-12">
					<form role="form" action="profesor.php" method="post">
						<div class="form-group" id=cajon-datos>
							<label for="nombre">Correo electrónico / Nombre de usuario</label>
							<input type="text" class="form-control" name="nombre" placeholder="Introduce tu nombre de usuario">
						</div>
						<div class="form-group" id=cajon-datos>
							<label for="nombre">Contraseña</label>
							<input type="password" class="form-control" name="contrasenya" placeholder="Tu contraseña">
						</div>
						<button type="submit" class="btn btn-primary" value="Continuar">Continuar</button>
					</form>
				<br>	<p><a href="../recuperarContrasenya.php">¿Has olvidado la contraseña?</a></p>
						<p><a href="../nuevaCuenta.php">Crear una cuenta nueva </a></p>
				</div>
			</div>
		</section>
	</body>
</html>
