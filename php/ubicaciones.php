<?php
session_start();
include "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");

    $stmt = $conn->prepare("INSERT INTO ubicacion (nombre, descripcion, categoria_ubicacion_id) VALUES (?, ?, ?)");
    $categoria_id = (int)$categoria;
    $stmt->bind_param("ssi", $nombre, $descripcion, $categoria_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Ubicación guardada correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$categorias = $conn->query("SELECT * FROM categoria_ubicacion");
$ubicaciones = $conn->query("SELECT ubicacion.*, categoria_ubicacion.nombre AS categoria_nombre FROM ubicacion LEFT JOIN categoria_ubicacion ON ubicacion.categoria_ubicacion_id = categoria_ubicacion.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - UBICACIONES</title>

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
                    <h1>UBICACIONES</h1>
                    <p>Este es el formato de las ubicaciones.</p>
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
                            placeholder="Buscar por nombre, descripción, categoria"
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
                    <thead>
                        <tr>
                            <th><h4>Nombre de la ubicación</h4></th>
                            <th><h4>Descripción</h4></th>
                            <th><h4>Categoria</h4></th>
                            <th><h4>Acciones</h4></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ubicacion = $ubicaciones->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $ubicacion['nombre']; ?> </td>
                            <td><?php echo $ubicacion['descripcion']; ?> </td>
                            <td><?php echo $ubicacion['categoria_nombre']; ?> </td>
                            <td><button class="btn-azul">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="btn-azul">
                                    <i class="fa-solid fa-trash"></i>
                                </button> 
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="btn-container">
                <button id="abrirModal" class="btn btn-azul">
                    <i class="fa-solid fa-plus"></i>
                    Nueva Ubicación
                </button>
                <a href="../php/categorias_ubicacion.php"><button class="btn-azul">
                    <i class="fa-solid fa-list"></i>
                    Categoria ubicaciones
                </button></a>
            </div>
        </div>

    <div class="overlay-backdrop" id="backdropModal"></div>
    <div class="overlay-panel modal-tool" id="overlay">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus"></i> Agregar nueva ubicación</h3>
            <button class="cerrar-modal" id="cerrarModal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form class="form" method="post">
            <div class="input-group">
                <label >Nombre de la ubicación:</label>
                <input type="text" name="nombre" placeholder="Ingrese el nombre de la ubicación" required>
            </div>
            <div class="input-group">
                <label >Descripción:</label>
                <textarea  rows="4" name="descripcion" placeholder="Ingrese la descripción" required></textarea>
            </div>
            <div class="input-group">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccione una categoría</option>
                    <?php  
                        while($cat = $categorias->fetch_assoc()){
                        echo "<option value='".$cat['id']."'>".$cat['nombre']."</option>";
                    }?>
                </select>
            </div>

            <button type="submit" class="btn btn-azul">
                <i class="fa-solid fa-floppy-disk"></i> Guardar
            </button>
        </form>
    </div>
    </template>
    <script src="../scripts/base.js"></script>
</body>
</html>