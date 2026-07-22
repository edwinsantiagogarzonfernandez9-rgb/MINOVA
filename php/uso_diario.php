<?php
session_start();
include "../config/conexion.php";
$nombreMaquina = '';
$idMaquina = isset($_GET['id']) ? trim($_GET['id']) : '';
$nombreMaquinaParam = isset($_GET['nombre_maquina']) ? trim($_GET['nombre_maquina']) : '';

if ($nombreMaquinaParam !== '') {
    $nombreMaquina = $nombreMaquinaParam;
} elseif ($idMaquina !== '') {
    $stmt = $conn->prepare("SELECT nombre FROM maquina WHERE id=? OR codigo=?");
    $stmt->bind_param("ss", $idMaquina, $idMaquina);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $nombreMaquina = $fila['nombre'];
    }
    $stmt->close();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $estadoFuncionamiento = trim($_POST["estadoFuncionamiento"]);
    $inicioOperacion = trim($_POST["inicioOperacion"]);
    $finOperacion = trim($_POST["finOperacion"]);
    $responsable = trim($_POST["responsable"]);
    $observaciones = trim($_POST["observaciones"]);

    $stmt = $conn->prepare("INSERT INTO uso_diario ( fecha,  inicio_operacion, fin_operacion, observaciones,  estado_funcionamiento,responsable,maquina_id) VALUES ( NOW(), ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi",  $inicioOperacion, $finOperacion,  $observaciones, $estadoFuncionamiento,$responsable,$idMaquina);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Registro guardado correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($idMaquina) . "&nombre_maquina=" . urlencode($nombreMaquina));
    exit;
}
$datosUsoDiario = $conn->query("SELECT * FROM uso_diario WHERE maquina_id = '" . $conn->real_escape_string($idMaquina) . "' ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - USO DIARIO</title>

    <link rel="stylesheet" href="../styles/base.css ">
    <link rel="stylesheet" href="../styles/estilos_formulario.css">
    <link rel="stylesheet" href="../styles/estilos_index.css">
    <link rel="stylesheet" href="../styles/componentes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="../img/logo-minova.png">


</head>
<body>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <script>
            alert(<?php echo json_encode($_SESSION['mensaje']); ?>);
        </script>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
    <div id="base-container"></div>

    <template id="page-content">
        <div class="grid-contenedor-5">

            <div class="welcome">
                <div>
                    <h1>USO DIARIO</h1>
                    <p>Este es el formato que se debe llenar diariamente.</p>
                </div>
                <div class="date">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="fechaActual"></span>
                </div>
            </div>
            <div class="cards">
                <div class="search-main">
                    <div class="search-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            id="q"
                            placeholder="Buscar por nombre de maquina, fecha, responsable, observaciones"
                            oninput="doSearch()"
                        />
                    </div>
                    <button class="btn-buscar" onclick="doSearch()">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </div>
            </div>
            <div class="cards2">
                <table>
                    <tr>
                        <th colSpan="6">REGISTRO DIARIO DE USO DE MAQUINARIA Y EQUIPOS MINA DIDACTICA</th>
                    </tr>
                    <tr>
                        <th colSpan="2">NOMBRE MAQUINA:</th>
                        <td colSpan="4" data-label="Nombre máquina"><?php echo htmlspecialchars($nombreMaquina); ?></td>
                    </tr>
                    <tr >
                        <th>Fecha</th>
                        <th>Verificacion de estado de <br> funcionamiento de la máquina</th>
                        <th>Inicio de operacion</th>
                        <th>Fin de operacion</th>
                        <th>Responsable a cargo</th>
                        <th>Observaciones</th>
                    </tr>
                    <?php while ($uso = $datosUsoDiario->fetch_assoc()): ?>
                    <tr>
                        
                            <td data-label="Fecha"><?php echo htmlspecialchars($uso['fecha']); ?></td>
                            <td data-label="Estado de funcionamiento"><?php echo htmlspecialchars($uso['estado_funcionamiento']); ?></td>
                            <td data-label="Inicio de operación"><?php echo htmlspecialchars($uso['inicio_operacion']); ?></td>
                            <td data-label="Fin de operación"><?php echo htmlspecialchars($uso['fin_operacion']); ?></td>
                            <td data-label="Responsable"><?php echo htmlspecialchars($uso['responsable']); ?></td>
                            <td data-label="Observaciones"><?php echo htmlspecialchars($uso['observaciones']); ?></td>
                       
                    </tr> 
                    <?php endwhile; ?>
                </table>
            </div>

            <div class="btn-container">
                <button class="btn-azul" onclick="window.location.href='hj_maquina.php?id=<?php echo urlencode((string)$idMaquina); ?>&nombre_maquina=<?php echo urlencode((string)$nombreMaquina); ?>'">
                    <i class="fa-solid fa-arrow-left"></i>
                    Regresar
                </button>
                <button id="abrirModal" class="btn btn-azul">
                    <i class="fa-solid fa-plus"></i>
                    Nuevo Registro
                </button>
                <button id="btnExcel" class="btn btn-azul">
                    <i class="fa-solid fa-file-excel"></i>
                    Exportar Excel
                </button>
                <button id="btnPDF" class="btn btn-azul">
                    <i class="fa-solid fa-file-pdf"></i>
                    Exportar PDF
                </button>
            </div>

        </div>

        <div class="overlay-backdrop" id="backdropModal"></div>
        <div class="overlay-panel modal-tool" id="overlay">
            <div class="modal-header">
                <h2>Agregar nuevo registro</h2>
                <button class="cerrar-modal" id="cerrarModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form class="form" method="post">
                <div class="input-group">
                    <label for="estadoFuncionamiento">Verificación del estado de funcionamiento:</label>
                    <input id="estadoFuncionamiento" name="estadoFuncionamiento" rows="4" placeholder="Verificación del estado de funcionamiento:" required>
                </div>
                <div class="inputs-row">
                    <div class="input-group">
                        <label for="inicioOperacion">Inicio de operación:</label>
                        <input type="time" id="inicioOperacion" name="inicioOperacion" placeholder="Inicio de operación:" required>
                    </div>
                    <div class="input-group">
                        <label for="finOperacion">Fin de operación:</label>
                        <input type="time" id="finOperacion" name="finOperacion" placeholder="Fin de operación:" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="responsable">Responsable a cargo:</label>
                    <input type="text" id="responsable" name="responsable" placeholder="Responsable a cargo:" required>
                </div>
                <div class="input-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Observaciones:"></textarea>
                </div>
                <button type="submit" class="btn-azul">
                    <i class="fa-solid fa-save"></i> Guardar
                </button>
            </form>
        </div>

    </template>
    <script src="../scripts/base.js"></script>
</body>
</html>