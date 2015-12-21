<?php

	require 'funciones_profesor.php';

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde. Si ves al técnico dile que "'. pg_last_error().'"');

    $nombre = $_REQUEST['nombre'];
    $contrasenya = $_REQUEST['contrasenya'];


	$result =  pg_query_params($con, 'SELECT pass, id, admin FROM alumnos WHERE nombre =  $1 AND profesor = TRUE', array($nombre))
	or die('La consulta fallo: ' . pg_last_error());

	$pass = pg_fetch_result($result, 0, 0);

	if ($pass == NULL) {
		header("Location: ./index.php?error=si");
   		exit;
	}

	if (crypt($contrasenya, $pass) != $pass) {
		header("Location: ./index.php?error=si");
   		exit;
	}

	session_start();
    $_SESSION['nombreUsuario'] = $nombre;
	$_SESSION['idUsuario'] = pg_fetch_result($result, 0, 1);
	$_SESSION['profesor'] = true;
	$_SESSION['admin'] = (pg_fetch_result($result, 0, 2) == 't' ? true : false);


	pg_free_result($result);

	header("Location: ./ayuda.php");
   	exit;

?>
