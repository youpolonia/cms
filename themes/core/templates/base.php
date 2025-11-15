<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $this->yield('title', 'Default Title'); ?></title>
    <link rel="stylesheet" href="<?php echo $this->asset('css/main.css'); ?>">
    <?php $this->yield('head'); ?>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">
                    <?php if ($this->isMultisiteEnabled()): ?>                        <?php echo $this->siteData('name', 'CMS'); ?>                    <?php else: ?>                        CMS
                    <?php endif; ?>
                </a>
            </div>
            <nav>
                <?php $this->yield('navigation', $this->require_once(__DIR__ . '/partials/navigation.php')); ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php $this->yield('content'); ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?>                <?php if ($this->isMultisiteEnabled()): ?>                    <?php echo $this->siteData('name', 'CMS'); ?>                <?php else: ?>                    CMS
                <?php endif; ?>
            </p>
            <?php $this->yield('footer'); ?>
        </div>
    </footer>

    <script src="<?php echo $this->asset('js/main.js'); ?>"></script>
    <?php $this->yield('scripts'); ?>
</body>
</html>
