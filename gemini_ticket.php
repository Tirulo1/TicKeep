<?php

function limpiarJsonGemini(string $texto): string
{
    $texto = trim($texto);
    $texto = str_replace("```json", "", $texto);
    $texto = str_replace("```", "", $texto);
    return trim($texto);
}

function procesarTicketGemini(string $rutaImagen): array
{
    require_once __DIR__ . '/config/env.php';
    cargarEnv(__DIR__ . '/.env');

    $apiKey = getenv('GEMINI_API_KEY');

    if (!file_exists($rutaImagen)) {
        return [
            'ok' => false,
            'error' => 'No existe la imagen del ticket.'
        ];
    }

    $mime = mime_content_type($rutaImagen);
    $base64 = base64_encode(file_get_contents($rutaImagen));

    $prompt = "
Analiza este ticket de compra.

Devuelve SOLO un JSON válido, sin markdown, con estas claves exactas:
{
  \"nombre_producto\": \"\",
  \"tienda\": \"\",
  \"fecha_compra\": \"\"
}

Reglas:
- fecha_compra debe estar en formato YYYY-MM-DD.
- tienda debe ser el comercio principal.
- nombre_producto debe ser el producto comprado más reconocible del ticket.
- Si el ticket es de ropa, devuelve el artículo principal, por ejemplo: Pantalón, Camiseta, Chaqueta, Zapatillas.
- Si hay varios productos, elige el primero o el más relevante.
- No dejes nombre_producto vacío salvo que sea imposible leerlo.
- No añadas explicaciones.
";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => $mime,
                            "data" => $base64
                        ]
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "maxOutputTokens" => 1000
        ]
    ];

    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if ($response === false) {
        return [
            'ok' => false,
            'error' => 'Error cURL: ' . curl_error($ch)
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $respuestaArray = json_decode($response, true);

    if ($httpCode !== 200) {
        return [
            'ok' => false,
            'error' => 'Error HTTP ' . $httpCode
        ];
    }

    $texto = $respuestaArray['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $jsonLimpio = limpiarJsonGemini($texto);
    $datos = json_decode($jsonLimpio, true);

    if (!is_array($datos)) {
        return [
            'ok' => false,
            'error' => 'No se pudo interpretar el JSON devuelto por Gemini.'
        ];
    }

    return [
        'ok' => true,
        'nombre_producto' => trim($datos['nombre_producto'] ?? ''),
        'tienda' => trim($datos['tienda'] ?? ''),
        'fecha_compra' => trim($datos['fecha_compra'] ?? '')
    ];
}
