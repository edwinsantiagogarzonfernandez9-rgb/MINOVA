<?php


$host    = "localhost";
$usuario = "root";
$clave   = "";
$bd      = "minova";
$puertos = [3306, 3307,]; // agrega más puertos aquí si algún día hace falta

$conn         = null;
$ultimoError  = null;

foreach ($puertos as $puerto) {
    try {
        $conn = new mysqli($host, $usuario, $clave, $bd, $puerto);
        break; 
    } catch (mysqli_sql_exception $e) {
        $ultimoError = $e->getMessage();
        $conn = null;
    }
}

if (!$conn) {
    die(
        "Error: no se pudo conectar a MySQL en ningún puerto (" . implode(", ", $puertos) . ").<br>" .
        "Último error: " . htmlspecialchars($ultimoError) . "<br><br>" .
        "Revisa que MySQL esté corriendo en XAMPP y que el usuario/contraseña de este archivo " .
        "coincidan con los de tu instalación local."
    );
}
?>