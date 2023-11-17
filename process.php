<?php
// Conectar a la base de datos (ajusta según tus credenciales)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "votacion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesar datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar Nombre y Apellido
    $nombreApellido = $_POST["nombreApellido"];
    if (empty($nombreApellido)) {
        die("Error: Nombre y Apellido no pueden estar en blanco.");
    }

    // Validar Alias
    $alias = $_POST["alias"];
    if (strlen($alias) <= 5 || !preg_match('/^[a-zA-Z0-9]+$/', $alias)) {
        die("Error: Alias debe tener más de 5 caracteres y contener solo letras y números.");
    }

    // Validar RUT
    $rut = $_POST["rut"];

    // Validar Email
    $email = $_POST["email"];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Email no válido.");
    }

    // Validar Región y Comuna
    $region = $_POST["region"];
    $comuna = $_POST["comuna"];
    if (empty($region) || empty($comuna)) {
        die("Error: Región y Comuna no pueden estar en blanco.");
    }

    // Validar Candidato en la Base de Datos
    $candidato = $_POST["candidato"];
    $checkCandidatoQuery = "SELECT * FROM candidatos WHERE nombre = '$candidato'";
    $candidatoResult = $conn->query($checkCandidatoQuery);
    if ($candidatoResult->num_rows == 0) {
        die("Error: El candidato seleccionado no existe.");
    }

    // Validar Checkbox "Como se enteró de nosotros"
    $comoSeEntero = isset($_POST["comoSeEntero"]) ? implode(",", $_POST["comoSeEntero"]) : "";
    if (empty($comoSeEntero) || count(explode(",", $comoSeEntero)) < 2) {
        die("Error: Debe seleccionar al menos dos opciones en 'Cómo se enteró de nosotros'.");
    }

    // Validar duplicación de votos por RUT
    $checkDuplicateQuery = "SELECT * FROM votos WHERE rut = '$rut'";
    $duplicateResult = $conn->query($checkDuplicateQuery);
    if ($duplicateResult->num_rows > 0) {
        die("Error: Ya has votado.");
    }

    // Insertar datos en la base de datos
    $insertQuery = "INSERT INTO votos (nombreApellido, rut, email, alias, region, comuna, candidato, comoSeEntero) VALUES ('$nombreApellido', '$rut', '$email', '$alias', '$region', '$comuna', '$candidato', '$comoSeEntero')";
    if ($conn->query($insertQuery) === TRUE) {
        echo "Voto registrado correctamente.";
    } else {
        echo "Error al registrar el voto: " . $conn->error;
    }
}

$conn->close();

// Función para validar RUT
function validarRut($rut) {
    // Verifica que no esté vacio y que el string sea de tamaño mayor a 3 carácteres(1-9)        
    if ((empty($rut)) || strlen($rut) < 3) {
        return array('error' => true, 'msj' => 'RUT vacío o con menos de 3 caracteres.');
    }

    // Quitar los últimos 2 valores (el guión y el dígito verificador) y luego verificar que sólo sea
    // numérico
    $parteNumerica = str_replace(substr($rut, -2, 2), '', $rut);

    if (!preg_match("/^[0-9]*$/", $parteNumerica)) {
        return array('error' => true, 'msj' => 'La parte numérica del RUT sólo debe contener números.');
    }

    $guionYVerificador = substr($rut, -2, 2);
    // Verifica que el guion y dígito verificador tengan un largo de 2.
    if (strlen($guionYVerificador) != 2) {
        return array('error' => true, 'msj' => 'Error en el largo del dígito verificador.');
    }

    // obliga a que el dígito verificador tenga la forma -[0-9] o -[kK]
    if (!preg_match('/(^[-]{1}+[0-9kK]).{0}$/', $guionYVerificador)) {
        return array('error' => true, 'msj' => 'El dígito verificador no cuenta con el patrón requerido');
    }

    // Valida que sólo sean números, excepto el último dígito que pueda ser k
    if (!preg_match("/^[0-9.]+[-]?+[0-9kK]{1}/", $rut)) {
        return array('error' => true, 'msj' => 'Error al digitar el RUT');
    }

    $rutV = preg_replace('/[\.\-]/i', '', $rut);
    $dv = substr($rutV, -1);
    $numero = substr($rutV, 0, strlen($rutV) - 1);
    $i = 2;
    $suma = 0;
    foreach (array_reverse(str_split($numero)) as $v) {
        if ($i == 8) {
            $i = 2;
        }
        $suma += $v * $i;
        ++$i;
    }
    $dvr = 11 - ($suma % 11);
    if ($dvr == 11) {
        $dvr = 0;
    }
    if ($dvr == 10) {
        $dvr = 'K';
    }
    if ($dvr == strtoupper($dv)) {
        return array('error' => false, 'msj' => 'RUT ingresado correctamente.');
    } else {
        return array('error' => true, 'msj' => 'El RUT ingresado no es válido.');
    }
}
?>
