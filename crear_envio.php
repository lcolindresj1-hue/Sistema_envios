<?php
require_once 'includes/funciones.php';
requiereLogin();

require_once 'config/conexion.php';

$mensaje = '';
$error = '';

$rol = $_SESSION['rol'] ?? '';
$idRol = (int)($_SESSION['id_rol'] ?? 0);
$esCliente = $idRol === 3;
$puedeConsignarRemitente = in_array($rol, ['Administrador', 'Operador'], true);

$remitenteSesion = trim(($_SESSION['nombres'] ?? $_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellidos'] ?? ''));
$telefonoSesion = $_SESSION['telefono'] ?? '';
$direccionSesion = $_SESSION['direccion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreRemitente = trim($_POST['nombre_remitente'] ?? '');
    $telefonoRemitente = trim($_POST['telefono_remitente'] ?? '');
    $direccionRemitente = trim($_POST['direccion_remitente'] ?? '');

    $nombre = trim($_POST['nombre_destinatario'] ?? '');
    $telefono = trim($_POST['telefono_destinatario'] ?? '');
    $direccion = trim($_POST['direccion_destinatario'] ?? '');
    $descripcion = trim($_POST['descripcion_paquete'] ?? '');
    $peso = trim($_POST['peso'] ?? '');
    $tipoPaquete = trim($_POST['tipo_paquete'] ?? '');
    $esFragil = isset($_POST['es_fragil']) ? 1 : 0;
    $observaciones = trim($_POST['observaciones'] ?? '');
    $instrucciones = trim($_POST['instrucciones_entrega'] ?? '');

    if ($esCliente) {
        $nombreRemitente = $remitenteSesion;
        $telefonoRemitente = $telefonoSesion;
        $direccionRemitente = $direccionSesion;
    }

    if (!$esCliente && $nombreRemitente === '') {
        $error = 'Ingrese el nombre del remitente.';
    } elseif ($nombre === '' || $telefono === '' || $direccion === '' || $descripcion === '' || $peso === '') {
        $error = 'Complete los campos obligatorios.';
    } elseif (!is_numeric($peso) || (float)$peso <= 0) {
        $error = 'El peso debe ser un número mayor a cero.';
    } else {
        try {
            $conexion->beginTransaction();

            $codigo = generarCodigoGuia();

            $stmtEstado = $conexion->prepare("
                SELECT id_estado
                FROM estado
                WHERE nombre_estado = 'Paquete registrado'
                LIMIT 1
            ");
            $stmtEstado->execute();
            $estado = $stmtEstado->fetch();

            if (!$estado) {
                throw new Exception("No existe el estado inicial 'Paquete registrado'.");
            }

            $idEstado = (int)$estado['id_estado'];

            $sql = "
                INSERT INTO envio (
                    codigo_guia,
                    id_usuario_remitente,
                    nombre_remitente,
                    telefono_remitente,
                    direccion_remitente,
                    nombre_destinatario,
                    telefono_destinatario,
                    direccion_destinatario,
                    descripcion_paquete,
                    peso,
                    es_fragil,
                    tipo_paquete,
                    estado_actual_id,
                    observaciones,
                    instrucciones_entrega
                )
                VALUES (
                    :codigo,
                    :usuario,
                    :nombre_remitente,
                    :telefono_remitente,
                    :direccion_remitente,
                    :nombre,
                    :telefono,
                    :direccion,
                    :descripcion,
                    :peso,
                    :es_fragil,
                    :tipo_paquete,
                    :estado,
                    :observaciones,
                    :instrucciones
                )
            ";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':usuario' => $_SESSION['id_usuario'],
                ':nombre_remitente' => $nombreRemitente,
                ':telefono_remitente' => $telefonoRemitente,
                ':direccion_remitente' => $direccionRemitente,
                ':nombre' => $nombre,
                ':telefono' => $telefono,
                ':direccion' => $direccion,
                ':descripcion' => $descripcion,
                ':peso' => (float)$peso,
                ':es_fragil' => $esFragil,
                ':tipo_paquete' => $tipoPaquete,
                ':estado' => $idEstado,
                ':observaciones' => $observaciones,
                ':instrucciones' => $instrucciones
            ]);

            $idEnvio = $conexion->lastInsertId();

            $stmtHist = $conexion->prepare("
                INSERT INTO historia_estado (
                    id_envio,
                    id_estado,
                    id_usuario,
                    comentario
                )
                VALUES (
                    :envio,
                    :estado,
                    :usuario,
                    :comentario
                )
            ");
            $stmtHist->execute([
                ':envio' => $idEnvio,
                ':estado' => $idEstado,
                ':usuario' => $_SESSION['id_usuario'],
                ':comentario' => 'Envío registrado en el sistema'
            ]);

            $conexion->commit();

            $mensaje = 'Envío creado correctamente. Código guía: ' . $codigo;

        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = 'Error al crear el envío: ' . $e->getMessage();
        }
    }
}

$tituloPagina = 'Crear solicitud de envío';
$subtituloPagina = 'Registre una nueva encomienda dentro del sistema';
$paginaActiva = 'crear';

include 'includes/header.php';
?>

<div class="card shadow-sm">
    <div class="card-body">
        <h4 class="mb-3">Datos del remitente</h4>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= e($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <?php if ($esCliente): ?>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del remitente</label>
                        <input type="text" class="form-control" value="<?= e($remitenteSesion) ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono del remitente</label>
                        <input type="text" class="form-control" value="<?= e($telefonoSesion ?: 'No registrado') ?>" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dirección del remitente</label>
                        <input type="text" class="form-control" value="<?= e($direccionSesion ?: 'No registrada') ?>" readonly>
                    </div>
                </div>
            <?php else: ?>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del remitente *</label>
                        <input type="text" name="nombre_remitente" class="form-control" placeholder="Ej. Juan Pérez" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono del remitente</label>
                        <input type="text" name="telefono_remitente" class="form-control" placeholder="Ej. 55555555">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Dirección del remitente</label>
                        <input type="text" name="direccion_remitente" class="form-control" placeholder="Ej. Ciudad de Guatemala">
                    </div>
                </div>
            <?php endif; ?>

            <h4 class="mb-3">Datos del envío</h4>

            <div class="mb-3">
                <label class="form-label">Nombre del destinatario *</label>
                <input type="text" name="nombre_destinatario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Teléfono del destinatario *</label>
                <input type="text" name="telefono_destinatario" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Dirección del destinatario *</label>
                <textarea name="direccion_destinatario" class="form-control" rows="2" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción del paquete *</label>
                <input type="text" name="descripcion_paquete" class="form-control" placeholder="Ej. Caja pequeña, documentos, repuestos" required>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Peso del paquete en kg *</label>
                    <input type="number" name="peso" class="form-control" min="0.01" step="0.01" placeholder="Ej. 2.50" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo de paquete</label>
                    <select name="tipo_paquete" class="form-select">
                        <option value="">Seleccione</option>
                        <option value="Caja">Caja</option>
                        <option value="Sobre">Sobre</option>
                        <option value="Documento">Documento</option>
                        <option value="Bolsa">Bolsa</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="es_fragil" value="1" id="es_fragil">
                        <label class="form-check-label" for="es_fragil">Paquete frágil</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2" placeholder="Ej. Caja sellada, revisar identificación"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Instrucciones de entrega</label>
                <textarea name="instrucciones_entrega" class="form-control" rows="2" placeholder="Ej. Entregar de 8:00 a 16:00"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Crear solicitud</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
