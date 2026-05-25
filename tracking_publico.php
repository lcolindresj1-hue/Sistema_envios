<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tracking Público - Sistema de Envíos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<div class="public-wrapper">
    <div class="public-card">
        <h2 class="public-title"><i class="bi bi-truck"></i> Tracking de Envíos</h2>
        <p class="text-center text-muted mb-4">Consulte el avance de su paquete usando el código de guía.</p>
        <form id="formTracking" class="row g-3 align-items-end mb-3">
            <div class="col-md-9"><label class="form-label">Código de guía</label><input type="text" id="codigo_guia" class="form-control" placeholder="Ejemplo: ENV-20260518-ABC123" required></div>
            <div class="col-md-3"><button class="btn btn-bi w-100" type="submit"><i class="bi bi-search"></i> Consultar</button></div>
        </form>
        <div id="mensaje"></div>
        <div id="resultado" style="display:none;">
            <hr>
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="info-box"><small>Código</small><strong id="codigo"></strong></div></div>
                <div class="col-md-3"><div class="info-box"><small>Destinatario</small><strong id="destinatario"></strong></div></div>
                <div class="col-md-3"><div class="info-box"><small>Fecha</small><strong id="fecha"></strong></div></div>
                <div class="col-md-3"><div class="info-box"><small>Estado actual</small><strong id="estado"></strong></div></div>
                <div class="col-12"><div class="info-box"><small>Descripción</small><strong id="descripcion"></strong></div></div>
            </div>
            <div class="progress mb-4"><div id="barra_progreso" class="progress-bar" style="width:0%">0%</div></div>
            <h5 class="card-title">Historial del envío</h5>
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Fecha</th><th>Estado</th><th>Comentario</th></tr></thead><tbody id="historial"></tbody></table></div>
        </div>
        <div class="text-center mt-4"><a href="login.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Volver</a></div>
    </div>
</div>
<script src="js/tracking.js"></script>
</body>
</html>
