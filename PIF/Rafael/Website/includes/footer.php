<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Theme Manager -->
<script src="js/theme.js"></script>

<!-- Custom JS -->
<script src="js/main.js"></script>
<?php if (isset($pageJS)): ?>
    <script src="js/<?php echo $pageJS; ?>"></script>
<?php endif; ?>
</body>
</html>