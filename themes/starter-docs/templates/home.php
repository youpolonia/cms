<?php
/**
 * Home Template — KnowledgeBase Theme
 * Search hero, quick links, popular topics, page content
 */
?>

<div class="search-hero">
  <h1>How can we help?</h1>
  <p>Search our documentation, guides, and tutorials</p>
  <div class="search-hero-input">
    <span class="search-icon">🔍</span>
    <input type="text" placeholder="Search for articles, guides, API references..." id="heroSearch">
  </div>
</div>

<div class="quick-links">
  <a href="/getting-started" class="quick-link-card">
    <span class="quick-link-icon">🚀</span>
    <h3>Getting Started</h3>
    <p>Quick setup guide</p>
  </a>
  <a href="/api-reference" class="quick-link-card">
    <span class="quick-link-icon">📡</span>
    <h3>API Reference</h3>
    <p>Endpoints & methods</p>
  </a>
  <a href="/articles" class="quick-link-card">
    <span class="quick-link-icon">📖</span>
    <h3>Tutorials</h3>
    <p>Step-by-step guides</p>
  </a>
  <a href="/faq" class="quick-link-card">
    <span class="quick-link-icon">💡</span>
    <h3>FAQ</h3>
    <p>Common questions</p>
  </a>
</div>

<div class="topics-section">
  <h2>Popular Topics</h2>
  <div class="topics-grid">
    <a href="/getting-started" class="topic-card">
      <span class="topic-icon">⚡</span>
      <h3>Installation</h3>
      <p>Set up your environment and get running in minutes.</p>
    </a>
    <a href="/api-reference" class="topic-card">
      <span class="topic-icon">🔧</span>
      <h3>Configuration</h3>
      <p>Customize settings, themes, and advanced options.</p>
    </a>
    <a href="/articles" class="topic-card">
      <span class="topic-icon">🔑</span>
      <h3>Authentication</h3>
      <p>API keys, OAuth, and security best practices.</p>
    </a>
    <a href="/gallery" class="topic-card">
      <span class="topic-icon">📊</span>
      <h3>Data & Media</h3>
      <p>Working with uploads, galleries, and file management.</p>
    </a>
    <a href="/articles" class="topic-card">
      <span class="topic-icon">🧩</span>
      <h3>Integrations</h3>
      <p>Connect with third-party services and webhooks.</p>
    </a>
    <a href="/faq" class="topic-card">
      <span class="topic-icon">🛠️</span>
      <h3>Troubleshooting</h3>
      <p>Common issues, error codes, and solutions.</p>
    </a>
  </div>
</div>

<?php if (!empty($content) && trim(strip_tags($content)) !== ''): ?>
<div class="topics-section">
  <div class="content-inner">
    <?= $content ?>
  </div>
</div>
<?php endif; ?>
