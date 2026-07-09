<?php
function tablaExiste(mysqli $conn, string $tabla): bool {
    $tabla = $conn->real_escape_string($tabla);
    $res = $conn->query("SHOW TABLES LIKE '{$tabla}'");
    return $res && $res->num_rows > 0;
}

function obtenerContextoMinovita(mysqli $conn): string {
    $partes = [];
    $partes[] = "Fecha y hora actual del sistema: " . date("Y-m-d H:i:s");

    if (tablaExiste($conn, "equipos")) {
        $res = $conn->query("SELECT nombre, tipo, estado FROM equipos ORDER BY estado LIMIT 30");
        if ($res && $res->num_rows > 0) {
            $partes[] = "\n=== ESTADO ACTUAL DE EQUIPOS ===";
            while ($e = $res->fetch_assoc()) {
                $partes[] = "- {$e['nombre']} ({$e['tipo']}): {$e['estado']}";
            }
        }
    }

    if (tablaExiste($conn, "alertas")) {
        $res = $conn->query("SELECT descripcion, nivel, fecha_hora FROM alertas WHERE resuelta = 0 ORDER BY fecha_hora DESC LIMIT 15");
        if ($res && $res->num_rows > 0) {
            $partes[] = "\n=== ALERTAS ACTIVAS ===";
            while ($a = $res->fetch_assoc()) {
                $partes[] = "- [{$a['nivel']}] {$a['descripcion']} — {$a['fecha_hora']}";
            }
        } else {
            $partes[] = "\n=== ALERTAS ACTIVAS ===\nNo hay alertas activas registradas.";
        }
    }

    if (count($partes) === 1) {
        $partes[] = "\nAún no hay datos en vivo conectados en este sistema (equipos/alertas). "
                  . "No inventes cifras: si te preguntan por el estado actual de la mina, "
                  . "indica que el módulo de monitoreo en vivo está en construcción.";
    }

    return implode("\n", $partes);
}