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
                or die('La actualizacion falló: '.pg_last_error());
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

