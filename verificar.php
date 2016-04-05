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

	if (isset($_GET['token']) && isset($_GET['mail'])) {
		$con = connect();

		if(!$con){
			set_mensaje('error', 'Error al establecer conexión con la base de datos');
			header('Location: index.php');
			exit;
		}

		$result =  pg_query_params($con, "
			SELECT nombre FROM alumnos
			WHERE nombre =  $1
			AND token_creation + interval '3 days' > NOW()
			AND token = $2
			", array($_GET['mail'], $_GET['token']));

		//Caso token activo
		if (pg_affected_rows($result) == 1) {

			$result =  pg_query_params($con, "
				UPDATE alumnos SET verificada = true WHERE nombre = $1;
				", array($_GET['mail']));
			if($result){
				set_mensaje('ok', 'Tu cuenta '.$_GET['mail'].' ha sido verificada, ya puedes comenzar a usarla');
			} else {
				set_mensaje('error', 'Error al verificar la cuenta, intentelo de nuevo más tarde');
			}
			header('Location: index.php');
			exit;
			}

		//Caso token caducado
		$result =  pg_query_params($con, "
			SELECT nombre FROM alumnos
			WHERE nombre =  $1
			AND token = $2
			", array($_GET['mail'], $_GET['token']));

		if (pg_affected_rows($result) == 1) {

			//Generamos un token para la verifiacion
			$token = md5(devurandom_rand() . $_POST['email'] . time());
			$result =  pg_query_params($con, "
					UPDATE alumnos SET token = $1,token_creation = now()  WHERE nombre = $2;
					", array($token,$_GET['mail'])) or die("Error insertando token");

			//Enviamos mensaje de verificacion
			$message = '<html><head><title>e-valUAM Nueva Cuenta</title></head>
							<body>
								<p>Recientemente has creado una cuenta en e-valUAM.</p>
								<p>Debes verificar la cuenta, haciendo click en el siguiente enlace para comenzar a usarla:
						<a href="https://e-valuam.ii.uam.es/verificar.php?token='.urlencode($token)."&mail=".urlencode($_GET['mail']).'>
						https://e-valuam.ii.uam.es/verificar.php?token='.urlencode($token).'&mail='.urlencode($_GET['mail']).'</a></p>
								<p>Si no has sido tú, sencillamente ignora este mensaje.</p>
							</body>
						</html>';
			//Texto plano no soportado
			$mensaje_plano = 'Recientemente has creado una cuenta en e-valUAM.
				Debes verificar la cuenta, haciendo click en el siguiente enlace para comenzar a usarla:
				https://e-valuam.ii.uam.es/verificar.php?token=' . urlencode($token) . '&mail=' . urlencode($_GET['mail']);

			$resultado = enviar_email('Recuperar la contraseña de e-valUAM', $_GET['mail'], $message, TRUE, $mensaje_plano);

			//Todo OK
			if($resultado) {
				set_mensaje('ok', 'El enlace de verificación ha caducado. Se ha reenviado otro correo. Comprueba tu bandeja de entrada.');
			//Error en el envío del correo
			} else {
				set_mensaje('error', 'Error en el envío del correo de verificación. Inténtelo más tarde o pongase en contacto
					con el administrador a través del <a href="contacto.php">formulario de contacto</a>');
			}
			header('Location: index.php');
			exit;
		}


	}
	//Caso de error
	set_mensaje('error', 'No se ha podido completar tu solicitud. Inténtelo más tarde o pongase en contacto
					con el administrador a través del <a href="contacto.php">formulario de contacto</a>');
	header('Location: index.php');
	exit;
	
?>
