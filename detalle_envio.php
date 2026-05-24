<?php
require_once 'includes/funciones.php';
requiereLogin();
require_once 'config/conexion.php';

$idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
$rol = $_SESSION['rol'] ?? '';
$puedeActualizarEstado = in_array($rol, ['Administrador', 'Operador'], true);

$idEnvio = intval($_GET['id'] ?? 0);

if ($idEnvio <= 0) {
    die('Envío no especificado.');
}

$sqlEnvio = "SELECT 
                e.*,
                es.nombre_estado,
                u.nombres AS usuario_creador_nombres,
                u.apellidos AS usuario_creador_apellidos
            FROM envio e
            INNER JOIN estado es 
                ON e.estado_actual_id = es.id_estado
            INNER JOIN usuario u
                ON e.id_usuario_remitente = u.id_usuario
            WHERE e.id_envio = :id
              AND (
                    e.id_usuario_remitente = :usuario
                    OR :rol IN ('Administrador', 'Operador')
              )";

$stmt = $conexion->prepare($sqlEnvio);
$stmt->bindValue(':id', $idEnvio, PDO::PARAM_INT);
$stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
$stmt->bindValue(':rol', $rol);
$stmt->execute();

$envio = $stmt->fetch();

if (!$envio) {
    die('El envío no existe o no tiene permisos para consultarlo.');
}

$nombreRemitente = $envio['nombre_remitente'] ?: trim($envio['usuario_creador_nombres'] . ' ' . $envio['usuario_creador_apellidos']);

$sqlHist = "SELECT h.fecha_hora, es.nombre_estado, h.comentario, u.nombres, u.apellidos
            FROM historia_estado h
            INNER JOIN estado es ON h.id_estado = es.id_estado
            INNER JOIN usuario u ON h.id_usuario = u.id_usuario
            WHERE h.id_envio = :id
            ORDER BY h.fecha_hora ASC";

$stmtHist = $conexion->prepare($sqlHist);
$stmtHist->bindValue(':id', $idEnvio, PDO::PARAM_INT);
$stmtHist->execute();

$historial = $stmtHist->fetchAll();

$tituloPagina = 'Detalle del envío';
$subtituloPagina = 'Información general y seguimiento del paquete';
$paginaActiva = 'historial';

include 'includes/header.php';
?>

<div class="app-card">
    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <h4 class="card-title mb-0">Información del envío</h4>
        <span class="badge-status"><?php echo e($envio['nombre_estado']); ?></span>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="info-box">
                <small>Código guía</small>
                <strong><?php echo e($envio['codigo_guia']); ?></strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <small>Remitente</small>
                <strong><?php echo e($nombreRemitente); ?></strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <small>Teléfono remitente</small>
                <strong><?php echo e($envio['telefono_remitente'] ?: 'No registrado'); ?></strong>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <small>Dirección remitente</small>
                <strong><?php echo e($envio['direccion_remitente'] ?: 'No registrada'); ?></strong>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <small>Destinatario</small>
                <strong><?php echo e($envio['nombre_destinatario']); ?></strong>
            </div>
        </div>

        <div class="col-md-4">
            <div class="info-box">
                <small>Teléfono destinatario</small>
                <strong><?php echo e($envio['telefono_destinatario']); ?></strong>
            </div>
        </div>

        <div class="col-md-8">
            <div class="info-box">
                <small>Dirección destinatario</small>
                <strong><?php echo e($envio['direccion_destinatario']); ?></strong>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <small>Fecha registro</small>
                <strong><?php echo e($envio['fecha_registro']); ?></strong>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <small>Descripción</small>
                <strong><?php echo e($envio['descripcion_paquete']); ?></strong>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <small>Observaciones</small>
                <strong><?php echo e($envio['observaciones'] ?: 'Sin observaciones'); ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="app-card table-card">
    <h4 class="card-title">Historial de estados</h4>

    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Comentario</th>
                <th>Usuario</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($historial as $item): ?>
                <tr>
                    <td><?php echo e($item['fecha_hora']); ?></td>
                    <td><span class="badge-status"><?php echo e($item['nombre_estado']); ?></span></td>
                    <td><?php echo e($item['comentario']); ?></td>
                    <td><?php echo e($item['nombres'] . ' ' . $item['apellidos']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4 d-flex gap-2 flex-wrap">
        <a class="btn btn-secondary" href="historial_envios.php">Volver al historial</a>

        <?php if ($puedeActualizarEstado): ?>
            <a class="btn btn-bi" href="actualizar_estado.php?id=<?php echo e($envio['id_envio']); ?>">
                <i class="bi bi-arrow-repeat"></i> Actualizar estado
            </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
