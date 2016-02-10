<?php

	include 'funciones_profesor.php';

	session_start();

	if (!isset($_SESSION['profesor'])) {
		header("Location: ./index.php");
   		exit;
	}

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');
?>


<html>
	<head>
		<title>e-valUAM 2.0 - Zona del profesor</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
		<script>
			function loadXMLDocAlumnos(num, ma_id) {
				var xmlhttp;
				if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else { // code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange = function() {
					if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
						document.getElementById("seleccionAlumno").innerHTML = xmlhttp.responseText;
					}
				}

				xmlhttp.open("post", "profesorRequest.php", true);
		        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		        xmlhttp.send("tipo=alumnos&id=" + num +"&ma_id=" + ma_id);
			}

			function loadXMLDocDatos(num, name, time, idExamen, idMateria) {
				window.open('profesorRequest.php?tipo=datos&idExamen=' + idExamen + '&time=' + time + '&name=' + name + '&id=' + num + '&ma_id=' + idMateria).focus();
			}
		</script>
	</head>

	<body>
		<header>
			<img id="logo_uam" src="../multimedia/logos/uam.jpg">
			<img id="logo_ope" src="../multimedia/logos/ope.bmp">
		</header>

		<?php mostrar_navegacion_profesor(); ?>

		<section id="instucciones">
			<p>En esta página se puede recuperar el examen que ha hecho cada alumno para cada examen.</p>
			<p>Primero deberas seleccionar un examen y a continuación un alumno.</p>
			<p>En una ventana nueva se abrirá su examen.</p>
		</section>

		<section id="seleccionExamen">

			<form>
				<h2>Examenes disponibles</h2>
				<table>
					<tr>
						<th>Nombre examen</th>
						<th>Nombre materia</th>
						<th>Seleccionar</th>
					</tr>
					<?php

						$result =  pg_query($con, 
							'SELECT ex.nombre AS nombre_ex, ma.nombre AS nombre_ma, ex.id AS id, ma.id AS ma_id FROM examenes AS ex INNER JOIN materias AS ma ON ex.id_materia = ma.id')
						or die('La consulta fallo: ' . pg_last_error());

						
						while ($examen = pg_fetch_array($result, null, PGSQL_ASSOC)) { 
							echo "<tr><td>".$examen['nombre_ex']."</td><td>".$examen['nombre_ma']."</td><td><input type=\"radio\" name=\"idExamen\" value=\"".$examen['id']."\" onclick=\"loadXMLDocAlumnos(this.value, ".$examen['ma_id'].")\"></td></tr>";
						}

					?>
				</table>
			</form>
		</section>

		<section id="seleccionAlumno">
		</section>
	</body>
</html>
