<?php

	$target_dir = "../multimedia/" . $_REQUEST['idMateria'] . "/";
	$target_file = $target_dir . basename($_FILES["fichero"]["name"]);
	$uploadOk = 1;
	$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

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

