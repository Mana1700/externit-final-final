<?php
$current_year = date('Y');
?>
<footer class="footer mt-5 py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="text-dark mb-3">ExternIT</h5>
                <p class="text-muted mb-0">Connecting students with real-world opportunities.</p>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="text-dark mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                    <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    <li><a href="privacy.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    <li><a href="terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="text-dark mb-3">Connect With Us</h5>
                <div class="social-links">
                    <a href="#" class="text-muted text-decoration-none me-3">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-muted text-decoration-none me-3">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="#" class="text-muted text-decoration-none me-3">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="#" class="text-muted text-decoration-none">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-muted mb-0">&copy; <?php echo $current_year; ?> ExternIT. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    border-top: 1px solid #dee2e6;
}
.social-links a {
    font-size: 1.25rem;
}
.social-links a:hover {
    opacity: 0.8;
}
</style>

<!-- Load Bootstrap first -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Then load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Finally load our custom JS -->
    <script src="/externit-final/assets/js/main.js"></script>