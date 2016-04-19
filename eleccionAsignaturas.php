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

	session_start();

	//Verificacion del login
	if (!isset($_SESSION['idUsuario'])) {
		header("Location: ./index.php?error=si");
        exit;
	}

	//Conexion con la base de datos
	$con = connect();
	if ($con == null) {
		set_mensaje('error','Error al conectar con la base de datos');
		header("Location: ./eleccionExamen.php");
        exit;
	}

	//Buscamos las asignaturas disponibles
	$result =  pg_query_params($con,
							'SELECT nombre,id,descripcion,pass,
							id IN (SELECT id FROM alumno_por_asignaturas WHERE id_alumno = $1) AS apuntado  
							FROM asignaturas 
							WHERE borrada = false
							ORDER BY nombre,id ',
							array($_SESSION['idUsuario']))
							or die('Error. Prueba de nuevo más tarde.');

	//Caso no hay asignaturas
	if (pg_num_rows($result) == 0) 
		$result = null;

?>

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
	   <!-- tablefilter -->
	   <link rel="stylesheet" type="text/css" href="./scripts/TableFilter/filtergrid.css" media="screen"/>
	   <script type="text/javascript" src="./scripts/TableFilter/tablefilter.js"></script>
	   <script type="text/javascript" src="./scripts/TableFilter/actb.js"></script>
	</head>

	<body>
		<?php mostrar_header_link(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>e-valUAM, inscripción a Asignaturas.</h1>

					<p> En esta página puedes inscribirte a diversas asignaturas, es posible que algunas requieran una contraseña</p>
					<p> Para volver a ver los exámenes disponibles pulsa <a href="./eleccionExamen.php">aquí.</a></p>
				</div>
			</div>



			<div class="row">
				<div class="col-md-12">
					<p class="lead">En la tabla siguiente encontrarás un listado de todas las asignaturas disponibles actualmente.</p>


					<table class="table table-hover" id="tablaAsignaturas" border="0"  cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>Asignatura</th>
								<th>Descripción</th>
								<th>Privacidad</th>
								<th>Opciones</th>
							</tr>
						</thead>
						<tbody>
							<?php
								//Caso no hay asignaturas
								if($result == null){
									echo "<tr><td>Aún no hay asignaturas para mostrar.</td><td></td><td></td><td></td></tr>";
								}

								// Imprimiendo los resultados en HTML
								while ($line = pg_fetch_array($result, null)) {

								    echo "\t<tr>\n";
								    echo "\t\t<td>".$line['nombre']."</td>\n";
								    echo "\t\t<td>".$line['descripcion']."</td>\n";

								$icono = ($line['pass'] == null || $line['pass'] =='' ) ? "glyphicon glyphicon-eye-open" : "glyphicon glyphicon-lock";
									echo "<td>  <span class='".$icono."'aria-hidden='true'></span></td>";
									if($line['apuntado']='f')
echo "\t\t<td><button type='button' class='btn btn-primary' data-toggle='modal' data-target='#myModal'>Inscribirse</button></td>";
								    	

//echo "\t\t<td><a class=\"btn btn-primary\" href=\"Examen.php?idExamen=".$line['id']."\">Inscribirse</a></td>";
									else
								    	echo "\t\t<td><a class=\"btn btn-primary\" href=\"Examen.php?idExamen=".$line['id']."\">Cancelar</a></td>";
								    echo "\t</tr>\n";
								}

								// Liberando el conjunto de resultados
								pg_free_result($result);
							?>
						</tbody>
					</table>
					<script language="javascript" type="text/javascript"> 
					//<![CDATA[ 
					var tableFilters = {
							col_2: "none",
							col_3: "none",
							paging: true,
							paging_length: 50
						}

					setFilterGrid("tablaAsignaturas",0,tableFilters); 
					//]]> 
					</script> 

					<p>Si quieres volver a la pagina de exámenes, pulsa <a href="./eleccionExamen.php">aquí.</a></p>
				</div>
			</div>

			<!-- Modal Inscripcion-->
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Inscripción</h4>
				  </div>
				  <div class="modal-body">
					<div class="form-group">
						<label class="control-label" for="passAsignatura">Introduzca la contraseña</label>
						<input class="form-control" id="mensaje" type="password" name="passAsignatura" size="20" placeholder="Contraseña">
					</div>
				  </div>
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-primary">Inscribirse</button>
				  </div>
				</div>
			  </div>
			</div>

		</main>
		<?php mostrar_licencia(); ?>
	</body>
</html>
