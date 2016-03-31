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

        $con = connect()
        or die("1");

        // Subida de los ficheros
        $dir_subida = './multimedia/alumnos/'.$_SESSION['idUsuario'].'/';
        $ficheros = array();

	$fichero1 = NULL;
	$fichero2 = NULL;
	$fichero3 = NULL;

        // Guardamos sobre qué ficheros trabajaremos
        if ($_FILES['fichero1']['size'] != 0 && is_uploaded_file($_FILES['fichero1']['tmp_name'])) {
                $ficheros[] = 'fichero1';
		$fichero1 = $_FILES['fichero1']['name'];
        }

        if ($_FILES['fichero2']['size'] != 0 && is_uploaded_file($_FILES['fichero2']['tmp_name'])) {
                $ficheros[] = 'fichero2';
		$fichero2 = $_FILES['fichero2']['name'];
        }

        if ($_FILES['fichero3']['size'] != 0 && is_uploaded_file($_FILES['fichero3']['tmp_name'])) {
                $ficheros[] = 'fichero3';
		$fichero3 = $_FILES['fichero3']['name'];
        }

	pg_query("BEGIN") or die("2");

	$res1 = pg_query_params($con, "INSERT INTO preguntas_alumnos (id_alumno, dificultad, texto, respuestaok, respuesta1, respuesta2, respuesta3, imagen)
		VALUES ($1, 1, $2, $3, $4, $5, $6, $7);",
		array($_SESSION['idUsuario'], $_POST['enunciado1'], $_POST['respuestaOk1'], $_POST['respuestaMal1_1'], $_POST['respuestaMal1_2'], $_POST['respuestaMal1_3'], $fichero1));

	$res2 = pg_query_params($con, "INSERT INTO preguntas_alumnos (id_alumno, dificultad, texto, respuestaok, respuesta1, respuesta2, respuesta3, imagen)
                VALUES ($1, 2, $2, $3, $4, $5, $6, $7);",
                array($_SESSION['idUsuario'], $_POST['enunciado2'], $_POST['respuestaOk2'], $_POST['respuestaMal2_1'], $_POST['respuestaMal2_2'], $_POST['respuestaMal2_3'], $fichero2));

        $res3 = pg_query_params($con, "INSERT INTO preguntas_alumnos (id_alumno, dificultad, texto, respuestaok, respuesta1, respuesta2, respuesta3, imagen)
                VALUES ($1, 3, $2, $3, $4, $5, $6, $7);",
                array($_SESSION['idUsuario'], $_POST['enunciado3'], $_POST['respuestaOk3'], $_POST['respuestaMal3_1'], $_POST['respuestaMal3_2'], $_POST['respuestaMal3_3'], $fichero3));

	$res4 = pg_query_params($con, "UPDATE alumnos SET envio_preguntas = 't' WHERE id = $1;", array($_SESSION['idUsuario']));


	// Comprobamos si existe el directorio. Si no existe, lo creamos
	if (!is_dir($dir_subida)) {
		mkdir($dir_subida);
	}

	$res5 = TRUE;

	// Los movemos
	foreach ($ficheros as $fichero) {
		if (!move_uploaded_file($_FILES[$fichero]['tmp_name'], $dir_subida . basename($_FILES[$fichero]["name"]))) {
			$res5 = FALSE;
		}
	}

	// Vemos si todo ha salido bien o no.
        if ($res1 and $res2 and $res3 and $res4 and $res5) {
                pg_query("COMMIT") or die("3");
                $_SESSION['envio_preguntas'] = TRUE;
		echo "0";
        } else {
                pg_query("ROLLBACK") or die("4");
                die("5");
        }

?>
