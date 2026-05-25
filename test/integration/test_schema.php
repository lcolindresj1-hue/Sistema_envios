<?php

require_once __DIR__ . '/../../config/conexion.php';

echo "TEST SCHEMA\n";

try {
    $tablas = ['rol', 'usuario', 'estado', 'envio', 'historia_estado'];

    foreach ($tablas as $tabla) {
        $stmt = $conexion->prepare("SHOW TABLES LIKE :tabla");
        $stmt->execute([':tabla' => $tabla]);

        if (!$stmt->fetch()) {
            echo "ERROR: No existe la tabla $tabla\n";
            exit(1);
        }

        echo "OK: Existe tabla $tabla\n";
    }

    $columnasNecesarias = [
        'usuario' => ['id_usuario', 'id_rol', 'nombres', 'apellidos', 'correo', 'password_hash'],
        'rol' => ['id_rol', 'nombre'],
        'estado' => ['id_estado', 'nombre_estado'],
        'envio' => [
            'id_envio',
            'codigo_guia',
            'id_usuario_remitente',
            'nombre_destinatario',
            'telefono_destinatario',
            'direccion_destinatario',
            'descripcion_paquete',
            'estado_actual_id'
        ],
        'historia_estado' => ['id_envio', 'id_estado', 'id_usuario', 'comentario']
    ];

    foreach ($columnasNecesarias as $tabla => $columnas) {
        $stmt = $conexion->query("SHOW COLUMNS FROM `$tabla`");
        $columnasTabla = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

        foreach ($columnas as $columna) {
            if (!in_array($columna, $columnasTabla, true)) {
                echo "ERROR: Falta columna $tabla.$columna\n";
                exit(1);
            }
        }

        echo "OK: Columnas principales de $tabla verificadas\n";
    }

    echo "TEST SCHEMA OK\n";
    exit(0);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
