<?php
session_start();
include '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $stmt = $conn->prepare("INSERT INTO categoria_ubicacion (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Categoría guardada correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categorias = $conn->query("SELECT * FROM categoria_ubicacion");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - CATEGORIAS UBICACIONES</title>
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
                    <h1>CATEGORIAS UBICACIONES</h1>
                    <p>Este es el formato de las categorias de las ubicaciones.</p>
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
                                placeholder="Buscar por nombre, descripción, categoria"
                                oninput="doSearch()"
                            />
                        </div>
                        <button class="btn-buscar" onclick="doSearch()">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                    </div>
                </div>
            </section>
            <section class="cards2">
                <table>
                    <thead>
                        <tr>
                            <th><h4>Nombre de la categoria</h4></th>
                            <th><h4>Descripción</h4></th>
                            <th><h4>Acciones</h4></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($categoria = $categorias->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $categoria['nombre']; ?></td>
                            <td><?php echo $categoria['descripcion']; ?></td>
                            <td>
                                <button class="btn-azul">
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
            </section>
        
            <div class="btn-container">
                <a href="../php/ubicaciones.php"><button class="btn-azul">
                    <i class="fa-solid fa-list"></i>
                    Volver a Ubicaciones
                </button></a>
                <button id="abrirModal" class="btn-azul">
                    <i class="fa-solid fa-plus"></i>
                    Nueva Categoria
                </button>
            </div>
            </div>
            <div class="overlay-backdrop" id="backdropModal"></div>
            <div class="overlay-panel modal-tool" id="overlay">
                <div class="modal-header">
                    <h3><i class="fa-solid fa-plus"></i> Agregar nueva categoria</h3>
                    <button class="cerrar-modal" id="cerrarModal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form class="form" method="post">
                    <div class="input-group">
                        <label >Nombre de la categoria:</label>
                        <input type="text" name="nombre" placeholder="Ingrese el nombre de la categoria" required>
                    </div>
                    <div class="input-group">
                        <label >Descripción:</label>
                        <textarea  rows="4" name="descripcion" placeholder="Ingrese la descripción" required></textarea>
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