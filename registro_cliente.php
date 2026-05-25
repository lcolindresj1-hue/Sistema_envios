<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'includes/funciones.php';
require_once 'config/conexion.php';

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmarPassword = trim($_POST['confirmar_password'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombres === '' || $apellidos === '' || $correo === '' || $password === '' || $confirmarPassword === '') {
        $error = 'Complete los campos obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingrese un correo electrónico válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener como mínimo 6 caracteres.';
    } elseif ($password !== $confirmarPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $stmtExiste = $conexion->prepare("SELECT id_usuario FROM usuario WHERE correo = :correo LIMIT 1");
            $stmtExiste->execute([':correo' => $correo]);

            if ($stmtExiste->fetch()) {
                $error = 'Ya existe un usuario registrado con ese correo.';
            } else {
                $stmtRol = $conexion->prepare("SELECT id_rol FROM rol WHERE id_rol = 3 AND nombre = 'Cliente' LIMIT 1");
                $stmtRol->execute();
                $rolCliente = $stmtRol->fetch();

                if (!$rolCliente) {
                    throw new Exception("No existe el rol Cliente con id_rol = 3. Revise la tabla rol.");
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conexion->prepare("\n                    INSERT INTO usuario (\n                        id_rol,\n                        nombres,\n                        apellidos,\n                        correo,\n                        password_hash,\n                        telefono,\n                        direccion,\n                        estado\n                    )\n                    VALUES (\n                        3,\n                        :nombres,\n                        :apellidos,\n                        :correo,\n                        :password_hash,\n                        :telefono,\n                        :direccion,\n                        'activo'\n                    )\n                ");

                $stmt->execute([
                    ':nombres' => $nombres,
                    ':apellidos' => $apellidos,
                    ':correo' => $correo,
                    ':password_hash' => $hash,
                    ':telefono' => $telefono,
                    ':direccion' => $direccion
                ]);

                $mensaje = 'Usuario cliente creado correctamente. Ya puede iniciar sesión.';
            }
        } catch (Exception $e) {
            $error = 'Error al crear el usuario: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear usuario cliente - Sistema de Envíos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-bi-body">
<div class="login-wrapper register-wrapper">
    <section class="login-brand">
        <div class="icon"><i class="bi bi-person-plus"></i></div>
        <h1>Crear usuario cliente</h1>
        <p>Registre su cuenta para crear solicitudes de envío y consultar el seguimiento de sus paquetes.</p>
    </section>

    <section class="login-card">
        <h3>Registro de cliente</h3>
        <p>Complete sus datos para crear una cuenta de cliente.</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= e($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombres *</label>
                    <input type="text" name="nombres" class="form-control" value="<?= e($_POST['nombres'] ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Apellidos *</label>
                    <input type="text" name="apellidos" class="form-control" value="<?= e($_POST['apellidos'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Correo electrónico *</label>
                <input type="email" name="correo" class="form-control" value="<?= e($_POST['correo'] ?? '') ?>" placeholder="cliente@correo.com" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmar contraseña *</label>
                    <input type="password" name="confirmar_password" class="form-control" minlength="6" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= e($_POST['telefono'] ?? '') ?>" placeholder="Ej. 55555555">
            </div>

            <div class="mb-3">
                <label class="form-label">Dirección</label>
                <textarea name="direccion" class="form-control" rows="2" placeholder="Ej. Ciudad de Guatemala"><?= e($_POST['direccion'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-bank w-100">
                <i class="bi bi-person-plus"></i> Crear cuenta
            </button>
        </form>

        <div class="tracking-link">
            <a href="login.php"><i class="bi bi-arrow-left"></i> Volver al login</a>
        </div>
    </section>
</div>
</body>
</html>
