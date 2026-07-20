<?php
session_start();
include "../config/conexion.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $serie = $_POST['serie'];
    $ubicacion = $_POST['ubicacion'];
    $usoMaquina = $_POST['usoMaquina'];
    $responsable = $_POST['responsable'];
    $fechaAdquisicion = $_POST['fechaAdquisicion'];
    $costoAdquisicion = $_POST['costoAdquisicion'];
    $estado = $_POST['estado'];
    $maquinaenoperacion = $_POST['maquinaenoperacion'];
    $garantia = $_POST['garantia'];
    $categoria = $_POST['categoria'];
    $caracteristicas = $_POST['caracteristicas'];
    $imagen='';
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
    $stmt = $conn->prepare("INSERT INTO maquina (codigo,nombre,modelo,numero_serie,fecha_adquisicion,costo_adquisicion,estado,garantia,imagen,ubicacion_id,categoria_maquina_id,empresa_nit,responsable,uso_maquina,en_operacion,caracteristicas) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssssiiissss", $codigo, $nombre, $modelo, $serie, $fechaAdquisicion, $costoAdquisicion, $estado, $garantia, $imagen, $ubicacion, $categoria, $marca, $responsable, $usoMaquina, $maquinaenoperacion, $caracteristicas);
    if($imagen !== '') {
        $stmt->send_long_data(9, $imagen); 
    }
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = 'Máquina agregada exitosamente.';
    } else {
        $_SESSION['mensaje'] = 'Error al agregar la máquina: ' . $stmt->error;
    };
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$ubicaciones= $conn->query("SELECT * FROM ubicacion");
$empresas= $conn->query("SELECT * FROM empresa");
$categorias= $conn->query("SELECT * FROM categoria_maquina");
$maquinas= $conn->query("SELECT m.id, m.codigo, m.nombre, m.numero_serie, m.modelo, m.estado, u.nombre AS nombre_ubicacion, e.nombre AS nombre_empresa FROM maquina m JOIN ubicacion u ON m.ubicacion_id = u.id JOIN empresa e ON m.empresa_nit = e.nit");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINOVA - MAQUINAS</title>
    <link rel="stylesheet" href="../styles/base.css ">
    <link rel="stylesheet" href="../styles/estilos_index.css">
    <link rel="stylesheet" href="../styles/estilos_tabla.css">
    <link rel="stylesheet" href="../styles/componentes.css">
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
                    <h1>MAQUINAS</h1>
                    <p>Este es el formato de las máquinas.</p>

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
                            <th>Código</th>
                            <th>Número de serie</th>
                            <th>Tipo</th>
                            <th>Marca</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($maquina = $maquinas->fetch_assoc()): ?>
                       <tr>
                         
                            <td><?php echo htmlspecialchars($maquina['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['numero_serie']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['nombre_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['nombre_ubicacion']); ?></td>
                            <td><?php echo htmlspecialchars($maquina['estado']); ?></td>
                            <td>
                                <a href="hj_maquina.php?id=<?php echo $maquina['id']; ?>" class="btn-azul">
                                    <i class="fa-solid fa-eye"></i> 
                                </a>
                                <a href="editar_maquina.php?id=<?php echo $maquina['id']; ?>" class="btn-azul">
                                    <i class="fa-solid fa-pen-to-square"></i> 
                                </a>
                                <a href="eliminar_maquina.php?id=<?php echo $maquina['id']; ?>" class="btn-azul" onclick="return confirm('¿Está seguro de que desea eliminar esta máquina?');">
                                    <i class="fa-solid fa-trash"></i> 
                                </a>
                            </td>
                        </tr>
                          <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <div class="btn-container">
                <button id="abrirModal" class="btn btn-azul">
                    <i class="fa-solid fa-plus"></i>
                    Nueva Máquina
                </button>
                <a href="categorias_maquinas.php"><button type="submit" class="btn-azul">
                    <i class="fa-solid fa-list"></i> Categorias maquinas
                </button></a>
            </div>
            <div class="overlay-backdrop" id="backdropModal"></div>
            <div class="overlay-panel modal-tool" id="overlay">
                <div class="modal-header">
                    <h2>Agregar nueva máquina</h2>
                    <button class="cerrar-modal" id="cerrarModal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form class="form" method="post" enctype="multipart/form-data">
                    <div class="inputs-row">
                        <div class="input-group">
                            <label for="codigo">Código:</label>
                            <input type="text" id="codigo" name="codigo" placeholder="Código de la máquina" required>
                        </div>
                        <div class="input-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Nombre de la máquina" required>
                        </div>
                    </div>
                    <div class="inputs-row"> 
                        <div class="input-group">
                            <label for="modelo">Modelo:</label>
                            <input type="text" id="modelo" name="modelo" placeholder="Modelo de la maquina">
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
                            <label for="serie">Número de serie:</label>
                            <input type="text" id="serie" name="serie" placeholder="Número de serie" required>
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
                            <label for="usoMaquina">Uso de la maquina:</label>
                            <input type="text" id="usoMaquina" name="usoMaquina" placeholder="Uso de la maquina">
                        </div>
                        <div class="input-group">
                            <label for="responsable">Responsable:</label>
                            <input type="text" id="responsable" name="responsable" placeholder="Ingrese el nombre del responsable" required>
                        </div>
                    </div>
                    <div class="inputs-row">
                        <div class="input-group">
                            <label for="fechaAdquisicion">Fecha de adquisición:</label>
                            <input type="date" id="fechaAdquisicion" name="fechaAdquisicion" >
                        </div>
                        <div class="input-group">
                            <label for="costoAdquisicion">Costo adquisición:</label>
                            <input type="number" id="costoAdquisicion" name="costoAdquisicion" placeholder="Costo de adquisición">
                        </div>
                    </div>
                    <div class="inputs-row">
                        <div class="input-group">
                            <label for="estado">Estado:</label>
                            <select id="estado" name="estado" required>
                                <option value="Activo">Activo</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Fuera de servicio">Fuera de servicio</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="maquinaenoperacion">Maquina en operación:</label>
                            <select id="maquinaenoperacion" name="maquinaenoperacion" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="inputs-row">
                        <div class="input-group">
                            <label for="garantia">Garantía:</label>
                            <input type="number" id="garantia" name="garantia" placeholder="Garantía en meses">
                        </div>
                        <div class="input-group">
                            <label for="categoria">Categoría:</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccione una categoria</option>
                                <?php
                                while ($cat = $categorias->fetch_assoc()) {
                                    echo "<option value='".$cat['id']."'>".$cat['nombre']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="caracteristicas">Caracteristicas:</label>
                        <textarea id="caracteristicas" name="caracteristicas" placeholder="Características de la maquina"></textarea>
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