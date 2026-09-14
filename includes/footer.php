<?php
$scripts = $scripts ?? [];
$isInternal = $isInternal ?? false;
?>
<?php if ($isInternal): ?>
    </main>
  </div>

<?php endif; ?>
<?php foreach ($scripts as $script): ?>
  <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>
