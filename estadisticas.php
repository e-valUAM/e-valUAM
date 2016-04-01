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

	include 'funciones_profesor.php';

	check_login();


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

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
		</script>
		<script>
			$(document).ready(function() {
			  $(window).keydown(function(event){
			    if(event.keyCode == 13) {
			      event.preventDefault();
			      return false;
			    }
			  });
			});

			function loadXMLDoc() {
				var min = document.getElementById("min").value;
				var num = $("input[name='idExamen']:checked").val();

				if (num == null)
					return;

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

				xmlhttp.open("post", "estadisticasRequest.php", true);
		        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		        xmlhttp.send("id=" + num + "&min=" + min);
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
			<p>En esta página se pueden ver las estadísticas de fallo por pregunta.</p>
			<p>Primero deberas seleccionar una materia y un número mínimo de veces que se debe haberse respondido una pregunta para tenerser en cuenta.</p>
			<p>Al final de la página aparecerá una tabla con toda la información.</p>
		</section>

		<section id="seleccionExamen">

			<form>
				<h2>Materias disponibles</h2>
				<p>Número mínimo de veces que debe haberse respondido una pregunta: <input onchange="loadXMLDoc()" id="min" value="5" type="number" name="min" min="1" size="4"></p>
				<table>
					<tr>
						<th>Nombre materia</th>
						<th>Seleccionar</th>
					</tr>
					<?php

						$result =  pg_query_params($con,
							'SELECT ma.nombre AS nombre_ma, ma.id AS ma_id
							FROM  materias AS ma INNER JOIN profesor_por_materia AS pm ON ma.id = pm.id_materia
							WHERE pm.id_alumno = $1', array($_SESSION['idUsuario']))
						or die('Error. Prueba de nuevo más tarde.');


						while ($examen = pg_fetch_array($result, null, PGSQL_ASSOC)) {
							echo "<tr><td>".$examen['nombre_ma']."</td><td><input type=\"radio\" name=\"idExamen\" value=\"".$examen['ma_id']."\" onclick=\"loadXMLDoc()\"></td></tr>";
						}

					?>
				</table>
			</form>
		</section>

		<section id="seleccionAlumno">
		</section>
	</body>
</html>
