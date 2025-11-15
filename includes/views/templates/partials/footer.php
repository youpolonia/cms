<footer class="site-footer">
    <div class="container">
        <div class="copyright">
            &copy; <?= date('Y') ?> <?= View::e('site_name', 'CMS')  ?>
        </div>
        <?php View::partial('partials/footer-nav') 
?>    </div>
</footer>
