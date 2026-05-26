<?php

require_once __DIR__ . '/../../config/conexion_test.php';

echo "TEST ESTADOS\n";

try {
    $estados = [
        'Paquete registrado',
        'En oficina',
        'En proceso de ruta',
        'En ruta',
        'Entregado a usuario',
        'En sede para recoger',
        'Entregado en sede',
        'Cancelado'
    ];

    foreach ($estados as $estado) {
        $stmt = $conexion->prepare("
            SELECT id_estado
            FROM estado
            WHERE nombre_estado = :estado
            LIMIT 1
        ");

        $stmt->execute([
            ':estado' => $estado
        ]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ERROR: Falta estado $estado\n";
            exit(1);
        }

        echo "OK: Existe estado $estado\n";
    }

    echo "TEST ESTADOS OK\n";
    exit(0);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
