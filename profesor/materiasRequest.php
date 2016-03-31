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

	if (!isset($_REQUEST['idMateria'])) {
		die();
	}

	if (!isset($_REQUEST['nombreMateria'])) {
		$result =  pg_query_params($con,
		'SELECT nombre, num_dificultades, num_respuestas, acepta_feedback
		FROM materias
		WHERE id = $1',
		array($_REQUEST['idMateria']))
		or die('Error. Prueba de nuevo más tarde.')

		$data = pg_fetch_array($result, null, PGSQL_ASSOC);

	//Buscamos el numero de preguntas de la materia
	$result = pg_query_params($con,
		'SELECT COUNT(*) AS "num_preguntas"
		FROM preguntas
		WHERE id_materia = $1 AND borrada = FALSE;',
		array($_REQUEST['idMateria']))
		or die('Error. Prueba de nuevo más tarde.')
	$data2 = pg_fetch_array($result, null, PGSQL_ASSOC);



?>
	<form role="form" method="post" id="form_edicion">
		<input type="hidden" id="idMateria" value="<?php echo $_REQUEST['idMateria']; ?>">
		<div class="form-group">
			<label class="control-label" for="nombreMateria">Nombre de la materia: </label>
			<input class="form-control" type="text" id="nombreMateria" name="nombreMateria" size="20" placeholder="Nombre de la materia" value="<?php echo $data['nombre']; ?>">
		</div>

		<div class="form-group">
				<label class="control-label" for="numDificultades">Elige el número de niveles que tendrán los exámenes: </label>
				<input type="number" class="form-control" name="numDificultades" min="1" max="25" value="<?php echo $data['num_dificultades']; ?>">
		</div>


		</div>
		<div class="form-group">
			<label class="control-label" for="numPreguntas">Elige el número de respuestas que tendrá cada pregunta:</label>
			<select class="form-control" id="numPreguntas" name="numPreguntas" <?php if($data2["num_preguntas"] != 0)echo 'disabled' ;?>>

				<option value="0" <?php if ($data["num_respuestas"] == 1) echo "selected"; ?>>Respuesta abierta</option>
				<option value="2" <?php if ($data["num_respuestas"] == 2) echo "selected"; ?>>2</option>
				<option value="3" <?php if ($data["num_respuestas"] == 3) echo "selected"; ?>>3</option>
				<option value="4" <?php if ($data["num_respuestas"] == 4) echo "selected"; ?>>4</option>
				<option value="5" <?php if ($data["num_respuestas"] == 5) echo "selected"; ?>>5</option>
				<?php if($data2["num_preguntas"] != 0)
							echo "<option selected>No puedes modificar este campo si ya existen preguntas</option>";?>
			</select>
		</div>
		<!--
		<div class="checkbox">
		  <label>
		    <input type="checkbox" id="feedback" name="feedback" value="t" <?php if ($data['acepta_feedback'] == 't') echo "checked"; ?>>
		    ¿Las preguntas tienen retroalimentación?
		  </label>
		</div>
		-->
	</form>
<?php
	} else {
		if ($_REQUEST['numDificultades'] >= 1 &&
			$_REQUEST['numPreguntas'] >= 1 && $_REQUEST['numPreguntas'] <= 5) {

			// $feedback = ($_REQUEST['feedback'] == 't' ? 't' : 'f');

			$result = pg_query_params($con,
				'UPDATE materias SET nombre = $1, num_dificultades = $2, num_respuestas = $3 WHERE id = $4;',
				array($_REQUEST['nombreMateria'], $_REQUEST['numDificultades'], $_REQUEST['numPreguntas'], $_REQUEST['idMateria']));

			if ($result) {
				echo "Ok";
			} else {
				echo "Error";
			}
		}
	}
?>
