<?php
session_start();
require_once 'config/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($correo === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos.']);
    exit;
}

$sql = "SELECT u.*, r.nombre AS rol
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.correo = :correo AND u.estado = 'activo'
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bindValue(':correo', $correo);
$stmt->execute();

$usuario = $stmt->fetch();

$acceso = false;

if ($usuario) {
    $hash = $usuario['password_hash'];
    $acceso = password_verify($password, $hash) || $password === $hash;
}

if ($acceso) {
    $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
    $_SESSION['id_rol'] = (int)$usuario['id_rol'];
    $_SESSION['nombre'] = $usuario['nombres'];
    $_SESSION['nombres'] = $usuario['nombres'];
    $_SESSION['apellidos'] = $usuario['apellidos'];
    $_SESSION['telefono'] = $usuario['telefono'];
    $_SESSION['direccion'] = $usuario['direccion'];
    $_SESSION['rol'] = $usuario['rol'];

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
}
