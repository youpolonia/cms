<?php
$title = isset($title) ? $title : 'Home';
?>
<main class="container">
  <h1><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
  <p>Welcome to My CMS.</p>
  <section>
    <?= isset($body) ? $body : '' ?>
  </section>
</main>
