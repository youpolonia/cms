<?php $this->extend('base') ?>

<?php $this->section('content') ?>
    <main class="content">
        <?php $this->insert('partials/header') ?>
        <section class="main-content">
            <h1>Welcome to our CMS</h1>
            <p>This is the default home page template.</p>
        </section>

        <?php $this->insert('partials/footer') ?>
    </main>
<?php $this->endSection();
