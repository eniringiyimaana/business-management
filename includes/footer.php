            </div> <!-- Closing tag for main content -->
        </div> <!-- Closing tag for row -->
    </div> <!-- Closing tag for container-fluid -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
    <?php if (isset($custom_js)): ?>
    <script>
        <?php echo $custom_js; ?>
    </script>
    <?php endif; ?>
</body>
</html>