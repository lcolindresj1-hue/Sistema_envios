<?php
require_once 'includes/funciones.php';
requiereLogin();
require_once 'config/conexion.php';
$idUsuario = $_SESSION['id_usuario'];
$rol = $_SESSION['rol'] ?? '';
$puedeActualizarEstado = in_array($rol, ['Administrador', 'Operador']);
$sql = "SELECT e.id_envio,e.codigo_guia,e.nombre_remitente,e.nombre_destinatario,e.fecha_registro,es.nombre_estado
        FROM envio e
        INNER JOIN estado es ON e.estado_actual_id = es.id_estado
        WHERE e.id_usuario_remitente = :usuario OR :rol IN ('Administrador','Operador')
        ORDER BY e.fecha_registro DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute([':usuario'=>$idUsuario, ':rol'=>$_SESSION['rol']]);
$envios = $stmt->fetchAll();
$tituloPagina = 'Historial de envíos';
$subtituloPagina = 'Consulta de solicitudes registradas y su estado actual';
$paginaActiva = 'historial';
include 'includes/header.php';
?>
<div class="app-card table-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="card-title mb-0">Listado de envíos</h4>
        <a href="crear_envio.php" class="btn btn-bi"><i class="bi bi-plus-circle"></i> Nuevo envío</a>
    </div>
    <?php if (count($envios) > 0): ?>
    <table class="table table-hover align-middle">
        <thead><tr><th>Código guía</th><th>Remitente</th><th>Destinatario</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($envios as $envio): ?>
            <tr>
                <td><strong><?php echo e($envio['codigo_guia']); ?></strong></td>
                <td><?php echo e($envio['nombre_remitente']); ?></td>
                <td><?php echo e($envio['nombre_destinatario']); ?></td>
                <td><?php echo e($envio['fecha_registro']); ?></td>
                <td><span class="badge-status"><?php echo e($envio['nombre_estado']); ?></span></td>
                <td>
    <a class="btn btn-sm btn-blue" href="detalle_envio.php?id=<?php echo e($envio['id_envio']); ?>">
        <i class="bi bi-eye"></i> Ver detalle
    </a>

    <?php if ($puedeActualizarEstado): ?>
        <a class="btn btn-sm btn-warning"
           href="actualizar_estado.php?id=<?php echo e($envio['id_envio']); ?>">
            <i class="bi bi-arrow-repeat"></i> Actualizar estado
        </a>
    <?php endif; ?>
</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?><div class="alert alert-info">No hay envíos registrados.</div><?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
