<?php
$config = require __DIR__ . '/../config/config.php';
?>
        </main> <!-- .app-main -->

        <footer class="app-footer">
            <div class="app-container footer-layout">
                <div class="footer-copyright">
                    &copy; <?php echo date("Y"); ?> <b><?php echo $config['app_name']; ?></b>
                </div>
                <div class="footer-credit">
                    Phát triển bởi <a target="_blank" href="<?php echo $config['author_url']; ?>"><?php echo $config['author_name']; ?></a>
                </div>
            </div>
        </footer>
    </div> <!-- .app-wrapper -->
    
    <!-- Custom JS -->
    <script src="<?php echo $config['base_url']; ?>/assets/js/main.js"></script>
</body>
</html>
