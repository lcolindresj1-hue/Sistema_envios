<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema de Envíos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-bi-body">
<div class="login-wrapper">
    <section class="login-brand">
        <div class="icon"><i class="bi bi-truck"></i></div>
        <h1>Sistema de Envíos</h1>
        <p>Gestión de paquetería, solicitudes y tracking en tiempo real desde.</p>
    </section>
    <section class="login-card">
        <h3>Iniciar sesión</h3>
        <p>Ingrese sus credenciales para acceder al sistema.</p>
        <div id="mensaje"></div>
        <form id="formLogin">
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="correo" class="form-control" placeholder="usuario@correo.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
            </div>
            <button type="submit" class="btn btn-bank w-100">Iniciar sesión</button>
        </form>
        <div class="tracking-link">
            <a href="registro_cliente.php"><i class="bi bi-person-plus"></i> Crear usuario cliente</a>
        </div>

        <div class="tracking-link mt-2">
            <a href="tracking_publico.php"><i class="bi bi-search"></i> Consultar tracking público</a>
        </div>
    </section>
</div>
<script src="js/login.js"></script>
</body>
</html>
