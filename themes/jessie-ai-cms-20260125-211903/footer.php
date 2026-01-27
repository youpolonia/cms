
<footer style="background-color: #F9FAFB; padding: 40px 20px; font-family: 'Inter', sans-serif;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; max-width: 1200px; margin: auto;">
        <div style="flex: 1; min-width: 200px; margin-right: 20px;">
            <h3 style="color: #1D4ED8;">Navigation</h3>
            <ul style="list-style: none; padding: 0; color: #111827;">
                <li><a href="/" style="text-decoration: none; color: inherit;">Homepage</a></li>
                <li><a href="/about" style="text-decoration: none; color: inherit;">About</a></li>
                <li><a href="/services" style="text-decoration: none; color: inherit;">Services</a></li>
                <li><a href="/contact" style="text-decoration: none; color: inherit;">Contact</a></li>
                <li><a href="/blog" style="text-decoration: none; color: inherit;">Blog</a></li>
                <li><a href="/portfolio" style="text-decoration: none; color: inherit;">Portfolio</a></li>
                <li><a href="/pricing" style="text-decoration: none; color: inherit;">Pricing</a></li>
                <li><a href="/team" style="text-decoration: none; color: inherit;">Team</a></li>
                <li><a href="/faq" style="text-decoration: none; color: inherit;">FAQ</a></li>
                <li><a href="/testimonials" style="text-decoration: none; color: inherit;">Testimonials</a></li>
            </ul>
        </div>
        <div style="flex: 1; min-width: 200px; margin-right: 20px;">
            <h3 style="color: #1D4ED8;">Contact Info</h3>
            <p style="color: #111827;">Email: info@jessieai.com</p>
            <p style="color: #111827;">Phone: (123) 456-7890</p>
            <p style="color: #111827;">Address: 123 AI Lane, Tech City, TX 12345</p>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h3 style="color: #1D4ED8;">Follow Us</h3>
            <a href="#" style="margin-right: 10px;"><img src="facebook-icon.png" alt="Facebook" /></a>
            <a href="#" style="margin-right: 10px;"><img src="twitter-icon.png" alt="Twitter" /></a>
            <a href="#" style="margin-right: 10px;"><img src="linkedin-icon.png" alt="LinkedIn" /></a>
            <a href="#" style="margin-right: 10px;"><img src="instagram-icon.png" alt="Instagram" /></a>
        </div>
    </div>
    <div style="text-align: center; margin-top: 20px; color: #111827;">
        <form style="margin-bottom: 20px;">
            <input type="email" placeholder="Subscribe to our newsletter" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px; width: 300px; margin-right: 10px;">
            <button type="submit" style="padding: 10px 15px; background-color: #1D4ED8; color: white; border: none; border-radius: 5px;">Subscribe</button>
        </form>
        <p>&copy; <span id="year"></span> Jessie AI CMS. All rights reserved.</p>
    </div>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</footer>

<!-- Theme Scripts -->
<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle, .hamburger, [data-menu-toggle]');
    const mobileMenu = document.querySelector('.mobile-menu, .nav-mobile, [data-mobile-menu]');
    
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });
    }
    
    // Sticky header
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
});
</script>
</body>
</html>