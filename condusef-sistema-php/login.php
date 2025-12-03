<?php
/**
 * CONDUSEF - Página de Login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';

session_start();

// Si ya está autenticado, redirigir al dashboard
if (validateSession()) {
    redirect(BASE_URL . '/index.php');
}

// Generar token CSRF
$csrfToken = generateCSRFToken();

setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0284c7 0%, #0c4a6e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
        }

        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .login-header {
            background-color: #0284c7;
            color: white;
            padding: 2rem;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-body {
            padding: 2rem;
        }

        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.25);
        }

        .btn-login {
            background-color: #0284c7;
            border-color: #0284c7;
            padding: 0.75rem;
            font-weight: 600;
        }

        .btn-login:hover {
            background-color: #0369a1;
            border-color: #0369a1;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock"></i>
                <h3 class="mb-0">CONDUSEF</h3>
                <p class="mb-0 small">Sistema de Gestión de Casos</p>
            </div>
            <div class="login-body">
                <div id="alertContainer"></div>

                <form id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Recordar sesión
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100" id="btnLogin">
                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Usuario por defecto: admin@condusef.com / admin123
                    </small>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-white">
                &copy; <?php echo date('Y'); ?> CONDUSEF - Maldonado y Asociados
            </small>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').click(function() {
                const passwordField = $('#password');
                const icon = $(this).find('i');

                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Login form submit
            $('#loginForm').submit(function(e) {
                e.preventDefault();

                const btnLogin = $('#btnLogin');
                const originalText = btnLogin.html();

                // Deshabilitar botón y mostrar loading
                btnLogin.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Iniciando sesión...');

                // Limpiar alertas previas
                $('#alertContainer').empty();

                $.ajax({
                    url: 'api/auth/login.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            $('#alertContainer').html(
                                '<div class="alert alert-success">' +
                                '<i class="bi bi-check-circle"></i> ' + response.message +
                                '</div>'
                            );

                            // Redirigir al dashboard
                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 1000);
                        } else {
                            // Mostrar error
                            $('#alertContainer').html(
                                '<div class="alert alert-danger">' +
                                '<i class="bi bi-exclamation-triangle"></i> ' + response.message +
                                '</div>'
                            );

                            btnLogin.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        $('#alertContainer').html(
                            '<div class="alert alert-danger">' +
                            '<i class="bi bi-exclamation-triangle"></i> Error de conexión. Inténtalo de nuevo.' +
                            '</div>'
                        );

                        btnLogin.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
</body>
</html>
