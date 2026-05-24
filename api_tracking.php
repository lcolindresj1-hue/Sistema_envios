<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'config/conexion.php';

try {
    $codigo = trim($_GET['codigo'] ?? '');

    if ($codigo === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Debe ingresar un código de guía.'
        ]);
        exit;
    }

    $sql = "
        SELECT
            e.id_envio,
            e.codigo_guia,
            e.nombre_remitente,
            e.telefono_remitente,
            e.direccion_remitente,
            e.nombre_destinatario,
            e.telefono_destinatario,
            e.direccion_destinatario,
            e.descripcion_paquete,
            e.peso,
            e.es_fragil,
            e.tipo_paquete,
            e.instrucciones_entrega,
            e.fecha_registro,
            es.nombre_estado,
            es.orden_estado AS orden,
            es.es_final
        FROM envio e
        INNER JOIN estado es ON e.estado_actual_id = es.id_estado
        WHERE e.codigo_guia = :codigo
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(':codigo', $codigo);
    $stmt->execute();

    $envio = $stmt->fetch();

    if (!$envio) {
        echo json_encode([
            'success' => false,
            'message' => 'Código de guía no encontrado.'
        ]);
        exit;
    }

    $sqlHist = "
        SELECT
            h.fecha_hora,
            es.nombre_estado,
            es.descripcion,
            es.orden_estado AS orden,
            h.comentario
        FROM historia_estado h
        INNER JOIN estado es ON h.id_estado = es.id_estado
        WHERE h.id_envio = :id
        ORDER BY h.fecha_hora ASC, h.id_historial ASC
    ";

    $stmtHist = $conexion->prepare($sqlHist);
    $stmtHist->bindValue(':id', $envio['id_envio'], PDO::PARAM_INT);
    $stmtHist->execute();

    $historial = $stmtHist->fetchAll();

    echo json_encode([
        'success' => true,
        'envio' => $envio,
        'historial' => $historial
    ]);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Error interno al consultar el tracking.',
        'debug' => $e->getMessage()
    ]);
}
