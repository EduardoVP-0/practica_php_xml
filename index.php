<?php
/**
 * Práctica: Procesamiento de XML con libxml en PHP
 */


// ACTIVAR EL MANEJO INTERNO DE ERRORES DE LIBXML
// Por defecto, si el XML tiene errores, PHP lanza alertas feas en pantalla.
// Al activar esto, le decimos a libxml: "Guarda los errores en silencio,
// yo los revisare y los mostrare como yo quiera"
libxml_use_internal_errors(true);

// Ruta al archivo XML
$rutaArchivo = __DIR__ . '/datos.xml';

try {
    // 2. LECTURA CON STREAMS
    // Un stream es un flujo continuo de datos. En vez de leer todo de golpe a ciegas,
    // abrimos un canal seguro con 'fopen' en modo lectura ('r').
    if (!file_exists($rutaArchivo)) {
        throw new Exception("El archivo 'datos.xml' no fue encontrado en la ruta indicada.");
    }

    $stream = fopen($rutaArchivo, 'r');
    if (!$stream) {
        throw new Exception("No se pudo abrir el stream de lectura para el archivo XML.");
    }

    // Leemos todo el contenido que viaja por el stream
    $contenidoXml = stream_get_contents($stream);
    
    // Cerramos el stream para liberar recursos de la computadora
    fclose($stream);

    // 3. OPCIONES DEL PARSER DE LIBXML
    // Un "parser" es el analizador que lee el texto y lo convierte en objetos
    // Aquí configuramos opciones oficiales de libxml:
    // - LIBXML_NOCDATA: Convierte los bloques CDATA en texto normal
    // - LIBXML_NOBLANKS: Elimina espacios y saltos de linea vacios innecesarios
    $opcionesParser = LIBXML_NOCDATA | LIBXML_NOBLANKS;

    // 4. PARSEAR (PROCESAR) EL XML CON LIBXML / SIMPEXML
    // simplexml_load_string procesa el texto usando el motor libxml de PHP.
    $xml = simplexml_load_string($contenidoXml, 'SimpleXMLElement', $opcionesParser);

    // Si falló el parseo, obtenemos los errores guardados en libxml
    if ($xml === false) {
        $errores = libxml_get_errors();
        $mensajeError = "Se encontraron errores al procesar el XML:<br><ul>";
        foreach ($errores as $error) {
            $mensajeError .= "<li>Línea {$error->line}: " . htmlspecialchars(trim($error->message)) . "</li>";
        }
        $mensajeError .= "</ul>";
        
        // Limpiamos el buffer de errores de libxml
        libxml_clear_errors();

        throw new Exception($mensajeError);
    }

} catch (Exception $e) {
    // Si algo falló, guardamos el error para mostrarlo en una alerta bonita
    $errorCapturado = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesador de XML con libxml en PHP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 30px;
            color: #333;
        }
        .contenedor {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
        }
        .badge {
            display: inline-block;
            background: #27ae60;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px 20px;
            border-left: 5px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }
        th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f9fbfd;
        }
        .precio {
            color: #2980b9;
            font-weight: bold;
        }
        .tag {
            background: #eef2f7;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="contenedor">
    <h1>Catálogo de Libros (Procesado con libxml)</h1>

    <?php if (isset($errorCapturado)): ?>
        <div class="error-box">
            <strong>¡Atención! Ocurrió una excepción:</strong>
            <p><?= $errorCapturado ?></p>
        </div>
    <?php else: ?>
        <span class="badge">✓ Archivo procesado correctamente mediante Stream y libxml</span>
        <p><strong>Total de libros encontrados:</strong> <?= count($xml->libro) ?></p>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Categoría</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Año</th>
                    <th>Precio</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($xml->libro as $libro): ?>
                    <tr>
                        <td><span class="tag">#<?= htmlspecialchars($libro['id']) ?></span></td>
                        <td><?= htmlspecialchars($libro['categoria']) ?></td>
                        <td><strong><?= htmlspecialchars($libro->titulo) ?></strong></td>
                        <td><?= htmlspecialchars($libro->autor) ?></td>
                        <td><?= htmlspecialchars($libro->anio) ?></td>
                        <td class="precio"><?= htmlspecialchars($libro->precio) ?> <?= htmlspecialchars($libro->precio['moneda']) ?></td>
                        <td><?= htmlspecialchars($libro->descripcion) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>

