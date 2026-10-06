            </main>
        </div>
    </div>

    <script src="../assets/js/admin-nav.js"></script>
    <script src="../assets/js/custom-select.js"></script>
    <script src="../assets/js/admin-core.js"></script>
    <?php foreach ($admin_scripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
