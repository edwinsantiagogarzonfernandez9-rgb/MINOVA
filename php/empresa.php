<?php
session_start();
include '../config/conexion.php';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nit = trim($_POST["nit"]);
    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    $direccion = trim($_POST["direccion"]);

    $stmt = $conn->prepare("INSERT INTO empresa (nit, nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nit, $nombre, $telefono, $correo, $direccion);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Empresa guardada correctamente';
    } else {
        $_SESSION['mensaje'] = 'Error: ' . $stmt->error;
    }

    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
$empresas = $conn->query("SELECT * FROM empresa");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - EMPRESAS</title>
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
                <h1>EMPRESAS</h1>
                <p>Este es el formato es de las empresas.</p>
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
                            placeholder="Buscar por nombre, telefono, correo, direccion"
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
                        <th><h4>Nit empresa</h4></th>
                        <th><h4>Nombre de la empresa</h4></th>
                        <th><h4>Telefono</h4></th>
                        <th><h4>Correo</h4></th>
                        <th><h4>Direccion</h4></th>
                        <th><h4>Acciones</h4></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($empresa = $empresas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $empresa['nit']; ?></td>
                        <td><?php echo $empresa['nombre']; ?></td>
                        <td><?php echo $empresa['telefono']; ?></td>
                        <td><?php echo $empresa['correo']; ?></td>
                        <td><?php echo $empresa['direccion']; ?></td>
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
        <button id="abrirModal" class="btn-azul">
            <i class="fa-solid fa-plus"></i>
            Nueva Empresa
        </button>
        <div class="overlay-backdrop" id="backdropModal"></div>
        <div class="overlay-panel modal-tool" id="overlay">
            <div class="modal-header">
                <h3><i class="fa-solid fa-plus"></i> Agregar nueva ubicación</h3>
                <button class="cerrar-modal" id="cerrarModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form class="form" method="post">
                <div class="inputs-row">
                    <div class="input-group">
                        <label >Nit de la empresa:</label>
                        <input type="text" name="nit" placeholder="Ingrese el Nit de la empresa" required>
                    </div>
                    <div class="input-group">
                        <label >Nombre de la empresa:</label>
                        <input type="text" name="nombre" placeholder="Ingrese el nombre de la empresa" required>
                    </div>
                </div>
                <div class="inputs-row">
                    <div class="input-group">
                        <label >Telefono:</label>
                        <input  type='number'  name="telefono" placeholder="Ingrese el teléfono" required></textarea>
                    </div>
                    <div class="input-group">
                        <label >Correo:</label>
                        <input type="email" name="correo" placeholder="Ingrese el correo electrónico" required>
                    </div>
                </div>
                    <div class="input-group">
                        <label >Direccion:</label>
                        <input  type='text' name="direccion" placeholder="Ingrese la dirección" required></textarea>
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