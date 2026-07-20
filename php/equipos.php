<?php
session_start();
include "../config/conexion.php";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo = trim($_POST["codigo"] ?? "");
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $marca = trim($_POST["marca"] ?? "");
    $serie = trim($_POST["serie"] ?? "");
    $ubicacion = trim($_POST["ubicacion"] ?? "");
    $responsable = trim($_POST["responsable"] ?? "");
    $estado = trim($_POST["estado"] ?? "");
    $fecha_ingreso = trim($_POST["fecha_ingreso"] ?? "");
    $modelo = trim($_POST["modelo"] ?? "");
    $imagen = '';

    if (!empty($_FILES['imagen']['name']) && is_uploaded_file($_FILES['imagen']['tmp_name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['imagen']['tmp_name']);
        $ext_permitidas = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];

        if (in_array($mime, $ext_permitidas, true)) {
            $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
        } else {
            $_SESSION['mensaje'] = 'Formato de imagen no válido.';
        }
    }

    $valid_estados = ['Activo', 'Inactivo', 'Mantenimiento', 'Fuera de servicio'];
    if (!in_array($estado, $valid_estados, true)) {
        $estado = 'Activo';
    }

    $stmt = $conn->prepare("INSERT INTO equipo (codigo, nombre, descripcion, numero_serie, responsable, estado, fecha_registro, empresa_nit, ubicacion_id, imagen, modelo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssiiss", $codigo, $nombre, $descripcion, $serie, $responsable, $estado, $fecha_ingreso, $marca, $ubicacion, $imagen, $modelo);

    if ($imagen !== '') {
        $stmt->send_long_data(9, $imagen);
    }

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Equipo guardado correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$ubicaciones= $conn->query("SELECT * FROM ubicacion");
$empresas= $conn->query("SELECT * FROM empresa");
$equipos= $conn->query("SELECT equipo.*, empresa.nombre AS marca_nombre, ubicacion.nombre AS ubicacion_nombre FROM equipo LEFT JOIN empresa ON equipo.empresa_nit = empresa.nit LEFT JOIN ubicacion ON equipo.ubicacion_id = ubicacion.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - EQUIPOS</title>
    <link rel="stylesheet" href="../styles/base.css ">
    <link rel="stylesheet" href="../styles/estilos_tabla.css">
    <link rel="stylesheet" href="../styles/componentes.css">
    <link rel="stylesheet" href="../styles/estilos_index.css">
    <link rel="icon" type="image/png" href="../img/logo-minova.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                    <h1>EQUIPOS</h1>
                    <p>Busqueda y gestion para los equipos.</p>
                </div>
                <div class="date">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="fechaActual"></span>
                </div>
            </div>
            <section class="cards">
                <div class="search-main">
                    <div class="search-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input
                            type="text"
                            id="q"
                            placeholder="Buscar por nombre, código, número de serie, marca, ubicación…"
                            oninput="doSearch()"
                        />
                    </div>

                    <button class="btn-buscar" onclick="doSearch()">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </div>
            </section>
            <section class="cards2">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>N° Serie</th>
                            <th>Responsable</th>
                            <th>Marca</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($equipo = $equipos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $equipo['nombre']; ?></td>
                            <td><?php echo $equipo['numero_serie']; ?></td>
                            <td><?php echo $equipo['responsable']; ?></td>
                            <td><?php echo $equipo['marca_nombre']; ?></td>
                            <td><?php echo $equipo['ubicacion_nombre']; ?></td>
                            <td><?php echo $equipo['estado']; ?></td>
                            <td>
                                <a href="hj_equipo.php?id=<?php echo urlencode((string)($equipo['id'] ?? $equipo['codigo'])); ?>" class="btn-azul" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <button class="btn-azul" title="Editar">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="btn-azul" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
                <button id="abrirModal" class="btn-azul">
                    <i class="fa-solid fa-plus"></i>
                    Nuevo equipo 
                </button>
            <div class="overlay-backdrop" id="backdropModal"></div>
            <div class="overlay-panel modal-tool" id="overlay">
            <div class="modal-header">
                <h2>Agregar nuevo equipo</h2>
                <button class="cerrar-modal" id="cerrarModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form class="form" method="post" enctype="multipart/form-data">
                <div class="inputs-row">
                    <div class="input-group">
                        <label for="codigo">Código:</label>
                        <input type="text" id="codigo" name="codigo" placeholder="Ingrese el código del equipo" required>
                    </div>
                    <div class="input-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ingrese el nombre del equipo" required>
                    </div>
                </div>
                <div class="input-group">
                    <label >Descripción:</label>
                    <textarea  rows="4" name="descripcion" placeholder="Ingrese la descripción" required></textarea>
                </div>
                <div class="inputs-row">
                    <div class="input-group">
                        <label for="serie">Número de serie:</label>
                        <input type="text" id="serie" name="serie" placeholder="Ingrese el número de serie del equipo" required>
                    </div>
                    <div class="input-group">
                        <label for="marca">Marca:</label>
                        <select id="marca" name="marca" required>
                            <option value="">Seleccione una marca</option>
                            <?php 
                                while($empresa=$empresas->fetch_assoc()){
                                    echo "<option value='".$empresa['nit']."'>".$empresa['nombre']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="inputs-row">
                    <div class="input-group">
                        <label for="fecha">Fecha ingreso:</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso"  required>
                    </div>
                    <div class="input-group">
                        <label for="ubicacion">Ubicación:</label>
                        <select id="ubicacion" name='ubicacion'required>
                            <option value="">Seleccione una ubicacion</option>
                            <?php 
                                while($ubicacion=$ubicaciones->fetch_assoc()){
                                    echo "<option value='".$ubicacion['id']."'>".$ubicacion['nombre']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="inputs-row">
                    <div class="input-group">
                        <label for="responsable">Responsable:</label>
                        <input type="text" id="responsable" name="responsable" placeholder="Ingrese el nombre del responsable" required>
                    </div>
                    <div class="input-group">
                        <label for="modelo">Modelo:</label>
                        <input type="text" id="modelo" name="modelo" placeholder="Ingrese el nombre del responsable" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione un estado</option>
                        <option value="Activo">Activo</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Fuera de servicio">Fuera de servicio</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="imagen">Imagen:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <button type="submit" class="btn-azul">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </form>
        </div>
    </div>
    </template>
    <script src="../scripts/base.js"></script>
</body>
</html>