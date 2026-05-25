<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/funciones.php';
$tituloPagina = $tituloPagina ?? 'Sistema de Envíos';
$subtituloPagina = $subtituloPagina ?? 'Gestión de paquetería y tracking en tiempo real';
$paginaActiva = $paginaActiva ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($tituloPagina); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<div class="app-shell">
    <aside class="app-sidebar">
        <div class="brand-box">
            <div class="brand-icon"><i class="bi bi-box-seam"></i></div>
            <div>
                <h5>EnvíosGT</h5>
                <span>Tracking System</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <a class="<?php echo $paginaActiva === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php"><i class="bi bi-speedometer2"></i> Inicio</a>
            <a class="<?php echo $paginaActiva === 'crear' ? 'active' : ''; ?>" href="crear_envio.php"><i class="bi bi-plus-circle"></i> Crear envío</a>
            <a class="<?php echo $paginaActiva === 'historial' ? 'active' : ''; ?>" href="historial_envios.php"><i class="bi bi-clock-history"></i> Historial</a>
            <a class="<?php echo $paginaActiva === 'tracking' ? 'active' : ''; ?>" href="tracking_publico.php"><i class="bi bi-search"></i> Tracking</a>
            <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <main class="app-main">
        <header class="topbar">
            <div>
                <h1><?php echo e($tituloPagina); ?></h1>
                <p><?php echo e($subtituloPagina); ?></p>
            </div>
            <div class="user-pill">
                <i class="bi bi-person-circle"></i>
                <span><?php echo e($_SESSION['nombre'] ?? 'Usuario'); ?></span>
            </div>
        </header>
