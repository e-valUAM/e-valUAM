<!--
		e-valUAM: An adaptive questionnaire environment.
		e-valUAM: Un entorno de cuestionarios adaptativos.

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

	//Si el formulario ha sido enviado
	if (isset($_POST['email']) && isset($_POST['g-recaptcha-response'])) {
		
		//Verificacion del captcha
		if (verificar_captcha()) {

			//Las contraseñas no son vacias
			if(isset($_POST['contrasenya']) && isset($_POST['contrasenya2'])){

				//Caso contraseñas no coinciden
				if(	strcmp($_POST['contrasenya'],$_POST['contrasenya2'])){
					set_mensaje('error', 'Las contraseñas no coinciden, por favor inténtelo de nuevo.');

				//Error conexion base de datos
				} else {
					$con = connect();
					if(!$con){
						set_mensaje('error', 'Error al conectarse con la base de datos, prueba de nuevo más tarde.');
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
									pinche <a href="recuperarContrasenya.php">aquí</a>.');
						header("Location: ./nuevaCuenta.php");
						exit;

					// Añadimos la cuenta sin verificar a la base de datos
					} else {

						//Encriptamos la contraseña
						$salt = md5(devurandom_rand());
						$hashed_password = crypt($_POST['contrasenya'], $salt);

						//Generamos un token para la verifiacion
						$token = md5(devurandom_rand() . $_POST['email'] . time());

						//Inicialmente todas las cuentas tienen permiso de alumno y profesor

						//Campo profesor true-false

						$result =  pg_query_params($con,
						'INSERT INTO alumnos (nombre, cambio_contrasenya, profesor, pass,token,token_creation) VALUES ($1,false,false,$2,$3,now())',
							array($_POST['email'],$hashed_password,$token));

						//Caso de error al añadir
						if (!$result) {
							set_mensaje('error', 'Error en la base de datos, por favor intentelo más tarde');
							header("Location: ./nuevaCuenta.php");
							exit;
						}
						//Enviamos mensaje de verificacion
						$message = '<html><head><title>e-valUAM Nueva Cuenta</title></head>
										<body>
											<p>Recientemente has creado una cuenta en e-valUAM.</p>
											<p>Debes verificar la cuenta, haciendo click en el siguiente enlace para comenzar a usarla:</p>
								<p><a href="https://e-valuam.ii.uam.es/verificar.php?token='.urlencode($token).'&mail='.urlencode($_POST['email']).'">
									https://e-valuam.ii.uam.es/verificar.php?token='.urlencode($token).'&mail='.urlencode($_POST['email']).'</a></p>
											<p>Si no has sido tú, sencillamente ignora este mensaje.</p>
										</body>
						    		</html>';
						//Texto plano no soportado
						$mensaje_plano = 'Recientemente has creado una cuenta en e-valUAM.
							Debes verificar la cuenta, haciendo click en el siguiente enlace para comenzar a usarla:
							https://e-valuam.ii.uam.es/verificar.php?token=' . urlencode($token) . '&mail=' . urlencode($_POST['email']);

						$resultado = enviar_email('Creación de cuenta de e-valUAM', $_POST['email'], $message, TRUE, $mensaje_plano);

						//Todo OK
						if($resultado) {
							set_mensaje('ok', 'Se te ha mandado un correo electrónico con instrucciones para verificar la cuenta.
											Tras ello podrás comenzar a utilizar e-valUAM. Comprueba tu bandeja de entrada.');
						//Error en el envío del correo
						} else {
							set_mensaje('error', 'Error en el envío del correo de verificación. Inténtelo más tarde o pongase en contacto
									con el administrador a través del <a href="contacto.php">formulario de contacto</a>.');
						}
						header('Location: index.php');
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
							<input type="email" class="form-control" name="email" placeholder="Introduce tu dirección de correo" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="contrasenya">Contraseña</label>
							<input type="password" class="form-control" name="contrasenya" placeholder="Introduce tu contraseña" required>
						</div>
						<div class="form-group" id="cajon-datos">
							<label for="contrasenya2">Confirmar contraseña</label>
							<input type="password" class="form-control" name="contrasenya2" placeholder="Repite tu contraseña" required>
						</div>
						<?php imprimir_captcha();	?>

						<div id="mensaje" class="hidden alert alert-danger" role="alert"><p>Las contraseñas no coinciden.</p></div>
						<button type="submit" class="btn btn-primary" value="Continuar" disabled>Continuar</button>
					</form>
					<br>
					<p><a href ="./index.php">Volver a la página principal</p>

				</div>
			</div>
		</main>
	<script type="text/javascript">
				var nueva1 = $("input[name='contrasenya']");
				var nueva2 = $("input[name='contrasenya2']");

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
