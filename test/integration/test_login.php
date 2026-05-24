<?php

require_once __DIR__ . '/../../config/conexion.php';

echo "====================================\n";
echo "TEST LOGIN ADMIN\n";
echo "====================================\n";

$correo = "admin@sistema.com";
$password = "123456";

try {
    echo "OK: Conexion MySQL exitosa\n";

    $sql = "
        SELECT 
            u.*,
            r.nombre AS nombre_rol
        FROM usuario u
        LEFT JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.correo = :correo
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':correo' => $correo
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "ERROR: Usuario admin no encontrado\n";
        exit(1);
    }

    echo "OK: Usuario admin encontrado\n";

    if (!isset($usuario['id_rol'])) {
        echo "ERROR: El usuario no tiene id_rol\n";
        exit(1);
    }

    if (!in_array($usuario['nombre_rol'], ['Administrador', 'Operador'], true)) {
        echo "ERROR: El usuario admin no tiene rol Administrador/Operador\n";
        exit(1);
    }

    $hash = $usuario['password_hash'] ?? '';

    $passwordCorrecto = false;

    if (password_get_info($hash)['algo'] !== 0) {
        $passwordCorrecto = password_verify($password, $hash);
    } else {
        $passwordCorrecto = hash_equals($hash, $password);
    }

    if (!$passwordCorrecto) {
        echo "ERROR: Password incorrecto\n";
        exit(1);
    }

    echo "OK: Credenciales correctas\n";
    echo "TEST LOGIN EXITOSO\n";
    exit(0);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
