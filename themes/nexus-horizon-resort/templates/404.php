<style>
.error-section{min-height:60vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 20px;background:var(--background,#0a0c14)}
.error-content{max-width:500px}
.error-code{display:block;font-size:clamp(6rem,15vw,10rem);font-weight:800;line-height:1;background:linear-gradient(135deg,var(--primary,#00ff9d),var(--accent,#00d9ff));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:16px;font-family:var(--font-heading,inherit)}
.error-title{font-size:clamp(1.5rem,3vw,2rem);font-weight:700;color:var(--text,#e6f1ff);margin:0 0 12px;font-family:var(--font-heading,inherit)}
.error-text{font-size:1rem;color:var(--text-muted,#8a9bb8);line-height:1.6;margin:0 0 32px}
.error-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.error-actions .btn{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:6px;font-weight:600;font-size:.9rem;text-decoration:none;transition:all .3s}
.error-actions .btn-primary{background:var(--primary,#00ff9d);color:rgba(0,0,0,.9);border:2px solid var(--primary,#00ff9d)}
.error-actions .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(var(--primary-rgb,0,255,157),.3)}
.error-actions .btn-outline{background:transparent;color:var(--text,#e6f1ff);border:2px solid var(--border,#2a3042)}
.error-actions .btn-outline:hover{border-color:var(--primary,#00ff9d);color:var(--primary,#00ff9d)}
</style>
<section class="error-section">
    <div class="error-content">
        <span class="error-code">404</span>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-text">The page you're looking for doesn't exist or has been moved.</p>
        <div class="error-actions">
            <a href="/" class="btn btn-primary"><i class="fas fa-home"></i> Go Home</a>
            <a href="/articles" class="btn btn-outline"><i class="fas fa-newspaper"></i> Browse Articles</a>
        </div>
    </div>
</section>