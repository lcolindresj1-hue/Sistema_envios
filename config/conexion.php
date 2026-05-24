<?php
$host = "mysql-369bddc3-miumg-2780.f.aivencloud.com";
$puerto = "12292";
$bd = "defaultdb";
$usuario = "avnadmin";
$password = "AVNS_3-Rz_QGSMx37KojB1YO";

try {
    $conexion = new PDO(
        "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8mb4",
        $usuario,
        $password
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
