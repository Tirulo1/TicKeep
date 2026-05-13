<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

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

$filename = "garantias_tickeep_" . date("Y-m-d") . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        .titulo {
            background: #202bbf;
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .subtitulo {
            background: #e8ebff;
            color: #202bbf;
            font-weight: bold;
        }

        .cabecera {
            background: #202bbf;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .celda {
            border: 1px solid #cccccc;
            padding: 8px;
        }

        .vigente {
            color: #16a34a;
            font-weight: bold;
        }

        .expira {
            color: #d97706;
            font-weight: bold;
        }

        .caducada {
            color: #dc2626;
            font-weight: bold;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="6" class="titulo">TicKeep - Exportación de garantías</td>
    </tr>
    <tr>
        <td colspan="6" class="subtitulo">Usuario: <?= h($usuario['nombre'] ?? 'Usuario') ?></td>
    </tr>
    <tr>
        <td colspan="6" class="subtitulo">Fecha de exportación: <?= date('d/m/Y H:i') ?></td>
    </tr>
    <tr>
        <td colspan="6" class="subtitulo">Total de garantías: <?= count($garantias) ?></td>
    </tr>
    <tr>
        <td colspan="6"></td>
    </tr>

    <tr>
        <td class="cabecera">Producto</td>
        <td class="cabecera">Tienda</td>
        <td class="cabecera">Fecha de compra</td>
        <td class="cabecera">Fecha de vencimiento</td>
        <td class="cabecera">Estado</td>
        <td class="cabecera">Comentarios</td>
    </tr>

    <?php if (count($garantias) > 0): ?>
        <?php foreach ($garantias as $g): ?>
            <?php
            $estado = $g['estado'] ?? 'Vigente';
            $claseEstado = 'vigente';

            if ($estado === 'Expira pronto') {
                $claseEstado = 'expira';
            } elseif ($estado === 'Caducada') {
                $claseEstado = 'caducada';
            }
            ?>
            <tr>
                <td class="celda"><strong><?= h($g['nombre_producto']) ?></strong></td>
                <td class="celda"><?= h($g['tienda'] ?: '-') ?></td>
                <td class="celda"><?= date('d/m/Y', strtotime($g['fecha_compra'])) ?></td>
                <td class="celda"><?= date('d/m/Y', strtotime($g['fecha_vencimiento'])) ?></td>
                <td class="celda <?= $claseEstado ?>"><?= h($estado) ?></td>
                <td class="celda"><?= h($g['comentarios'] ?: '-') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="celda">No hay garantías registradas.</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>