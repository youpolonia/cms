<?php
$aboutLabel = theme_get('about.label', 'How It Works');
$aboutTitle = theme_get('about.title', 'From Idea to MVP in Minutes');
$aboutDesc = theme_get('about.description', 'Skip the months of learning to code. Our AI understands what you want to build and handles the technical heavy lifting.');
$aboutImage = theme_get('about.image', '');
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-content" data-animate>
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                
                <div class="process-steps">
                    <div class="step" data-animate>
                        <div class="step-number">01</div>
                        <div class="step-content">
                            <h4>Describe Your Vision</h4>
                            <p>Tell us what you want to build in plain English. "I need a booking system for my yoga studio."</p>
                        </div>
                    </div>
                    <div class="step" data-animate>
                        <div class="step-number">02</div>
                        <div class="step-content">
                            <h4>AI Generates Code</h4>
                            <p>Our engine translates your requirements into clean, production-ready code with best practices baked in.</p>
                        </div>
                    </div>
                    <div class="step" data-animate>
                        <div class="step-number">03</div>
                        <div class="step-content">
                            <h4>Iterate & Launch</h4>
                            <p>Tweak with natural language commands. When you're happy, deploy with one click.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="about-visual" data-animate>
                <?php if ($aboutImage): ?>
                <div class="about-image" data-ts-bg="about.image">
                    <img src="<?= esc($aboutImage) ?>" alt="How CodeForge works" loading="lazy">
                </div>
                <?php else: ?>
                <div class="code-preview">
                    <div class="preview-glow"></div>
                    <div class="code-window">
                        <div class="window-header">
                            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                            <span class="filename">BookingSystem.jsx</span>
                        </div>
                        <div class="code-content">
                            <pre><code><span class="keyword">import</span> { useState } <span class="keyword">from</span> <span class="string">'react'</span>;

<span class="keyword">export default function</span> <span class="function">BookingCalendar</span>() {
  <span class="keyword">const</span> [selectedDate, setDate] = <span class="function">useState</span>(<span class="keyword">null</span>);
  
  <span class="keyword">return</span> (
    <span class="tag">&lt;div</span> <span class="attr">className</span>=<span class="string">"booking-grid"</span><span class="tag">&gt;</span>
      <span class="comment">// AI-generated booking logic</span>
    <span class="tag">&lt;/div&gt;</span>
  );
}</code></pre>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
