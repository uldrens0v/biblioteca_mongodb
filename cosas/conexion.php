<?php

include "cargar_env.php";
cargarEnv("secrets.env");
function estableceConexion()
{
    global $conexion;
    //datos de conexion base de datos postgres/supabase
    $host = 'aws-1-eu-west-1.pooler.supabase.com';
    $port = '6543';
    $db   = 'postgres';
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host=$host;port=$port;dbname=$db";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        return new PDO($dsn, $user, $pass, $options);
        /*echo "<h2>funcionando la conexion con supabase</h2>";
        $stmt = $pdo->query('SELECT version()');
        $version = $stmt->fetch();
        echo "<p><strong>version de BD:</strong> " . $version['version'] . "</p>";*/

    } catch (PDOException $e) {
        $error = $e->getMessage();
        echo "<h2>Error de conexión: $error</h2>";
    }
}
function cerrarConexion($conexion){
    $conexion = null ;
}


