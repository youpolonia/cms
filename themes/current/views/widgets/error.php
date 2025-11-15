<?php
// Widget error (theme view stub)
?>
<div class="widget-error">
  <p>Wystąpił błąd w widżecie<?php if (isset($widget)) echo ': ' . htmlspecialchars((string)$widget, ENT_QUOTES); ?>.</p>
  <?php if (isset($error)): ?>
    <pre><?php echo htmlspecialchars((string)$error, ENT_QUOTES); ?></pre>
  <?php endif; ?>
</div>
