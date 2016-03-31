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

	require 'funciones_profesor.php';

	$con = connect()
    or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.')

    $nombre = $_REQUEST['nombre'];
    $contrasenya = $_REQUEST['contrasenya'];


	$result =  pg_query_params($con, 'SELECT pass, id, admin FROM alumnos WHERE nombre =  $1 AND profesor = TRUE', array($nombre))
	or die('Error. Prueba de nuevo más tarde.')

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
