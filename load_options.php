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

// Manejar la solicitud AJAX
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Obtener opciones de Región
    if ($action === 'get_regiones') {
        $query = "SELECT * FROM regiones";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
            }
        }
    }

    // Obtener opciones de Comuna según la Región seleccionada
    elseif ($action === 'get_comunas' && isset($_GET['region'])) {
        $region = $_GET['region'];
        $query = "SELECT * FROM comunas WHERE id_region = $region";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
            }
        }
    }

    // Obtener opciones de Candidato
    elseif ($action === 'get_candidatos') {
        $query = "SELECT * FROM candidatos";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['nombre'] . "'>" . $row['nombre'] . "</option>";
            }
        }
    }
}

$conn->close();
?>
