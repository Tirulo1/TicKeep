<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id_usuario = $_SESSION['id_usuario'];

try {
    $queryUser = "SELECT nombre FROM usuarios WHERE id_usuario = :id";
    $stmtUser = $pdo->prepare($queryUser);
    $stmtUser->execute([':id' => $id_usuario]);
    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT nombre_producto, tienda, fecha_compra, fecha_vencimiento, estado, comentarios
            FROM garantias
            WHERE id_usuario = :id
            ORDER BY fecha_compra DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al exportar: " . $e->getMessage());
}

function h($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            background: #202bbf;
            color: white;
            padding: 18px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .info {
            margin-bottom: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #202bbf;
            color: white;
        }

        th {
            padding: 9px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #202bbf;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f4f6ff;
        }

        .estado-vigente {
            color: #16a34a;
            font-weight: bold;
        }

        .estado-expira {
            color: #d97706;
            font-weight: bold;
        }

        .estado-caducada {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>TicKeep</h1>
    <p>Exportación de garantías</p>
</div>

<div class="info">
    <strong>Usuario:</strong> ' . h($usuario['nombre'] ?? 'Usuario') . '<br>
    <strong>Fecha de exportación:</strong> ' . date('d/m/Y H:i') . '<br>
    <strong>Total de garantías:</strong> ' . count($garantias) . '
</div>

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th>Tienda</th>
            <th>Compra</th>
            <th>Vencimiento</th>
            <th>Estado</th>
            <th>Comentarios</th>
        </tr>
    </thead>
    <tbody>';

if (count($garantias) > 0) {
    foreach ($garantias as $g) {
        $estado = $g['estado'] ?? 'Vigente';
        $claseEstado = 'estado-vigente';

        if ($estado === 'Expira pronto') {
            $claseEstado = 'estado-expira';
        } elseif ($estado === 'Caducada') {
            $claseEstado = 'estado-caducada';
        }

        $html .= '
        <tr>
            <td><strong>' . h($g['nombre_producto']) . '</strong></td>
            <td>' . h($g['tienda'] ?: '-') . '</td>
            <td>' . date('d/m/Y', strtotime($g['fecha_compra'])) . '</td>
            <td>' . date('d/m/Y', strtotime($g['fecha_vencimiento'])) . '</td>
            <td class="' . $claseEstado . '">' . h($estado) . '</td>
            <td>' . h($g['comentarios'] ?: '-') . '</td>
        </tr>';
    }
} else {
    $html .= '
        <tr>
            <td colspan="6" style="text-align:center;">No hay garantías registradas.</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    © 2026 TicKeep. Tu tranquilidad, garantizada.
</div>

</body>
</html>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = "garantias_tickeep_" . date("Y-m-d") . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit();