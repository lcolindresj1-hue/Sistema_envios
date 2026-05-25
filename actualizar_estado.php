<?php
require_once 'includes/funciones.php';
requiereLogin();

$rol = $_SESSION['rol'] ?? '';
$idRol = (int)($_SESSION['id_rol'] ?? 0);

if ($idRol === 3 || !in_array($rol, ['Administrador', 'Operador'], true)) {
    header('Location: historial_envios.php');
    exit;
}

require_once 'config/conexion.php';

$idEnvio = intval($_GET['id'] ?? 0);

if ($idEnvio <= 0) {
    die('Envío inválido.');
}

$stmtEnvio = $conexion->prepare("
    SELECT 
        e.*,
        es.nombre_estado,
        es.orden_estado AS orden_actual,
        es.es_final
    FROM envio e
    INNER JOIN estado es 
        ON e.estado_actual_id = es.id_estado
    WHERE e.id_envio = :id
");

$stmtEnvio->bindValue(':id', $idEnvio, PDO::PARAM_INT);
$stmtEnvio->execute();

$envio = $stmtEnvio->fetch();

if (!$envio) {
    die('El envío no existe.');
}

$estadosStmt = $conexion->prepare("
    SELECT *
    FROM estado
    WHERE orden_estado >= :orden_actual
    ORDER BY orden_estado ASC, id_estado ASC
");

$estadosStmt->execute([
    ':orden_actual' => (int)$envio['orden_actual']
]);

$estados = $estadosStmt->fetchAll();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idEstado = intval($_POST['id_estado'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($idEstado <= 0) {

        $error = 'Seleccione un estado válido.';

    } else {

        try {

            $stmtNuevo = $conexion->prepare("
                SELECT 
                    id_estado,
                    nombre_estado,
                    orden_estado,
                    es_final
                FROM estado
                WHERE id_estado = :id
                LIMIT 1
            ");

            $stmtNuevo->execute([
                ':id' => $idEstado
            ]);

            $estadoNuevo = $stmtNuevo->fetch();

            if (!$estadoNuevo) {
                throw new Exception('El nuevo estado no existe.');
            }

            if ((int)$envio['es_final'] === 1) {
                throw new Exception('Este envío ya está en un estado final y no puede modificarse.');
            }

            if ((int)$estadoNuevo['orden_estado'] < (int)$envio['orden_actual']) {
                throw new Exception('No se permite regresar a una etapa anterior del tracking.');
            }

            $conexion->beginTransaction();

            $stmt = $conexion->prepare("
                UPDATE envio
                SET estado_actual_id = :estado
                WHERE id_envio = :envio
            ");

            $stmt->execute([
                ':estado' => $idEstado,
                ':envio' => $idEnvio
            ]);

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
                ':comentario' => $comentario !== ''
                    ? $comentario
                    : 'Estado actualizado'
            ]);

            $conexion->commit();

            header('Location: detalle_envio.php?id=' . $idEnvio);
            exit;

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            $error = 'Error al actualizar el estado: ' . $e->getMessage();
        }
    }
}

$tituloPagina = 'Actualizar estado';
$subtituloPagina = 'Cambio de estado y registro en historial del envío';
$paginaActiva = 'historial';

include 'includes/header.php';
?>

<div class="card shadow-sm">
    <div class="card-body">

        <h4 class="mb-3">Actualizar estado del envío</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <strong>Código guía:</strong>
            <?= e($envio['codigo_guia']) ?>
            <br>

            <strong>Estado actual:</strong>
            <?= e($envio['nombre_estado']) ?>
        </div>

        <?php if ((int)$envio['es_final'] === 1): ?>

            <div class="alert alert-warning">
                Este envío ya está finalizado.
                No permite cambios de estado.
            </div>

            <a href="detalle_envio.php?id=<?= (int)$idEnvio ?>"
               class="btn btn-outline-secondary">
                Volver
            </a>

        <?php else: ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">
                        Nuevo estado
                    </label>

                    <select name="id_estado"
                            class="form-select"
                            required>

                        <option value="">
                            Seleccione
                        </option>

                        <?php foreach ($estados as $estado): ?>

                            <option value="<?= (int)$estado['id_estado'] ?>">
                                <?= e($estado['nombre_estado']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <small class="text-muted">
                        Solo se muestran estados iguales o posteriores.
                    </small>
                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Comentario
                    </label>

                    <textarea
                        name="comentario"
                        class="form-control"
                        rows="3"
                        placeholder="Ej. Paquete recibido en oficina central"
                    ></textarea>

                </div>

                <button type="submit"
                        class="btn btn-primary">

                    Guardar cambio

                </button>

                <a href="detalle_envio.php?id=<?= (int)$idEnvio ?>"
                   class="btn btn-outline-secondary">

                    Cancelar

                </a>

            </form>

        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>