<?php
require 'vendor/autoload.php';

use clases\Libro;
use clases\Revista;
use clases\Socio;
use clases\UsuarioOcasional;

function usuario_existe($dni, $collection) {
    return $collection->findOne(['dni' => $dni]) !== null;
}

function obtenerTodosLosDocumentos($collection) {
    $documentos = [];
    if ($collection) {
        $cursor = $collection->find(['tipo' => ['$in' => ['Libro', 'Revista']]]);
        foreach ($cursor as $docData) {
            $docObj = null;
            if (isset($docData['tipo']) && $docData['tipo'] === 'Libro') {
                $docObj = new Libro($docData['titulo'], $docData['codigo'], $docData['ano'] ?? 0);
            } elseif (isset($docData['tipo']) && $docData['tipo'] === 'Revista') {
                $docObj = new Revista($docData['codigo'], $docData['titulo']);
            }

            if ($docObj) {
                if (!empty($docData['prestado']) && !empty($docData['usuario_dni'])) {
                    $usuario = new Socio($docData['usuario_dni'], $docData['usuario_nombre'] ?? 'Desconocido');
                    $docObj->setPrestadoA($usuario);
                }
                $documentos[] = $docObj;
            }
        }
    }
    return $documentos;
}

$mensaje = "";
$collection = null;

$uri = 'mongodb+srv://danimegaguay2_db_user:qeiulO5NgYSFGBDE@biblioteca.zdjlimd.mongodb.net/?appName=biblioteca&tlsInsecure=true&port=443';
try {
    $client = new MongoDB\Client($uri, [], [
        'driver' => [
            'allow_invalid_hostname' => true,
        ],
    ]);

    $client->selectDatabase('admin')->command(['ping' => 1]);

    $db = $client->objetosPHP;
    $collection = $db->objects;
} catch (Exception $e) {
    die("error al conectar con la base de datos");
}

// procesamiento de todas las acciones post
if (isset($_POST['crear_libro'])) {
    $libro = new Libro($_POST['titulo'], $_POST['codigo'], $_POST['ano']);
    $collection->insertOne([
        'tipo' => 'Libro',
        'titulo' => $libro->getTitulo(),
        'codigo' => $libro->getCodigo(),
        'ano' => $libro->getAnoPublicacion(),
        'prestado' => false,
        'usuario_dni' => null
    ]);
    $mensaje = "libro añadido con éxito";

} elseif (isset($_POST['crear_revista'])) {
    $revista = new Revista($_POST['codigo'], $_POST['titulo']);
    $collection->insertOne([
        'tipo' => 'Revista',
        'titulo' => $revista->getTitulo(),
        'codigo' => $revista->getCodigo(),
        'prestado' => false,
        'usuario_dni' => null
    ]);
    $mensaje = "revista añadida con éxito";

} elseif (isset($_POST['crear_socio'])) {
    if (usuario_existe($_POST['dni'], $collection)) {
        $mensaje = "el usuario ya existe";
    } else {
        $socio = new Socio($_POST['dni'], $_POST['nombre']);
        $collection->insertOne([
            'tipo' => 'Socio',
            'dni' => $socio->getDNI(),
            'nombre' => $socio->getNombre()
        ]);
        $mensaje = "socio creado con éxito";
    }

} elseif (isset($_POST['crear_ocasional'])) {
    if (usuario_existe($_POST['dni'], $collection)) {
        $mensaje = "el usuario ya existe";
    } else {
        $ocasional = new UsuarioOcasional($_POST['dni'], $_POST['nombre']);
        $collection->insertOne([
            'tipo' => 'UsuarioOcasional',
            'dni' => $ocasional->getDNI(),
            'nombre' => $ocasional->getNombre()
        ]);
        $mensaje = "usuario ocasional creado con éxito";
    }

} elseif (isset($_POST['prestar'])) {
    $codigo = $_POST['cod_doc'];
    $dni = $_POST['dni_user'];
    $usuario = $collection->findOne(['tipo' => ['$in' => ['Socio', 'UsuarioOcasional']], 'dni' => $dni]);
    $documento = $collection->findOne(['codigo' => $codigo, 'prestado' => false]);

    if ($usuario && $documento) {
        $collection->updateOne(
            ['codigo' => $codigo],
            ['$set' => ['prestado' => true, 'usuario_dni' => $dni, 'usuario_nombre' => $usuario['nombre']]]
        );
        $mensaje = "prestamo realizado: " . $documento['titulo'] . " a " . $usuario['nombre'];
    } else {
        $mensaje = "error: usuario no existe o documento ya prestado";
    }

} elseif (isset($_POST['devuelve'])) {
    $codigo = $_POST['cod_devuelve'];
    $doc = $collection->findOne(['codigo' => $codigo, 'prestado' => true]);

    if ($doc) {
        $collection->updateOne(
            ['codigo' => $codigo],
            ['$set' => ['prestado' => false, 'usuario_dni' => null, 'usuario_nombre' => null]]
        );
        $mensaje = "documento devuelto correctamente";
    } else {
        $mensaje = "el documento no estaba prestado o no existe";
    }
}

// busqueda parcial
$resultadosBusqueda = null;
if (isset($_GET['buscar_texto']) && !empty($_GET['buscar_texto'])) {
    $texto = $_GET['buscar_texto'];
    $resultadosBusqueda = $collection->find(['titulo' => new MongoDB\BSON\Regex($texto, 'i')]);
}

$informePrestamos = $collection ? $collection->find(['prestado' => true]) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca ANDRES CARRERO FRAILE - MONGODB</title>

    <style>
        /* bloque css abierto */
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .section {
            background: white;
            padding: 15px;
            border-radius: 8px;
            width: 320px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .full-width {
            width: 95%;
            max-width: 1100px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            font-size: 0.85em;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            margin-top: 4px;
        }

        input[type="submit"] {
            margin-top: 12px;
            width: 100%;
            padding: 8px;
            cursor: pointer;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #333;
            color: white;
        }

        .mensaje {
            background: #d1ecf1;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
            text-align: center;
        }

        h3 {
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 5px;
            color: #333;
        }
    </style>
</head>
<body>

<?php
// salida de la interfaz mediante php
echo "<h1>Panel de Control Biblioteca</h1>";

if ($mensaje) {
    echo "<div class='mensaje'>$mensaje</div>";
}

echo "<div class='container'>";

// buscador de documentos
echo "<div class='section'>
        <h3>Buscar Documento</h3>
        <form method='get'>
            <input type='text' name='buscar_texto' placeholder='título...'>
            <input type='submit' value='buscar'>
        </form>";
if ($resultadosBusqueda) {
    echo "<ul style='font-size: 0.9em; margin-top: 10px;'>";
    foreach ($resultadosBusqueda as $res) {
        echo "<li><strong>" . $res['titulo'] . "</strong> (" . $res['codigo'] . ")</li>";
    }
    echo "</ul>";
}
echo "</div>";

// gestión de prestamos y devoluciones
echo "<div class='section'>
        <h3>Préstamo / Devolución</h3>
        <form method='post'>
            <label>Código Doc:</label> <input type='text' name='cod_doc'>
            <label>DNI Usuario:</label> <input type='text' name='dni_user'>
            <input type='submit' name='prestar' value='Realizar Préstamo'>
        </form>
        <hr>
        <form method='post'>
            <label>Código para devolver:</label> <input type='text' name='cod_devuelve'>
            <input type='submit' name='devuelve' value='Devolver Documento' style='background: #28a745;'>
        </form>
    </div>";

// formulario alta de libros
echo "<div class='section'>
        <h3>Nuevo Libro</h3>
        <form method='post'>
            <label>Título:</label> <input type='text' name='titulo' required>
            <label>Código:</label> <input type='text' name='codigo' required>
            <label>Año:</label> <input type='number' name='ano' required>
            <input type='submit' name='crear_libro' value='Guardar Libro'>
        </form>
    </div>";

// formulario alta de revistas
echo "<div class='section'>
        <h3>Nueva Revista</h3>
        <form method='post'>
            <label>Título:</label> <input type='text' name='titulo' required>
            <label>Código:</label> <input type='text' name='codigo' required>
            <input type='submit' name='crear_revista' value='Guardar Revista'>
        </form>
    </div>";

// formulario alta de socios
echo "<div class='section'>
        <h3>Nuevo Socio</h3>
        <form method='post'>
            <label>Nombre:</label> <input type='text' name='nombre' required>
            <label>DNI:</label> <input type='text' name='dni' required>
            <input type='submit' name='crear_socio' value='Registrar Socio'>
        </form>
    </div>";

// formulario alta de usuario ocasional
echo "<div class='section'>
        <h3>Usuario Ocasional</h3>
        <form method='post'>
            <label>Nombre:</label> <input type='text' name='nombre' required>
            <label>DNI:</label> <input type='text' name='dni' required>
            <input type='submit' name='crear_ocasional' value='Registrar Ocasional'>
        </form>
    </div>";

// tabla de informe general de prestamos
echo "<div class='section full-width'>
        <h3>Informe de Préstamos Activos</h3>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Código</th>
                    <th>Prestado a (DNI)</th>
                    <th>Nombre Usuario</th>
                </tr>
            </thead>
            <tbody>";
foreach ($informePrestamos as $docP) {
    echo "<tr>
            <td>" . ($docP['tipo'] ?? '') . "</td>
            <td>" . ($docP['titulo'] ?? '') . "</td>
            <td>" . ($docP['codigo'] ?? '') . "</td>
            <td>" . ($docP['usuario_dni'] ?? '') . "</td>
            <td>" . ($docP['usuario_nombre'] ?? '') . "</td>
          </tr>";
}
echo "      </tbody>
        </table>
    </div>";

// Nuevo bloque para mostrar los objetos
$todosLosDocumentos = obtenerTodosLosDocumentos($collection);
echo "<div class='section full-width'>
        <h3>Listado de Todos los Documentos</h3>
        <ul style='list-style-type: none; padding: 0;'>";
foreach ($todosLosDocumentos as $docObj) {
    echo "<li style='background: #f9f9f9; margin: 5px 0; padding: 10px; border-bottom: 1px solid #eee;'>" . $docObj->toString() . "</li>";
}
echo "  </ul>
    </div>
</div>";
?>

</body>
</html>