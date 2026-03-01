<?php


function visualizarTabla(PDOStatement $datos){
    $resultados = $datos->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultados) > 0) {
        echo "<table border='1'><tr>";

        // generar cabeceras usando las llaves del primer registro
        foreach (array_keys($resultados[0]) as $columna) {
            echo "<th>" . htmlspecialchars($columna) . "</th>";
        }
        echo "</tr>";

        // generar filas de datos
        foreach ($resultados as $fila) {
            echo "<tr>";
            foreach ($fila as $valor) {
                echo "<td>" . $valor . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }

}