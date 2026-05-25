<?php

require_once __DIR__ . '/../../config/conexion.php';

echo "TEST INSERT ENVIO\n";

function obtenerColumnas(PDO $conexion, string $tabla): array
{
    $stmt = $conexion->query("SHOW COLUMNS FROM `$tabla`");
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
}

function agregarSiExiste(array &$datos, array $columnas, string $columna, mixed $valor): void
{
    if (in_array($columna, $columnas, true)) {
        $datos[$columna] = $valor;
    }
}

try {
    $columnasEnvio = obtenerColumnas($conexion, 'envio');

    $stmtUsuario = $conexion->query("
        SELECT id_usuario
        FROM usuario
        ORDER BY id_usuario ASC
        LIMIT 1
    ");
    $idUsuario = (int)$stmtUsuario->fetchColumn();

    if ($idUsuario <= 0) {
        throw new Exception('No existe ningún usuario para usar como remitente.');
    }

    $stmtEstado = $conexion->prepare("
        SELECT id_estado
        FROM estado
        WHERE nombre_estado = :estado
        LIMIT 1
    ");
    $stmtEstado->execute([
        ':estado' => 'Paquete registrado'
    ]);
    $idEstadoInicial = (int)$stmtEstado->fetchColumn();

    if ($idEstadoInicial <= 0) {
        throw new Exception('No existe el estado inicial Paquete registrado.');
    }

    $codigo = 'TEST-' . date('YmdHis') . '-' . random_int(1000, 9999);

    $datos = [];

    agregarSiExiste($datos, $columnasEnvio, 'codigo_guia', $codigo);
    agregarSiExiste($datos, $columnasEnvio, 'id_usuario_remitente', $idUsuario);

    // Datos del remitente agregados para soportar el nuevo flujo:
    // Cliente: automático. Admin/Operador: digitado manualmente.
    agregarSiExiste($datos, $columnasEnvio, 'nombre_remitente', 'Remitente Integracion');
    agregarSiExiste($datos, $columnasEnvio, 'telefono_remitente', '55550000');
    agregarSiExiste($datos, $columnasEnvio, 'correo_remitente', 'remitente@test.com');
    agregarSiExiste($datos, $columnasEnvio, 'direccion_remitente', 'Zona 1');
    agregarSiExiste($datos, $columnasEnvio, 'remitente_nombre', 'Remitente Integracion');
    agregarSiExiste($datos, $columnasEnvio, 'remitente_telefono', '55550000');

    agregarSiExiste($datos, $columnasEnvio, 'nombre_destinatario', 'Cliente Test');
    agregarSiExiste($datos, $columnasEnvio, 'telefono_destinatario', '55555555');
    agregarSiExiste($datos, $columnasEnvio, 'direccion_destinatario', 'Zona 1');
    agregarSiExiste($datos, $columnasEnvio, 'descripcion_paquete', 'Paquete Integracion');
    agregarSiExiste($datos, $columnasEnvio, 'peso', 2.50);
    agregarSiExiste($datos, $columnasEnvio, 'es_fragil', 1);
    agregarSiExiste($datos, $columnasEnvio, 'tipo_paquete', 'Caja');
    agregarSiExiste($datos, $columnasEnvio, 'estado_actual_id', $idEstadoInicial);
    agregarSiExiste($datos, $columnasEnvio, 'observaciones', 'Prueba de integración');
    agregarSiExiste($datos, $columnasEnvio, 'instrucciones_entrega', 'Entregar en recepción');

    if (empty($datos)) {
        throw new Exception('No se encontraron columnas válidas para insertar el envío.');
    }

    $columnas = array_keys($datos);
    $params = array_map(fn($columna) => ':' . $columna, $columnas);

    $sql = "
        INSERT INTO envio (
            `" . implode('`, `', $columnas) . "`
        )
        VALUES (
            " . implode(', ', $params) . "
        )
    ";

    $conexion->beginTransaction();

    $stmt = $conexion->prepare($sql);

    foreach ($datos as $columna => $valor) {
        $stmt->bindValue(':' . $columna, $valor);
    }

    $stmt->execute();

    $idEnvio = (int)$conexion->lastInsertId();

    if ($idEnvio <= 0) {
        throw new Exception('No se pudo obtener el ID del envío insertado.');
    }

    $stmtHistorial = $conexion->prepare("
        INSERT INTO historia_estado (
            id_envio,
            id_estado,
            id_usuario,
            comentario
        )
        VALUES (
            :id_envio,
            :id_estado,
            :id_usuario,
            :comentario
        )
    ");

    $stmtHistorial->execute([
        ':id_envio' => $idEnvio,
        ':id_estado' => $idEstadoInicial,
        ':id_usuario' => $idUsuario,
        ':comentario' => 'Registro inicial desde prueba de integración'
    ]);

    $conexion->commit();

    echo "OK: Envío insertado con ID $idEnvio\n";
    echo "TEST INSERT ENVIO OK\n";
    exit(0);

} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
