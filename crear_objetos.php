<?php
require 'vendor/autoload.php';

use clases\Libro;
use clases\Revista;
use clases\Socio;
use clases\UsuarioOcasional;

$mensaje = "";
$collection = null;

try {
    $url = 'mongodb+srv://danimegaguay2_db_user:lLvH7Yf02ljBxN7L@biblioteca.zdjlimd.mongodb.net/?appName=biblioteca';
    $client = new MongoDB\Client($url);
    $db = $client->objetosPHP;
    $collection = $db->objects;
} catch (Exception $e) {
    $mensaje = "error de conexión: " . $e->getMessage();
}
function usuario_existe($dni,$collection)
{
    $usuario = $collection->findOne(['tipo' => ['$in' => ['Socio', 'UsuarioOcasional']], 'dni' => $dni]);
    return $usuario;

}

    // crear libro
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

        // crear revista
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

        // crear socio
    } elseif (isset($_POST['crear_socio'])) {
        if (usuario_existe($_POST['dni'], $collection)) {
            $mensaje = "el usuario ya existe";
        } else{
            $socio = new Socio($_POST['dni'], $_POST['nombre']);
            $collection->insertOne([
                    'tipo' => 'Socio',
                    'dni' => $socio->getDNI(),
                    'nombre' => $socio->getNombre()
            ]);
            $mensaje = "socio creado con éxito";

        }

        // crear usuario ocasional
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

        // realizar prestamo
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
            $mensaje = "error: usuario no existe o documento ya prestado/no encontrado";
        }

        // devolver documento
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
    $resultadosBusqueda = $collection->find([
            'titulo' => new MongoDB\BSON\Regex($texto, 'i')
    ]);
}

// lista de prestamos activos
$informePrestamos = $collection ? $collection->find(['prestado' => true]) : [];


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Integral Biblioteca</title>
    <style>
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

<h1>Panel de Control Biblioteca</h1>

<?php if ($mensaje): ?>
    <div class="mensaje"><?php echo $mensaje; ?></div><?php endif; ?>

<div class="container">

    <div class="section">
        <h3>Buscar Documento</h3>
        <form method="get">
            <input type="text" name="buscar_texto" placeholder="título...">
            <input type="submit" value="buscar">
        </form>
        <?php if ($resultadosBusqueda): ?>
            <ul style="font-size: 0.9em; margin-top: 10px;">
                <?php foreach ($resultadosBusqueda as $res): ?>
                    <li><strong><?php echo $res['titulo']; ?></strong> (<?php echo $res['codigo']; ?>)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="section">
        <h3>Préstamo / Devolución</h3>
        <form method="post">
            <label>Código Doc:</label> <input type="text" name="cod_doc">
            <label>DNI Usuario:</label> <input type="text" name="dni_user">
            <input type="submit" name="prestar" value="Realizar Préstamo">
        </form>
        <hr>
        <form method="post">
            <label>Código para devolver:</label> <input type="text" name="cod_devuelve">
            <input type="submit" name="devuelve" value="Devolver Documento" style="background: #28a745;">
        </form>
    </div>

    <div class="section">
        <h3>Nuevo Libro</h3>
        <form method="post">
            <label>Título:</label> <input type="text" name="titulo" required>
            <label>Código:</label> <input type="text" name="codigo" required>
            <label>Año:</label> <input type="number" name="ano" required>
            <input type="submit" name="crear_libro" value="Guardar Libro">
        </form>
    </div>

    <div class="section">
        <h3>Nueva Revista</h3>
        <form method="post">
            <label>Título:</label> <input type="text" name="titulo" required>
            <label>Código:</label> <input type="text" name="codigo" required>
            <input type="submit" name="crear_revista" value="Guardar Revista">
        </form>
    </div>

    <div class="section">
        <h3>Nuevo Socio</h3>
        <form method="post">
            <label>Nombre:</label> <input type="text" name="nombre" required>
            <label>DNI:</label> <input type="text" name="dni" required>
            <input type="submit" name="crear_socio" value="Registrar Socio">
        </form>
    </div>

    <div class="section">
        <h3>Usuario Ocasional</h3>
        <form method="post">
            <label>Nombre:</label> <input type="text" name="nombre" required>
            <label>DNI:</label> <input type="text" name="dni" required>
            <input type="submit" name="crear_ocasional" value="Registrar Ocasional">
        </form>
    </div>

    <div class="section full-width">
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
            <tbody>
            <?php foreach ($informePrestamos as $docP): ?>
                <tr>
                    <td><?php echo $docP['tipo']; ?></td>
                    <td><?php echo $docP['titulo']; ?></td>
                    <td><?php echo $docP['codigo']; ?></td>
                    <td><?php echo $docP['usuario_dni']; ?></td>
                    <td><?php echo $docP['usuario_nombre']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>