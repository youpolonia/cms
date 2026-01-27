<?php
/**
 * Jessie AI-CMS Theme - Footer
 */
$themeUrl = '/themes/jessie';
?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <span class="logo-icon">🤖</span>
                        <span class="logo-text">Jessie</span>
                    </a>
                    <p>The intelligent content management system powered by AI.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Jessie AI-CMS. Built with ❤️ and pure PHP.</p>
            </div>
        </div>
    </footer>

    <script>
        const header = document.getElementById('site-header');
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>
