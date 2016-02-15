
<?php
	include 'funciones.php';
	
	if (isset($_POST['token']) && isset($_POST['email']) && isset($_POST['nueva'])) {
		$con = connect() or die ('Error. Prueba de nuevo más tarde');

		$result =  pg_query_params($con, "
			SELECT nombre 
			FROM alumnos 
			WHERE nombre =  $1 
			AND token_creation + interval 3 'hours' > NOW()
			AND token = $2
			", array($_POST['email'], $_POST['token']));

		if (pg_num_rows($result) == 1) {
			$result = pg_query_params($con, "UPDATE alumnos SET pass = $1, token = NULL, toke_creation = NULL WHERE nombre = $2", array($_POST['nueva'], $_POST['email']));

			if (pg_affected_rows($result) == 1) {
				set_mensaje('ok', 'Tu contraseña se ha cambiado. Ya puedes usarla.');
				header('Location: index.php');
				exit;
			}
		}

		set_mensaje('error', 'No se ha podido completar tu solicitud. Si aún quieres, puedes pedir una nueva contraseña.');
		header('Location: recuperarContrasenya.php');
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
					<form action="comprobarFormulario()" method="post">
						<div class="form-group">
							<label for="email">Dirección de email</label>
    							<input type="email" class="form-control" id="email" value="<?php echo $_GET['mail']; ?>">
						</div>
						<div class="form-group pass">
							<label for="nueva">Nueva contraseña</label>
							<input type="password" class="form-control" id="nueva">
						</div>
						<div class="form-group pass">
							<label for="nueva2">Repite la contraseña</label>
							<input type="password" class="form-control" id="nueva2">
						</div>
						<input type="hidden" id="token" value="<?php echo $_GET['token']; ?>">
						<button type="submit" class="btn btn-primary" value="Continuar">Guardar</button>
					</form>
				</div>
			</div>
		<script>
			var iguales = false;
			
			$('input:password').change(function () {
				var nueva = $('.nueva').value;
				var nueva2 = $('.nueva2').value;

				if (nueva.lenght >= 1 && nueva.localeCompare(nueva2) == 0) {
					iguales = true;
					$('#pass').removeClass('has-error');
				} else {
					iguales = false;
					$('#pass').addClass('has-error');
				}
			});
			
			function comprobarFormulario() {
				return iguales;	
			}
		</script>
		</main>
	</body>
</html>
