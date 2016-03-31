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

	if (isset($_POST['token']) && isset($_POST['email']) && isset($_POST['nueva'])) {
		$con = connect() or die ('Error. Prueba de nuevo más tarde');

		$result =  pg_query_params($con, "
			SELECT nombre
			FROM alumnos
			WHERE nombre =  $1
			AND token_creation + interval '3 hours' > NOW()
			AND token = $2
			", array($_POST['email'], $_POST['token']));

		if (pg_num_rows($result) == 1) {
			$salt = md5(devurandom_rand());
			$hashed_password = crypt($_POST['nueva'], $salt);

			if (crypt($_POST['nueva'], $hashed_password) == $hashed_password) {
				$result = pg_query_params($con, "UPDATE alumnos SET pass = $1, token = NULL, token_creation = NULL WHERE nombre = $2", array($hashed_password, $_POST['email']));

				if (pg_affected_rows($result) == 1) {
					set_mensaje('ok', 'Tu contraseña se ha cambiado. Ya puedes usarla.');
					header('Location: index.php');
					exit;
				}
			}
		}

		set_mensaje('error', 'No se ha podido completar tu solicitud. Repite el proceso.');
		header('Location: recuperarContrasenya.php');
		exit;
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
					<h1>Cambio de contraseña</h1>
					<p>Ahora puedes establecer tu nueva contraseña:</p>
					<form action="token.php" onsubmit="return comprobarFormulario()" method="post">
						<div class="form-group" id="cajon-datos">
							<label class="control-label" for="email">Dirección de email</label>
    							<input type="email" class="form-control" name="email" value="<?php echo $_GET['email']; ?>">
						</div>
						<div class="form-group pass" id="cajon-datos">
							<label class="control-label" for="nueva">Nueva contraseña</label>
							<input type="password" class="form-control" name="nueva" id="nueva">
						</div>
						<div class="form-group pass" id="cajon-datos">
							<label class="control-label" for="nueva2">Repite la contraseña</label>
							<input type="password" class="form-control" name="nueva2" id="nueva2">
						</div>
						<input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
						<button type="submit" class="btn btn-primary" value="Continuar">Guardar</button>
					</form>
				</div>
			</div>
		<script>
			var iguales = false;

			$('input:password').on('change keyup', function () {
				console.log('ey');
				var nueva = $('input[name=nueva]').val();
				var nueva2 = $('input[name=nueva2]').val();
				console.log(nueva + ' ' + nueva2);
				console.log(nueva.length);

				if (nueva.length >= 1 && nueva === nueva2) {
					console.log('si');
					iguales = true;
					$('.pass').removeClass('has-error');
				} else {
					console.log('no');
					iguales = false;
					$('.pass').addClass('has-error');
				}
			});

			function comprobarFormulario() {
				return iguales;
			}
		</script>
		</main>
	</body>
</html>
