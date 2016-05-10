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

	// Bienvenido al infierno de los if's
	
	//Recogemos formulario inscripcion
	if(isset($_REQUEST['inscripcion'])){

		//Caso borrado
		if($_REQUEST['inscripcion'] == 'f'){

			$result =  pg_query_params($con,
							"UPDATE alumno_por_asignaturas SET activo = false WHERE id_alumno = $1 AND id_asignatura = $2;",
							array($_SESSION['idUsuario'],$_REQUEST['id']))
							or die('Error. Prueba de nuevo más tarde.');
			set_mensaje('ok','Ya no podrá ver el contenido de la asignatura.');


		} else {

			//Query para saber si estaba inscrito anteriormente
			$result2 =  pg_query_params($con,
							"SELECT id_asignatura FROM alumno_por_asignaturas WHERE id_alumno = $1 AND id_asignatura = $2",
							array($_SESSION['idUsuario'],$_REQUEST['id']))
							or die('Error. Prueba de nuevo más tarde.');

			//Sacamos informacion de la asignatura
			$result =  pg_query_params($con,
							"SELECT id,pass 
							 FROM asignaturas 
							 WHERE id = $1 AND borrada = false;",
							array($_REQUEST['id']))
							or die('Error. Prueba de nuevo más tarde.');


			$line = pg_fetch_array($result, null);

			//Caso sin contraseña
			if($line['pass'] == null || $line['pass'] ==''){
				
				//Caso nueva inscripcion
				if (pg_num_rows($result2) == 0){
					$result = pg_query_params($con,"INSERT INTO alumno_por_asignaturas VALUES ($1,$2)",
					array($_SESSION['idUsuario'],$_REQUEST['id']));
				//Caso reinscripcion
				} else {
					$result = pg_query_params($con,"UPDATE alumno_por_asignaturas SET activo = true WHERE id_alumno = $1 and id_asignatura = $2",
					array($_SESSION['idUsuario'],$_REQUEST['id']));
				}
				//Retroalimentacion
				if(!result)
					set_mensaje('error',"Error al inscribirse a la asignatura");
				else
					set_mensaje('ok',"Inscrito correctamente en la asignatura, a partir de ahora podrá ver su contenido");

			//Caso contraseña			
			} else {
				if (crypt( $_REQUEST['password'],$line['pass']) == $line['pass']) {
				
					//Caso nueva inscripcion
					if (pg_num_rows($result2) == 0){
						$result = pg_query_params($con,"INSERT INTO alumno_por_asignaturas VALUES ($1,$2)",
						array($_SESSION['idUsuario'],$_REQUEST['id']));
					//Caso reinscripcion
					} else {
						$result = pg_query_params($con,"UPDATE alumno_por_asignaturas SET activo = true WHERE id_alumno = $1 and id_asignatura = $2",
						array($_SESSION['idUsuario'],$_REQUEST['id']));
					}
					//Retroalimentacion
					if(!result)
						set_mensaje('error',"Error al inscribirse a la asignatura");
					else
						set_mensaje('ok',"Inscrito correctamente en la asignatura, a partir de ahora podrá ver su contenido");			

				} else {
					set_mensaje('error',"Contraseña incorrecta.");
				}
			}
		}
	}

	//Buscamos las asignaturas disponibles
	$result =  pg_query_params($con,
							"SELECT nombre,id,descripcion,pass is not null AS pass,
							id IN (SELECT id_asignatura FROM alumno_por_asignaturas WHERE id_alumno = $1 AND activo = true) AS apuntado  
							FROM asignaturas 
							WHERE borrada = false
							ORDER BY nombre,id",
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

		<script>
$(document).ready(function(){
			$('#myModal').on('show.bs.modal', function (event) {
			  var button = $(event.relatedTarget)
			  var recipient = button.data('id')
			  $('#id').val(recipient);
			  $('#password').val('');
			});
});

		</script>



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

								// Imprimiendo tabla en HTML
								while($line = pg_fetch_array($result, null)) {

									//Campos comunes a todos
								    echo "\t<tr>\n";
								    echo "\t\t<td>".$line['nombre']."</td>\n";
								    echo "\t\t<td>".$line['descripcion']."</td>\n";

									$icono = ($line['pass'] != 't' ) ? "glyphicon glyphicon-eye-open" : "glyphicon glyphicon-lock";
									echo "<td>  <span class='".$icono."'aria-hidden='true'></span></td>";

									//Imprimimos boton con link
									echo "<td>";
									if($line['apuntado'] == 't'){ //Caso borrado

										echo "<a class='btn btn-danger' href='eleccionAsignaturas.php?id=".$line['id']."&inscripcion=f'>Cancelar</a>";

									} else { //Caso apuntarse

										if($line['pass'] != 't'){ //Caso sin contraseña
											echo "<a class='btn btn-primary' href='eleccionAsignaturas.php?id=".$line['id']."&inscripcion=t'>";
											echo "Inscribirse</a>";

										} else { //Caso con contraseña
echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#myModal' data-id=".$line['id'].">";
											echo "Inscribirse</button>";

										}
									}

									echo "</td></tr>";
								}

								// Liberando el conjunto de resultados
								pg_free_result($result);
							?>
						</tbody>
					</table>
					<p>Si quieres volver a la pagina de exámenes, pulsa <a href="./eleccionExamen.php">aquí.</a></p>
				</div>
			</div>

			<!-- Scripts -->
			<script language="javascript" type="text/javascript"> 
				var tableFilters = {
					col_2: "none",
					col_3: "none",
					paging: true,
					paging_length: 50
				}
				setFilterGrid("tablaAsignaturas",0,tableFilters); 
			</script> 




			<!-- Modal Inscripcion-->
			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			  <div class="modal-dialog" role="document">
				<div class="modal-content">



					<!-- HEADER -->
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Inscripción</h4>
				  </div>

					<!-- BODY -->

				   <div class="modal-body">
                
                <form action="eleccionAsignaturas.php" method="post">
                  <div class="form-group">
                      <input type="hidden" class="form-control" id="inscripcion"  name = "inscripcion" value="t" />
                  </div>

				  <div class="form-group">
                      <input type="hidden" class="form-control" id="id" name = "id" />
                  </div>

                  <div class="form-group">
                    <label for="password">Contraseña</label>
                      <input type="password" class="form-control"
                          id="password" name = "password" placeholder="Contraseña"/>
                  </div>
                  <button type="submit" class="btn btn-default">Inscribirse</button>
                </form>
                
                
            </div>

					<!-- FOOTER -->
				  <div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
				  </div>
				</div>
			  </div>
			</div>

		</main>




		<?php mostrar_licencia(); ?>
	</body>
</html>
