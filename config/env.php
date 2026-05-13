<?php

function cargarEnv($ruta)
{
    if (!file_exists($ruta)) {
        return;
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }

        [$clave, $valor] = array_pad(explode('=', $linea, 2), 2, '');

        $clave = trim($clave);
        $valor = trim($valor);

        if ($clave !== '') {
            putenv("$clave=$valor");
            $_ENV[$clave] = $valor;
        }
    }
}