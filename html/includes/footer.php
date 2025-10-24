<?php
/**
 * Footer - Closes layout and includes scripts
 * Closes page content, main content wrapper, and adds footer with scripts
 */
?>
            </div>
            <!-- Page Content Ends Here -->
        </div>
        <!-- Main Content Ends Here -->
    <?php if (isLoggedIn()): ?>
    </div>
    <!-- Layout Wrapper Ends Here -->
    <?php endif; ?>

    <!-- Footer -->
    <footer id="main-footer" class="footer mt-auto py-3 border-top">
        <div id="footer-container" class="container-fluid">
            <div id="footer-row" class="row">
                <div id="footer-copyright" class="col-md-6 text-md-start text-center">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> TodoTracker. All rights reserved.</span>
                </div>
                <div id="footer-links" class="col-md-6 text-md-end text-center mt-2 mt-md-0">
                    <a id="footer-privacy" href="/privacy.php" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a id="footer-terms" href="/terms.php" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>

    <!-- Auto-hide toasts after 5 seconds -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide toasts
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(function(toastEl) {
                const toast = new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 5000
                });
                toast.show();
            });
        });
    </script>
</body>
</html>
