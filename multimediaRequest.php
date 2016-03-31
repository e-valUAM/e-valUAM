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

	$target_dir = "../multimedia/" . $_REQUEST['idMateria'] . "/";
	$target_file = $target_dir . basename($_FILES["fichero"]["name"]);
	$uploadOk = 1;
	$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

	// Allow certain file formats
	if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
	&& $imageFileType != "gif" && $imageFileType != "mp3") {
		$uploadOk = 0;
	}

	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		header("Location: ./gestionMultimedia.php?error");
		exit;
	// if everything is ok, try to upload file
	} else {
		if (!is_dir($target_dir)) {
			mkdir($target_dir);
		}

		if (move_uploaded_file($_FILES["fichero"]["tmp_name"], $target_file)) {
			header("Location: ./gestionMultimedia.php?exito");
			exit;
		} else {
			header("Location: ./gestionMultimedia.php?error");
			exit;
		}
	}

?>
