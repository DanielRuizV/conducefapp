            </div> <!-- End main-content -->
        </div> <!-- End content -->
    </div> <!-- End wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar, #content').toggleClass('active');
            });

            // Auto-hide alerts after 5 seconds
            $('.alert:not(.alert-permanent)').delay(5000).slideUp(300);

            // Confirmar antes de eliminar
            $('.btn-delete, .delete-btn').on('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                    return false;
                }
            });

            // CSRF Token para todas las peticiones AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>'
                }
            });
        });

        /**
         * Función helper para mostrar alertas
         */
        function showAlert(message, type = 'info', container = '#alertContainer') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${getAlertIcon(type)}"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $(container).html(alertHtml);

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $(container + ' .alert').slideUp(300);
            }, 5000);
        }

        /**
         * Obtiene el icono apropiado para el tipo de alerta
         */
        function getAlertIcon(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'warning': 'exclamation-circle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }

        /**
         * Formatea fecha a formato legible
         */
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        /**
         * Formatea cantidad monetaria
         */
        function formatMoney(amount) {
            if (amount === null || amount === undefined) return '$0.00';
            return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    </script>
</body>
</html>
