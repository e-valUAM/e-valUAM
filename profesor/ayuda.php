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

?>

<!DOCTYPE html>

<html>
	<head>
		<title>e-valUAM 2.0 - Ayuda</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<link rel="shortcut icon" href="../favicon.png" type="image/png"/>
		<!-- bootstrap -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
		<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
	</head>

	<body>
		<?php mostrar_header_profesor(); mostrar_navegacion_profesor(basename(__FILE__)); ?>
		<main class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					

					<h2>Introducción</h2>
					<p>En e-valUAM todo se organiza en torno a cuatro conceptos principales: asignaturas, materias, preguntas y exámenes.</p>

					<h4>Asignaturas</h4>
					<p>Es posible crear <strong><a href='gestionAsignaturas.php'>asignaturas</a></strong> para incluir materias en ellas, los alumnos podrán inscribirse a ellas desde el menu de inscripción, en el portal para alumnos. Una vez inscritos podrán realizar los exámenes de las materias incluidas en la asignatura. Es posible hacer privada una asignatura con una contraseña, para tener control de quién puede acceder al contenido.</p>
					<p> A la hora de crear una asignatura se solicitará un nombre, que será público para todo el mundo junto a una descripción breve y opcionalmente una contraseña</p>

					<h4>Materias</h4>
					<p> Las <strong><a href='gestionMaterias.php'>materias</a></strong> permiten agrupar preguntas de un mismo tipo, están pensadas para incluir preguntas de un determinado tema, pues los exámenes se realizan sobre las preguntas de una materia común.</p>
					<p>Para crear una materia es necesario especificar un nombre público y a que asignatura pertenece, si no has creado una anteriormente puedes hacerlo <a href='gestionAsignaturas.php'>aquí</a>. Hay que elegir el número de niveles que tendrán los exámenes de la materia y el tipo de pregunta, las cuales pueden ser tipo test (de 2 a 5 respuestas) o preguntas abiertas</p>

					<h4>Preguntas</h4>
					<p>Una <strong><a href='gestionPreguntas.php'>pregunta</a></strong> es exactamente lo que parece: una cuestión que los alumnos deberán responder. Hay dos tipos principales de preguntas, tipo test y de respuesta abierta. Además pueden contener imágenes o grabaciones de audio.</p>

<p>Las preguntas tipo test pueden contener de 2 a 5 respuestas, de las cuales solo una es correcta. Cuando se muestran en el exámen las respuestas aparecen ordenadas aleatoriamente, es decir, no se muestran siempre cada respuesta en la misma letra.</p>

<p>Las preguntas abiertas no contienen opciones seleccionables, se admite como respuesta un texto a escribir por el alumno, para su correción la respuesta debe coincidir con la respuesta correcta proporcionada al generar la pregunta, salvo espacios y tabulaciones al principio y final, que no son tenidos en cuenta</p>
<p>Además e-valUAM permite crear preguntas paramétricas, las cuales contienen valores que varian cada vez que se genera la pregunta. Puedes encontrar más información en la sección de preguntas frecuentes.</p>

					<p>Las preguntas se dividen en <strong>niveles</strong>. Los niveles menores agrupan las preguntas más básicas mientras que los niveles más altos agrupan las preguntas avanzadas. Un alumno no responderá preguntas de un nivel alto hasta que no haya respondido correctamente suficientes preguntas del nivel anterior. El número de pregutnas que deberá responder dependerá del número de niveles total y del número de preguntas que tenga un examen (por ejemplo, si el examen tiene 30 preguntas y tres niveles, deberá responder correctamente 10 preguntas del primer nivel para empezar a ver pregutnas del segundo nivel, y otras 10 correctas para pasar al tercer nivel).</p>
					
					<h4>Exámenes</h4>
					<p>A la hora de crear un <strong><a href='gestionExamen.php'>examen</a></strong> se debe elegir la materia sobre la que se realizará, el número de preguntas a contestar y el tiempo disponible. Permite decidir si el examen mostrará feedback según se vayan contestando las preguntas y que datos se muestran al finalizar el examen. Una vez creado un examen, si se marca como visible, estará disponible para todos los alumnos inscritos en la asignatura</p>
					

					<h4>Estadisticas</h4>
					<p>e-valUAM pretende facilitar la labor de los docentes al permitir crear un conjunto robusto y extenso de preguntas con el que se puedan ir creando exámenes o pruebas de autoevaluación para los alumnos de una manera rápida y sencilla. Permite ver cada examen, analizar qué preguntas estaban peor planteadas o qué partes del temario no llegaron bien a los alumnos.</p>

					<h4>e-valUAM 3.0</h4>
					<p>e-valUAM es una herramienta actualmente en desarrollo, por lo que cada vez irá añadiendo más características</p>

					<h2>Preguntas frecuentes</h2>
					<ul>
					<li>
							<h3>¿Cómo creo una pregunta paramétrica?</h3>
							<p>Las preguntas paramétricas son un tipo especial de pregunta abierta, pero tienen valores que cambian cuando se genera la pregunta en un examen. Al crear una nueva pregunta, de una materia con respuestas abiertas, aparecera la opción de si la pregunta tiene parámetros. Cuando esta opción es marcada aparecerá un formulario con el que especificar los parámetros</p>

<p> En el texto de la pregunta, ha de ponerse $1,$2,...($ + número de parámetro) en el lugar donde aparecerá el parámetro. A su vez se ha de especificar el rango de valores que tomará cada uno. Por el momento los parámetros generados son números con 3 decimales en el rango especificado</p>
<p>A su vez hay que adjuntar una función <strong>matlab</strong>, que será llamada para generar la respuesta a la pregunta. Esta función debe tener tantos argumentos de entrada como parámetros la pregunta y debe devolver un escalar, que será la respuesta a la pregunta. No deben existir varias funciones en una misma materia con el mismo nombre, pues puede producir confusión sobre que función llamar. La función debe encontrarse en un fichero de mismo nombre acabado en .m .</p>
<p>Se recomienda usar format short para la salida de la función matlab</p>
						</li>
						<li>
							<h3>Quiero que mis preguntas tengan imagenes/audio, pero no sé cómo</h3>
							<p>Incluir archivos multimedia requiere de dos pasos. Da igual el órden en el que se hagan, pero ambos deben realizarse para que la pregunta se muetre correctamente a los alumnos</p>
							<p>Por un lado, se deberán subir los ficheros de audio o imagen al servidor. Lo puedes hacer desde la pestaña de <a href="gestionMultimedia.php">Ficheros multimedia.</a></p>
							<p>Por otro, deberás indicar al crear la pregunta los nombres de los ficheros de imagen/audio que quieres asociar a ella.</p>
							<p>Si al crear la pregunta se te olvidó escribir el nombre o hay un error, puedes editar la pregunta desde la página de <a href="gestionPreguntas.php">Preguntas</a>. Si se te ha olvidado el nombre del fichero, puedes consultarlo en la sección de abajo del todo de <a href="gestionMultimedia.php">Ficheros multimedia.</a></p>
						</li>
						<li>
							<h3>Cuando entro en la sección de alumnos, no logro ver mi examen</h3>
							<p>Para que un examen sea visible a los alumnos no basta con crearlo, sino que hay que marcarlo como visible. Cuando crees el examen en <a href="gestionExamenes.php">Exámenes</a>, revisa que marcas la casilla de <em>¿Está el examen visible?</em></p>
						</li>
						<li>
							<h3>He creado un examen pero hay un error en alguno de sus campos. ¿Cómo puedo corregirlo?</h3>
							<p>Ahora mismo la única manera es borrar el examen que tiene el error y crear uno nuevo con ese campo corregido. Estamos trabajando en ofrecer algo más cómodo.</p>
						</li>
						<li>
							<h3>No encuentro respuesta a mi pregunta en esta página</h3>
							<p>Utiliza el <a href='../contacto.php'>formulario de contacto</a> y te responderemos lo antes posible.</p>
						</li>
					</ul>

					<br>
				</div>
			</div>
		</main>
	</body>
</html>
