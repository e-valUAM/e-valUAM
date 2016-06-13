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

	//Verificacion del login admin
	check_admin();

	//Conexion con la base de datos
	$con = connect();
	if ($con == null) {
		set_mensaje('error','Error al conectar con la base de datos');
		header("Location: ./ayuda.php");
        exit;
	}

	//Query para otorgar/ revocar permisos
	if(isset($_REQUEST['id'])){

		if($_REQUEST['tipo'] == 'profesor'){

			$result =  pg_query_params($con,
				'UPDATE alumnos SET parametricas = false, profesor = $1 WHERE id = $2;',
				array( $_REQUEST['permisos'], $_REQUEST['id']))
				or die('Error cambiando permisos en la base de datos.'.pg_last_error());

		} else if ($_REQUEST['tipo'] == 'parametricas'){
			$result =  pg_query_params($con,
				'UPDATE alumnos SET parametricas = $1 WHERE id = $2;',
				array( $_REQUEST['permisos'], $_REQUEST['id']))
				or die('Error cambiando permisos en la base de datos.');
		}

		if ($result == null) 
			set_mensaje('error','No se ha producido ningun cambio');
		else
		set_mensaje('ok','Usuario Nº '.$_REQUEST['id'].' ahora'.( $_REQUEST['permisos']=='t' ? '' : ' no').' tiene permisos de '.$_REQUEST['tipo']);

		unset($_REQUEST['id']);
	}

	//Buscamos los usuarios con sus permisos
	$result =  pg_query($con, "SELECT id, nombre, admin, profesor, parametricas FROM alumnos ORDER BY id;");

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
	   <link rel="stylesheet" type="text/css" href="../scripts/TableFilter/filtergrid.css" media="screen"/>
	   <script type="text/javascript" src="../scripts/TableFilter/tablefilter.js"></script>
	   <script type="text/javascript" src="../scripts/TableFilter/actb.js"></script>
	</head>

	<body>
		<?php mostrar_header_profesor(); ?>

		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<h1>e-valUAM, Gestión de permisos</h1>

					<p> Página para administrar los permisos de los usuarios. Solo administradores pueden ver esta página.</p>
					<p> Para volver al portal del profesor pulsa <a href="./ayuda.php">aquí.</a></p>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<p class="lead">En la tabla siguiente encontrarás un listado de todos los usuarios con sus permisos actuales</p>


					<table class="table table-hover" id="tablaAsignaturas" border="0"  cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>ID</th>
								<th>Nombre</th>
								<th>Administrador</th>
								<th>Profesor</th>
								<th>Paramétricas</th>
							</tr>
						</thead>
						<tbody>
							<?php
								//Caso no hay alumnos
								if($result == null){
									echo "<tr><td>No hay alumnos</td><td></td><td></td><td></td><td></td></tr>";
								}

								// Imprimiendo tabla en HTML
								while($line = pg_fetch_array($result, null)) {

								    echo "\t<tr>\n";
									echo "\t\t<td>".$line['id']."</td>\n";
								    echo "\t\t<td>".$line['nombre']."</td>\n";

									//ADMIN
									$icono = ($line['admin'] == 't' ) ? "glyphicon glyphicon-ok" : "glyphicon glyphicon-remove";
									echo "<td>  <span class='".$icono."'aria-hidden='true'></span></td>";

									//PROFESOR
									echo "<td>";
									if($line['profesor'] != 't'){
										echo "<a class='btn btn-primary' href='gestionAdmin.php?id=".$line['id']."&permisos=t&tipo=profesor'>";
										echo "Dar profesor</a>";
									} else {
										echo "<a class='btn btn-danger' href='gestionAdmin.php?id=".$line['id']."&permisos=f&tipo=profesor'>";
										echo "Revocar profesor</a>";
									}
									echo "</td>";

									//PARAMETRICAS
									echo "<td>";
									if($line['parametricas'] != 't'){
										echo "<a class='btn btn-primary' href='gestionAdmin.php?id=".$line['id']."&permisos=t&tipo=parametricas'";
										echo ($line['profesor'] != 't' ? 'disabled' :'').">";
										echo "Dar parametricas</a>";
									} else {
										echo "<a class='btn btn-danger' href='gestionAdmin.php?id=".$line['id']."&permisos=f&tipo=parametricas'>";
										echo "Revocar parametricas</a>";
									}
									echo "</td></tr>";
								}

								// Liberando el conjunto de resultados
								pg_free_result($result);
							?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Scripts -->
			<script language="javascript" type="text/javascript"> 
				var tableFilters = {
					col_2: "none",
					col_3: "none",
					paging: true,
					paging_length: 25
				}
				setFilterGrid("tablaAsignaturas",0,tableFilters); 
			</script> 
		</main>
		<?php mostrar_licencia(); ?>
	</body>
</html>
