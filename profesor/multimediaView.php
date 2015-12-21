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



