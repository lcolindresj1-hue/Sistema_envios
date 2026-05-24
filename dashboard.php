<?php
require_once 'includes/funciones.php';
requiereLogin();
require_once 'config/conexion.php';

$rol = $_SESSION['rol'] ?? '';
$puedeVerResumen = in_array($rol, ['Administrador', 'Operador']);

$totalEnvios = 0;
$totalUsuarios = 0;
$totalEntregados = 0;
$totalEnRuta = 0;

if ($puedeVerResumen) {
    $totalEnvios = (int)$conexion
        ->query("SELECT COUNT(*) FROM envio")
        ->fetchColumn();

    $totalUsuarios = (int)$conexion
        ->query("SELECT COUNT(*) FROM usuario")
        ->fetchColumn();

    $totalEntregados = (int)$conexion
        ->query("
            SELECT COUNT(*)
            FROM envio e
            INNER JOIN estado es ON e.estado_actual_id = es.id_estado
            WHERE es.nombre_estado IN ('Entregado a usuario', 'Entregado en sede')
        ")
        ->fetchColumn();

    $totalEnRuta = (int)$conexion
        ->query("
            SELECT COUNT(*)
            FROM envio e
            INNER JOIN estado es ON e.estado_actual_id = es.id_estado
            WHERE es.nombre_estado = 'En ruta'
        ")
        ->fetchColumn();
}

$tituloPagina = 'Panel principal';
$subtituloPagina = 'Resumen general del sistema de gestión de envíos';
$paginaActiva = 'dashboard';

include 'includes/header.php';
?>

<?php if ($puedeVerResumen): ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <i class="bi bi-box"></i>
            <h4><?= $totalEnvios; ?></h4>
            <span>Envíos registrados</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="bi bi-truck"></i>
            <h4><?= $totalEnRuta; ?></h4>
            <span>En ruta</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="bi bi-check-circle"></i>
            <h4><?= $totalEntregados; ?></h4>
            <span>Envíos entregados</span>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="bi bi-people"></i>
            <h4><?= $totalUsuarios; ?></h4>
            <span>Usuarios del sistema</span>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="app-card">
    <h4 class="card-title">Accesos rápidos</h4>

    <div class="row g-4">
        <div class="col-md-3">
            <a class="quick-card" href="crear_envio.php">
                <i class="bi bi-plus-circle"></i>
                <strong>Crear envío</strong>
                <span>Registrar una nueva solicitud.</span>
            </a>
        </div>

        <div class="col-md-3">
            <a class="quick-card" href="historial_envios.php">
                <i class="bi bi-clock-history"></i>
                <strong>Historial</strong>
                <span>Consultar envíos registrados.</span>
            </a>
        </div>

        <div class="col-md-3">
            <a class="quick-card" href="tracking_publico.php">
                <i class="bi bi-search"></i>
                <strong>Tracking</strong>
                <span>Buscar por código guía.</span>
            </a>
        </div>

        <div class="col-md-3">
            <a class="quick-card" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <strong>Salir</strong>
                <span>Cerrar sesión actual.</span>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>