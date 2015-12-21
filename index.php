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
		<?php mostrar_header(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>Bienvenido a e-valUAM 2.0</h1>
					<p>Para acceder es necesario que dispongas de una cuenta de usuario y de una contraseña.</p>
					<p>Si no tienes una cuenta de usuario, o si has olvidado la clave, pregunta a tu profesor al respecto.</p>
					<?php
						if (isset($_REQUEST['error']) && $_REQUEST['error'] == 'si')
							echo "<div class=\"alert alert-danger\" role=\"alert\"><p>Los datos introducidos no coinciden. Prueba de nuevo.</p></div>";
					?>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<form action="eleccionExamen.php" method="post">
						<div class="form-group">
							<label for="nombre">Nombre de usuario</label>
							<input type="text" class="form-control" name="nombre" placeholder="Introduce tu nombre de usuario">
						</div>
						<div class="form-group">
							<label for="nombre">Contraseña</label>
							<input type="password" class="form-control" name="contrasenya" placeholder="Tu contraseña">
						</div>
						<button type="submit" class="btn btn-primary" value="Continuar">Continuar</button>
					</form>
				</div>
			</div>
		</main> 
	</body>
</html>