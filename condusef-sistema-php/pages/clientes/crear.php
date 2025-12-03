<?php
/**
 * CONDUSEF - Crear Cliente
 */

$pageTitle = 'Nuevo Cliente';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus"></i> Nuevo Cliente</h2>
    <a href="lista.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div id="alertContainer"></div>

        <form id="formCliente">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">

            <h5 class="mb-3">Datos Personales</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telefono_alternativo" class="form-label">Teléfono Alternativo</label>
                    <input type="tel" class="form-control" id="telefono_alternativo" name="telefono_alternativo">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="curp" class="form-label">CURP</label>
                    <input type="text" class="form-control" id="curp" name="curp" maxlength="18" pattern="[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9]{2}">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="rfc" class="form-label">RFC</label>
                    <input type="text" class="form-control" id="rfc" name="rfc" maxlength="13">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                </div>
            </div>

            <h5 class="mb-3 mt-4">Domicilio</h5>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="domicilio_calle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="domicilio_calle" name="domicilio_calle">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="domicilio_numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="domicilio_numero" name="domicilio_numero">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="domicilio_colonia" class="form-label">Colonia</label>
                    <input type="text" class="form-control" id="domicilio_colonia" name="domicilio_colonia">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="domicilio_ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="domicilio_ciudad" name="domicilio_ciudad">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="domicilio_estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="domicilio_estado" name="domicilio_estado">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="domicilio_cp" class="form-label">Código Postal</label>
                    <input type="text" class="form-control" id="domicilio_cp" name="domicilio_cp" maxlength="5" pattern="[0-9]{5}">
                </div>
            </div>

            <h5 class="mb-3 mt-4">Información Adicional</h5>
            <div class="row">
                <div class="col-12 mb-3">
                    <label for="notas" class="form-label">Notas</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="lista.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="bi bi-save"></i> Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formCliente').submit(function(e) {
        e.preventDefault();

        const btnGuardar = $('#btnGuardar');
        const originalText = btnGuardar.html();

        btnGuardar.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/api/clientes/crear.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = 'lista.php';
                    }, 1500);
                } else {
                    showAlert(response.message, 'danger');
                    btnGuardar.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                showAlert('Error al guardar el cliente', 'danger');
                btnGuardar.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
