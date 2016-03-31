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

        if (!(isset($_SESSION['profesor']) &&  isset($_SESSION['admin']))) {
                header("Location: ./index.php?error=si");
                exit;
        }

        $con = connect()
        or die('No se ha podido conectar con la base de datos. Prueba de nuevo más tarde.');

?>


<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>
<body>

<!-- http://www.random.org/strings/?num=8&len=16&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new -->
<!-- http://www.php.net/manual/es/function.crypt.php -->

<?php

function devurandom_rand() {
    $diff = $max - $min;
    if ($diff < 0 || $diff > 0x7FFFFFFF) {
	throw new RuntimeException("Bad range");
    }
    $fp = fopen('/dev/urandom','rb');
    $bytes = '';
    if ($fp !== FALSE) {
        $bytes .= fread($fp, 4);
        fclose($fp);
    }

    if ($bytes === false || strlen($bytes) != 4) {
        throw new RuntimeException("Unable to get 4 bytes");
    }

    return $bytes;

}
//$password = array("Molins" => "Ejemplo","Nombre" => "pass");

$es_para_colegios = NULL;

if ($es_para_colegios === TRUE) {

	/* Para colegios */
	$start = 1;
	$end = 0;
	$colegio = "";

	$password = array();

	for ($i = 1; $i <= $end; $i++) {
		$value = sprintf("%s%03d", $colegio, $i);
		$password[$value] = $value;
		//echo "<p>$value</p>";
	}

} else if ($es_para_colegios === FALSE) {

	/* Para individuos */

	$password = array();

} else {
	echo "<p>No se hace nada. Configura el fichero.</p>";
	exit;
}

pg_query("BEGIN;");

foreach ($password as $nombre => $pass) {
	$salt = md5(devurandom_rand());
	$hashed_password = crypt($pass, $salt);


	if (crypt($pass, $hashed_password) == $hashed_password) {
   		pg_query_params($con,
                        'INSERT INTO alumnos (nombre, pass, cambio_contrasenya) VALUES ($1, $2, FALSE);',
                        array($nombre, $hashed_password))
                or die('Error. Prueba de nuevo más tarde.')
	} else {
		echo "<p>".$nombre." no insertado. Se aborta.</p>";
		pg_query("ROLLBACK;");
	}

}

pg_query("COMMIT");

/* Se deben pasar todos los resultados de crypt() como el salt para la comparación de una
   contraseña; para evitar problemas cuando diferentes algoritmos hash son utilizados. (Como
   se dice arriba; el hash estándar basado en DES utiliza un salt de 2
   caracteres: pero el hash basado en MD5 utiliza 12.) */
?>

<p>Hecho</p>

</body>
</html>
