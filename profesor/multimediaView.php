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

	//echo $_REQUEST['idMateria'];

	$dir = scandir("../multimedia/".$_REQUEST['idMateria']."/");

	//print_r($dir);

	$i = 0;
	echo '<div class="row">';
	foreach ($dir as $f) {

		if ($f == "." || $f == "..")
			continue;


		if (strpos($f,'.mp3') !== false) { // fichero de audio
		?>
			<div class="col-md-4">
				<h2><?php echo $f; ?></h2>
				<audio controls preload="auto">
					<source src="../multimedia/<?php echo $_REQUEST['idMateria']."/".$f; ?>" type="audio/mpeg"></source>
					Tu navegador no soporta audio. Por favor, actualiza <a href=\"http://browsehappy.com/\">a un navegador más moderno.</a>
				</audio>
			</div>

		<?php
		} else { // fichero de imagen
		?>
			<div class="col-md-4">
				<h2><?php echo $f; ?></h2>
				<img class="img-responsive" src="../multimedia/<?php echo $_REQUEST['idMateria']."/".$f; ?>">
			</div>
		<?php
		}

		$i += 1;

		if ($i % 3 == 0) {
		?>
			</div><div class="row">
		<?php

		}
	}
?>

</div>
