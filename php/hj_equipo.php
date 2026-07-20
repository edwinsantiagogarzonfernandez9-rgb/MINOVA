<?php
session_start();
include "../config/conexion.php";

$detalleEquipo = null;
$idEquipo = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($idEquipo !== '') {
    $stmt = $conn->prepare("SELECT equipo.*, empresa.nombre AS marca_nombre, ubicacion.nombre AS ubicacion_nombre FROM equipo LEFT JOIN empresa ON equipo.empresa_nit = empresa.nit LEFT JOIN ubicacion ON equipo.ubicacion_id = ubicacion.id WHERE equipo.id = ? OR equipo.codigo = ?");
    $stmt->bind_param("ss", $idEquipo, $idEquipo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $detalleEquipo = $resultado->fetch_assoc();
    $stmt->close();
}

if (!$detalleEquipo) {
    $detalleEquipo = [
        'nombre' => 'Sin información',
        'codigo' => '',
        'descripcion' => '',
        'numero_serie' => '',
        'responsable' => '',
        'estado' => '',
        'fecha_registro' => '',
        'modelo' => '',
        'marca_nombre' => '',
        'ubicacion_nombre' => '',
        'imagen' => ''
    ];
}

$imagenEquipo = '../img/electrobomba.jpg';
if (!empty($detalleEquipo['imagen'])) {
    $valorImagen = $detalleEquipo['imagen'];

    if (is_string($valorImagen) && preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i', $valorImagen)) {
        $nombreImagen = basename($valorImagen);
        $rutaImagen = __DIR__ . '/../img/' . $nombreImagen;
        if (file_exists($rutaImagen)) {
            $imagenEquipo = '../img/' . $nombreImagen;
        } 
    } elseif (is_string($valorImagen) && $valorImagen !== '') {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($valorImagen) ?: 'image/jpeg';
        $imagenEquipo = 'data:' . $mime . ';base64,' . base64_encode($valorImagen);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de vida <?php echo htmlspecialchars($detalleEquipo['nombre']); ?></title>
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/componentes.css">
    <link rel="stylesheet" href="../styles/estilos_index.css">
    <link rel="icon" type="image/png" href="../img/logo-minova.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../scripts/base.js" defer></script>
</head>
<body> 
    <div id="base-container"></div>
    <template id="page-content">
        <div class="grid-contenedor-4">
            <div class="welcome">
                <div>
                    <h1><span id="equipo"><?php echo htmlspecialchars($detalleEquipo['nombre']); ?></span></h1>
                    <p>Hoja de vida del equipo</p>
                </div>
                <div class="date">
                <i class="fa-regular fa-calendar"></i>
                <span id="fechaActual"></span>
                </div>
            </div>
            <section class="cards">
                <div class="card">
                    <div class="cont-img">
                        <img src="<?php echo htmlspecialchars($imagenEquipo); ?>" alt="Imagen del equipo">
                    </div>
                </div>
            
                <div class="card" style="list-style: none;font-size: 1.25rem;"> 
                    <li><strong>NOMBRE:</strong> <span id="eb_nombre"><?php echo htmlspecialchars($detalleEquipo['nombre']); ?></span></li>
                    <li><strong>CÓDIGO:</strong> <span id="eb_codigoPrograma"><?php echo htmlspecialchars($detalleEquipo['codigo']); ?></span></li>
                    <li><strong>MARCA:</strong> <span id="eb_marca"><?php echo htmlspecialchars($detalleEquipo['marca_nombre']); ?></span></li>
                    <li><strong>MODELO:</strong> <span id="eb_modelo"><?php echo htmlspecialchars($detalleEquipo['modelo']); ?></span></li>
                    <li><strong>DESCRIPCIÓN:</strong> <span id="eb_descripcion"><?php echo htmlspecialchars($detalleEquipo['descripcion']); ?></span></li>
                    <li><strong>NÚMERO DE SERIE:</strong> <span id="eb_serie"><?php echo htmlspecialchars($detalleEquipo['numero_serie']); ?></span></li>
                    <li><strong>RESPONSABLE:</strong> <span id="eb_responsable"><?php echo htmlspecialchars($detalleEquipo['responsable']); ?></span></li>
                    <li><strong>UBICACIÓN:</strong> <span id="eb_ubicacion"><?php echo htmlspecialchars($detalleEquipo['ubicacion_nombre']); ?></span></li>
                    <li><strong>ESTADO:</strong> <span id="eb_estado"><?php echo htmlspecialchars($detalleEquipo['estado']); ?></span></li>
                    <li><strong>FECHA DE REGISTRO:</strong> <span id="eb_fecha"><?php echo htmlspecialchars($detalleEquipo['fecha_registro']); ?></span></li>
                </main>
            </section>
                <div class="btn-container" >
                    <button class="btn-azul"><i class="fas fa-wind"></i> Medición gases</button>
                    <button class="btn-azul"><i class="fas fa-calendar-alt"></i> Calibración</button>
                    <button class="btn-azul"><i class="fas fa-stethoscope"></i> Diagnóstico</button>
                </div>

    </div>

</body>
</html>