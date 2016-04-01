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

	//check_login();

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

    session_start();
    if (!isset($_SESSION['nombreUsuario'])) {
    	header("Location: ./index.php");
    	exit;
    }

    if (isset($_REQUEST['antigua'])) {

		$result =  pg_query_params($con, 'SELECT pass FROM alumnos WHERE id =  $1', array($_SESSION['idUsuario']))
		or die('Error. Prueba de nuevo más tarde.');

		$pass = pg_fetch_result($result, 0, 0);

		$errorAntigua = FALSE;
		$errorNuevas = FALSE;
		$errorActulizacion = FALSE;
		$correcto = FALSE;

		if (crypt($_REQUEST['antigua'], $pass) != $pass) {
			$errorAntigua = TRUE;
		} else if ($_REQUEST['nueva1'] != $_REQUEST['nueva2']) {
			$errorNuevas = TRUE;
		} else {
			$salt = md5(devurandom_rand());
			$hashed_password = crypt($_REQUEST['nueva1'], $salt);

			if (pg_affected_rows(pg_query($con, "UPDATE alumnos SET pass = '".$hashed_password."', cambio_contrasenya = FALSE WHERE id = ".$_SESSION['idUsuario'])) == 0)
				$errorActulizacion = TRUE;
			else {
				$correcto = TRUE;
			}
		}

	}

?>
<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Cambiar contraseña</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="estilo.css">
		<link rel="shortcut icon" href="favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>
		<?php mostrar_header(); ?>

		<main class="container-fluid">
			<?php if ($errorAntigua) { ?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger" role="alert"><p>Contraseña antigua no válida. Vuelve a probar.</p></div>
					</div>
				</div>
			<?php } else if ($errorNuevas) {?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger" role="alert"><p>Las contraseñas no coinciden. Vuelve a probar.</p></div>
					</div>
				</div>
			<?php } else if ($errorActulizacion) {?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger" role="alert"><p>Error al actualizar tu contraseña. Prueba de nuevo más adelante.</p></div>
					</div>
				</div>
			<?php } else if ($correcto) {?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-success" role="alert"><p>Contraseña correctamente actualizada. <a class="alert-link" href="index.php">Volver.</a></p></div>
					</div>
				</div>
			<?php } ?>

			<div class="row">
				<div class="col-md-12">
					<h1>Cambio de contraseña</h1>
					<p>Antes de continuar es necesario que cambies tu contraseña.</p>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<form action="cambiarContrasenya.php" method="post">
						<div class="form-group">
							<label for="antigua">Contraseña antigua</label>
							<input type="password" class="form-control" name="antigua" placeholder="Introduce tu antigua contraseña">
						</div>
						<div class="form-group">
							<label for="nueva1">Contraseña nueva</label>
							<input type="password" class="form-control" name="nueva1" placeholder="Tu nueva contraseña">
						</div>
						<div class="form-group">
							<label for="nueva2">Repite tu nueva contraseña</label>
							<input type="password" class="form-control" name="nueva2" placeholder="Repite tu nueva contraseña">
						</div>

						<div id="mensaje" class="hidden alert alert-danger" role="alert"><p>Las contraseñas no coinciden.</p></div>

						<button type="submit" class="btn btn-primary" value="Continuar" disabled>Continuar</button>
					</form>
				</div>
			</div>
		</main>
		<script type="text/javascript">
			var nueva1 = $("input[name='nueva1']");
			var nueva2 = $("input[name='nueva2']");

			function checkContrasenyas() {

				if (nueva1.val() == nueva2.val()) {
					$("button[value='Continuar']").removeAttr("disabled");
					if (!$("#mensaje").hasClass("hidden"))
						$("#mensaje").addClass("hidden");
				} else {
					if (!$("button[value='Continuar']").attr("disabled"))
						$("button[value='Continuar']").attr("disabled", "disabled");
					$("#mensaje").removeClass("hidden");
				}
			}

			nueva1.keyup(checkContrasenyas);
			nueva2.keyup(checkContrasenyas);
		</script>
	</body>
</html>
