<?php

require_once __DIR__ . '/../../config/conexion_test.php';

echo "TEST TRACKING\n";

try {
    $stmt = $conexion->prepare("
        SELECT
            e.id_envio,
            e.codigo_guia,
            e.nombre_destinatario,
            es.nombre_estado,
            es.orden_estado
        FROM envio e
        INNER JOIN estado es ON e.estado_actual_id = es.id_estado
        ORDER BY e.id_envio DESC
        LIMIT 1
    ");

    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        echo "ERROR: No hay envíos para tracking\n";
        exit(1);
    }

    if (empty($resultado['codigo_guia'])) {
        echo "ERROR: Código guía vacío\n";
        exit(1);
    }

    if (empty($resultado['nombre_estado'])) {
        echo "ERROR: Estado vacío\n";
        exit(1);
    }

    $stmtHistorial = $conexion->prepare("
        SELECT COUNT(*)
        FROM historia_estado
        WHERE id_envio = :id_envio
    ");
    $stmtHistorial->execute([
        ':id_envio' => $resultado['id_envio']
    ]);

    $totalHistorial = (int)$stmtHistorial->fetchColumn();

    if ($totalHistorial <= 0) {
        echo "ERROR: El envío no tiene historial de estados\n";
        exit(1);
    }

    echo "OK: Código guía " . $resultado['codigo_guia'] . "\n";
    echo "OK: Estado actual " . $resultado['nombre_estado'] . "\n";
    echo "TEST TRACKING OK\n";
    exit(0);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
