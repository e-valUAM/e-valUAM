<?php

	include 'funciones_profesor.php';

	check_login();

	$con = connect()
	or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

	if (isset($_REQUEST['idPregunta'])) {

		$result =  pg_query_params($con, 
		'SELECT texto, imagen, audio, dificultad, id, id_materia
		FROM preguntas
		WHERE id = $1 AND borrada = FALSE',
		array($_REQUEST['idPregunta']));

		$data = pg_fetch_array($result, null, PGSQL_ASSOC);

		$resultMateria =  pg_query_params($con, 
		'SELECT num_dificultades, num_respuestas
		FROM materias
		WHERE id = $1',
		array($_REQUEST['idMateria']));

		$dataMateria = pg_fetch_array($resultMateria, null, PGSQL_ASSOC);

		$result =  pg_query_params($con, 
		'SELECT texto, imagen, audio
		FROM respuestas
		WHERE id_pregunta = $1 AND correcta = True',
		array($_REQUEST['idPregunta']));

		$respuesta = pg_fetch_array($result, null, PGSQL_ASSOC);
?>

	<form role="form" method="post" id="form_edicion">
		<input type="hidden" id="idMateria" name="idMateria" value="<?php echo $_REQUEST['idMateria']; ?>">
		<input type="hidden" id="idPregunta" name="idPregunta" value="<?php echo $_REQUEST['idPregunta']; ?>">

		<div class="form-group">
			<label class="control-label" for="dificultad">Elige la dificultad: </label>
			<select class="form-control" id="dificultad" name="dificultad">
			<?php 
				echo "!".$dataMateria['num_dificultades'];

				switch (intval($dataMateria['num_dificultades'])) {
					case 4:
						echo "<option value=\"4\"";
						if ($data['dificultad'] == 4) echo " selected";
						echo ">4</option>";
					case 3:
						echo "<option value=\"3\"";
						if ($data['dificultad'] == 3) echo " selected";
						echo ">3</option>";
					case 2:
						echo "<option value=\"2\"";
						if ($data['dificultad'] == 2) echo " selected";
						echo ">2</option>";
					case 1:
						echo "<option value=\"1\"";
						if ($data['dificultad'] == 1) echo " selected";
						echo ">1</option>";
				}
			?>
			</select>
		</div>

		<div class="checkbox">
			<label>
				<input onchange="changeImagenEdit(this)" type="checkbox" id="imagenPrincipalCheckbox" value="t" <?php if ($data['imagen'] != NULL) echo "checked"; ?>>
				¿La pregunta tiene una imagen principal?
			</label>
		</div>

		<div class="checkbox">
			<label>
				<input onchange="changeImagenRespuestaEdit(this)" type="checkbox" id="imagenRespuestasCheckbox" value="t" <?php if ($respuesta['imagen'] != NULL) echo "checked"; ?>>
				¿Las respuestas son imágenes?
			</label>
		</div>

		<div class="checkbox">
		  <label>
			<input onchange="changeAudioEdit(this)" type="checkbox" id="audioCheckbox" value="t" <?php if ($data['audio'] != NULL) echo "checked"; ?>>
			¿La pregunta va acompañada de audio?
		  </label>
		</div>

		<div class="form-group" id="titulo">
			<label class="control-label" for="titulo">Título: </label>
			<input class="form-control" id="tituloText" type="text" name="titulo" placeholder="Título de la pregunta" value="<?php echo $data['texto']; ?>">
		</div>

		<!-- Imagen -->

		<div class="form-group <?php echo ($data['imagen'] != NULL ? "show" : "hidden"); ?>" id="imagenEdit">
			<label class="control-label" for="imagenPrincipal">Imagen: </label>
			<input class="form-control" id="imagenPrincipal" type="text" name="imagenPrincipal" placeholder="Nombre del fichero con la imagen" value="<?php echo $data['imagen']; ?>">
		</div>

		<!-- Audio -->

		<div class="form-group audioEdit <?php echo ($data['audio'] != NULL ?  "show": "hidden"); ?>">
			<label class="control-label" for="audioPrincipal">Audio de la pregunta: </label>
			<input class="form-control" id="audioPrincipal" type="text" name="audioPrincipal" placeholder="Nombre del fichero con el audio" value="<?php echo $data['audio']; ?>">
		</div>

		<div class="form-group" id="zona-respuestas">
			<?php

				function respuestaNum($num, $text, $imagen, $audio) {
					// Texto de la respuesta
					$ret = '<div class="form-group show textoRespuesta">';
					if ($num == 1) {
						$ret .= '<label class="control-label" for="respuesta' . $num . '">Respuesta correcta: </label>';
					} else {
						$ret .= '<label class="control-label" for="respuesta' . $num . '">Respuesta #' . $num . ': </label>';
					}

					$ret .= '<input class="form-control" type="text" id="respuesta' . $num . '" name="respuesta' . $num . '" value="'. $text .'">';
					$ret .= '</div>';

					// Texto con el nombre de la imagen
					$ret .= '<div class="form-group ' . ($imagen != NULL ? "show" : "hidden") . ' imagenRespuestaEdit">';
					if ($num == 1) {
						$ret .= '<label class="control-label" for="respuestaImagen' . $num . '">Imagen respuesta correcta: </label>';
					} else {
						$ret .= '<label class="control-label" for="respuestaImagen' . $num . '">Imagen respuesta #' . $num . ': </label>';
					}
					$ret .= '<input class="form-control" type="text" id="respuestaImagen' . $num . '" name="respuestaImagen' . $num . '" value="' . ($imagen != NULL ? $imagen : "") . '" placeholder="Nombre del fichero con la imagen">';
					$ret .= '</div>';

					// Texto con el nombre del audio
					$ret .= '<div class="form-group ' . ($audio != NULL ? "show" : "hidden") . ' audioEdit">';
					if ($num == 1) {
						$ret .= '<label class="control-label" for="respuestaAudio' . $num . '">Audio respuesta correcta: </label>';
					} else {
						$ret .= '<label class="control-label" for="respuestaAudio' . $num . '">Audio respuesta #' . $num . ': </label>';
					}
					$ret .= '<input class="form-control" type="text" id="respuestaAudio' . $num . '" name="respuestaAudio' . $num . '" value="' . ($audio != NULL ? $audio : "") . '" placeholder="Nombre del fichero con el audio">';
					$ret .= '</div>';

					return $ret;
				}

				echo respuestaNum(1, $respuesta['texto'], $respuesta['imagen'], $respuesta['audio']);

				$result =  pg_query_params($con, 
				'SELECT texto, imagen, audio
				FROM respuestas
				WHERE id_pregunta = $1 AND correcta = False',
				array($_REQUEST['idPregunta']));

				$i = 2;
				while ($respuesta = pg_fetch_array($result, null, PGSQL_ASSOC)) {
					echo respuestaNum($i, $respuesta['texto'], $respuesta['imagen'], $respuesta['audio']);
					$i += 1;
				}

			?>
		</div>
	</form>

<?php } ?>