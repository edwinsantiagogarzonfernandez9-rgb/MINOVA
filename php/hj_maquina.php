<?php
session_start();
include "../config/conexion.php";
$detallemaquina=null;
$idmaquina=isset($_GET['id']) ? trim($_GET['id']):'';
if($idmaquina!==''){
    $stmt=$conn->prepare("SELECT maquina.*, empresa.nombre AS nombre_empresa, ubicacion.nombre AS nombre_ubicacion, categoria_maquina.nombre AS categoria_nombre FROM maquina LEFT JOIN empresa ON maquina.empresa_nit = empresa.nit LEFT JOIN ubicacion ON maquina.ubicacion_id = ubicacion.id LEFT JOIN categoria_maquina ON maquina.categoria_maquina_id = categoria_maquina.id WHERE maquina.id=? OR maquina.codigo=?");
    $stmt->bind_param("ss",$idmaquina,$idmaquina);
    $stmt->execute();
    $resultado=$stmt->get_result();
    $detallemaquina=$resultado->fetch_assoc();
    $stmt->close();
}
if(!$detallemaquina){
    $detallemaquina=[
        'nombre'=>'Sin información',
        'codigo'=>'',
        'modelo'=>'',
        'numero_serie'=>'',
        'fecha_adquisicion'=>'',
        'costo_adquisicion'=>'',
        'estado'=>'',
        'garantia'=>'',
        'imagen'=>'',
        'nombre_empresa'=>'',
        'nombre_ubicacion'=>'',
        'categoria_nombre'=>'',
        'responsable'=>'',
        'uso_maquina'=>'',
        'en_operacion'=>'',
        'caracteristicas'=>''
    ];
}
$imagenmaquina='../img/electrobomba.jpg';
if(!empty($detallemaquina['imagen'])){
    $valorImagen=$detallemaquina['imagen'];
    if(is_string($valorImagen) && preg_match('/\.(png|jpe?g|gif|webp|bmp)$/i',$valorImagen)){
        $nombreImagen=basename($valorImagen);
        $rutaImagen=__DIR__.'/../img/'.$nombreImagen;
        if(file_exists($rutaImagen)){
            $imagenmaquina='../img/'.$nombreImagen;
        }
    }elseif(is_string($valorImagen) && $valorImagen!==''){
        $finfo=new finfo(FILEINFO_MIME_TYPE);
        $mime=$finfo->buffer($valorImagen) ?: 'image/jpeg';
        $imagenmaquina='data:'.$mime.';base64,'.base64_encode($valorImagen);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de vida - <?php echo htmlspecialchars($detallemaquina['nombre']); ?></title>
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/componentes.css">
    <link rel="stylesheet" href="../styles/estilos_index.css">
    <link rel="icon" type="image/png" href="../img/logo-minova.png">
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../scripts/base.js" defer></script>
</head>
<body> 
    <div id="base-container"></div>
    <template id="page-content">
        <div class="grid-contenedor-2">
            <div class="welcome">
                <div>
                    <h1><?php echo htmlspecialchars($detallemaquina['nombre']); ?></h1>
                    <p>Hoja de vida de la maquina</p>
                </div>
                    <div class="date">
                        <i class="fa-regular fa-calendar"></i>
                        <span id="fechaActual"></span>
                    </div>
                
            </div>

                <section class="cards">
                    <div class="card">
                        <div class="cont-img">
                            <img src="<?php echo htmlspecialchars($imagenmaquina); ?>" alt="Imagen de la máquina">
                        </div>
                    </div>
                    <div class="card" style="list-style: none;font-size: 1.25rem;">

                            <li><strong>NOMBRE:</strong> <span id="eb_nombre"><?php echo htmlspecialchars($detallemaquina['nombre']); ?></span></li>
                            <li><strong>CÓDIGO PROGRAMA:</strong> <span id="eb_codigoPrograma"><?php echo htmlspecialchars($detallemaquina['codigo']); ?></span></li>
                            <li><strong>MARCA:</strong> <span id="eb_marca"><?php echo htmlspecialchars($detallemaquina['nombre_empresa']); ?></span></li>
                            <li><strong>MODELO:</strong> <span id="eb_modelo"><?php echo htmlspecialchars($detallemaquina['modelo']); ?></span></li>
                            <li><strong>GARANTIA:</strong> <span id="eb_garantia"><?php echo htmlspecialchars($detallemaquina['garantia']); ?></span></li>
                            <li><strong>USO:</strong> <span id="eb_uso"><?php echo htmlspecialchars($detallemaquina['uso_maquina']); ?> </span></li>
                    </div>
                </section>
                <section class="cards2">
                    <div class="card" style="list-style: none;font-size: 1.25rem;">        
                            <li><strong>EMPRESA:</strong> <span id="eb_empresaPrincipal">Sena Centro Minero</span></li>
                            <li><strong>LUGAR DE TRABAJO:</strong> <span id="eb_lugarTrabajo"><?php echo htmlspecialchars($detallemaquina['nombre_ubicacion']); ?></span></li>
                            <li><strong>UBICACIÓN:</strong> <span id="eb_ubicacionGeneral">Sena Centro Minero - Morca</span></li>
                            <li><strong>CIUDAD:</strong> <span id="eb_ciudad">Sogamoso</span></li>
                            <li><strong>FECHA ADQUISICION:</strong> <span id="eb_fecha"><?php echo htmlspecialchars($detallemaquina['fecha_adquisicion']); ?></span></li>
                            <li><strong>EQUIPO EN OPERACIÓN:</strong> <span id="eb_equipoOperacion"><?php echo htmlspecialchars($detallemaquina['en_operacion']); ?></span></li>
                            <li><strong>ESTADO:</strong> <span id="eb_estado"><?php echo htmlspecialchars($detallemaquina['estado']); ?></span></li>
                        </div>
                    <div class="card" style="list-style: none;font-size: 1.25rem;">
                            <li><strong>RESPONSABLE:</strong> <span id="eb_responsable"><?php echo htmlspecialchars($detallemaquina['responsable']); ?></span></li>
                            <li><strong>SERIAL:</strong> <span id="eb_serial"><?php echo htmlspecialchars($detallemaquina['numero_serie']); ?></span></li>
                            <li><strong>CARACTERÍSTICAS:</strong> <span id="eb_caracteristicas"><?php echo htmlspecialchars($detallemaquina['caracteristicas']); ?></span></li>
                    </div>
                </section>
                <section class="middle">
                    <div class="btn-container">
                        <button class="btn-azul"><i class="fas fa-home"></i> Uso diario</button>
                        <button class="btn-azul"><i class="fas fa-cog"></i> Pre operacional</button>
                        <a href="agregar_inspeccion.php"><button class="btn-azul"><i class="fas fa-search"></i> Inspeccion</button></a>
                    </div>
                </section>
        </div>
    </template>
</body>
</html>