<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$isEdit = !empty($popup);
$title = $isEdit ? 'Edit Pop-up' : 'Create Pop-up';
$formFields = [];
if ($isEdit && !empty($popup['form_fields'])) {
    $formFields = json_decode($popup['form_fields'], true) ?: [];
}
$popupSettings = [];
if ($isEdit && !empty($popup['settings'])) {
    $popupSettings = json_decode($popup['settings'], true) ?: [];
}
ob_start();
?>
<style>
.pb-wrap{display:grid;grid-template-columns:60% 40%;gap:20px;min-height:calc(100vh - 120px)}
.pb-preview{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;padding:24px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.pb-preview-label{position:absolute;top:12px;left:16px;font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);font-weight:600}
.pb-settings{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;padding:20px;overflow-y:auto;max-height:calc(100vh - 120px)}
.pb-section{margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:16px}
.pb-section:last-child{border-bottom:none}
.pb-section-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--accent);margin-bottom:12px;display:flex;align-items:center;gap:6px}
.pb-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.pb-color{display:flex;align-items:center;gap:8px}
.pb-color input[type=color]{width:36px;height:36px;border:2px solid var(--border);border-radius:6px;cursor:pointer;padding:2px;background:var(--bg-tertiary)}
.pb-color label{font-size:.8rem;color:var(--text-secondary)}
.pb-toggle{display:flex;align-items:center;gap:8px}
.pb-toggle input[type=checkbox]{width:18px;height:18px;accent-color:var(--accent)}
.form-hint{font-size:.7rem;color:var(--text-muted);margin-top:2px}

/* Live preview popup styles */
.pv-overlay{position:absolute;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;transition:all .3s}
.pv-popup{border-radius:12px;padding:28px;max-width:420px;width:100%;text-align:center;transition:all .3s;box-shadow:0 16px 48px rgba(0,0,0,.3)}
.pv-popup img{max-width:100%;max-height:160px;border-radius:8px;margin-bottom:12px;object-fit:cover}
.pv-popup h2{font-size:1.3rem;font-weight:700;margin:0 0 8px}
.pv-popup p{font-size:.85rem;margin:0 0 16px;opacity:.85;line-height:1.5}
.pv-popup .pv-btn{display:inline-block;padding:10px 24px;border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;border:none;transition:transform .15s}
.pv-popup .pv-btn:hover{transform:scale(1.03)}
.pv-popup .pv-form{display:flex;flex-direction:column;gap:8px;margin-bottom:12px;text-align:left}
.pv-popup .pv-form input{padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.1);color:inherit;font-size:.85rem}

/* Type-specific preview */
.pv-slide-in .pv-overlay{align-items:flex-end;justify-content:flex-end;background:transparent;padding:16px}
.pv-slide-in .pv-popup{max-width:340px;text-align:left}
.pv-bar .pv-overlay{align-items:flex-start;background:transparent;padding:0}
.pv-bar .pv-popup{max-width:100%;width:100%;border-radius:0;padding:12px 24px;display:flex;align-items:center;gap:16px;text-align:left}
.pv-bar .pv-popup h2{font-size:.95rem;margin:0;white-space:nowrap}
.pv-bar .pv-popup p{margin:0;font-size:.8rem;flex:1}
.pv-bar .pv-popup img{max-height:40px;margin:0}
.pv-bar .pv-popup .pv-form{flex-direction:row;margin:0}
.pv-bar .pv-popup .pv-form input{width:140px}
.pv-fullscreen .pv-overlay{background:rgba(0,0,0,.85)}
.pv-fullscreen .pv-popup{max-width:560px}
.pv-bottom .pv-overlay{align-items:flex-end}
.pv-bottom-right .pv-overlay{align-items:flex-end;justify-content:flex-end;padding:16px}
.pv-bottom-left .pv-overlay{align-items:flex-end;justify-content:flex-start;padding:16px}
.pv-top .pv-overlay{align-items:flex-start}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="/admin/popups" style="color:var(--text-muted);font-size:1.2rem">←</a>
        <h1 style="font-size:1.3rem;font-weight:700"><?= $isEdit ? '✏️ Edit' : '🎯 Create' ?> Pop-up</h1>
    </div>
</div>

<form method="post" action="<?= $isEdit ? '/admin/popups/' . $popup['id'] . '/update' : '/admin/popups/store' ?>" id="popupForm">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="pb-wrap">
        <!-- LIVE PREVIEW -->
        <div class="pb-preview" id="previewArea">
            <span class="pb-preview-label">Live Preview</span>
            <div class="pv-overlay" id="pvOverlay">
                <div class="pv-popup" id="pvPopup">
                    <img id="pvImg" src="" alt="" style="display:none">
                    <h2 id="pvHeading">Your Heading</h2>
                    <p id="pvBody">Your message goes here...</p>
                    <div id="pvForm" class="pv-form" style="display:none"></div>
                    <button type="button" class="pv-btn" id="pvBtn">Subscribe</button>
                </div>
            </div>
        </div>

        <!-- SETTINGS -->
        <div class="pb-settings">
            <!-- Basic -->
            <div class="pb-section">
                <div class="pb-section-title">📋 Basic</div>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" value="<?= h($popup['name'] ?? '') ?>" required placeholder="e.g. Newsletter Signup">
                </div>
                <div class="pb-row">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" id="fType" class="form-select">
                            <?php foreach (['modal'=>'Modal','slide_in'=>'Slide In','bar'=>'Bar','fullscreen'=>'Fullscreen'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($popup['type'] ?? 'modal') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position</label>
                        <select name="position" id="fPosition" class="form-select">
                            <?php foreach (['center'=>'Center','bottom_right'=>'Bottom Right','bottom_left'=>'Bottom Left','top'=>'Top','bottom'=>'Bottom'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($popup['position'] ?? 'center') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="pb-section">
                <div class="pb-section-title">📝 Content</div>
                <div class="form-group">
                    <label class="form-label">Heading</label>
                    <input type="text" name="name_heading" id="fHeading" class="form-input" value="<?= h($popup['name'] ?? '') ?>" placeholder="Join our newsletter">
                </div>
                <div class="form-group">
                    <label class="form-label">Body Text</label>
                    <textarea name="content" id="fBody" class="form-input" rows="3" placeholder="Get the latest updates..."><?= h($popup['content'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image" id="fImage" class="form-input" value="<?= h($popup['image'] ?? '') ?>" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label class="form-label">CTA Button Text</label>
                    <input type="text" name="cta_text" id="fCtaText" class="form-input" value="<?= h($popup['cta_text'] ?? 'Subscribe') ?>">
                </div>
            </div>

            <!-- Appearance -->
            <div class="pb-section">
                <div class="pb-section-title">🎨 Appearance</div>
                <div style="display:flex;gap:20px;flex-wrap:wrap">
                    <div class="pb-color">
                        <input type="color" name="bg_color" id="fBgColor" value="<?= h($popup['bg_color'] ?? '#1e293b') ?>">
                        <label>Background</label>
                    </div>
                    <div class="pb-color">
                        <input type="color" name="text_color" id="fTextColor" value="<?= h($popup['text_color'] ?? '#e2e8f0') ?>">
                        <label>Text</label>
                    </div>
                    <div class="pb-color">
                        <input type="color" name="btn_color" id="fBtnColor" value="<?= h($popup['btn_color'] ?? '#6366f1') ?>">
                        <label>Button</label>
                    </div>
                </div>
            </div>

            <!-- Trigger -->
            <div class="pb-section">
                <div class="pb-section-title">⚡ Trigger</div>
                <div class="pb-row">
                    <div class="form-group">
                        <label class="form-label">Trigger Type</label>
                        <select name="trigger_type" id="fTriggerType" class="form-select">
                            <?php foreach (['delay'=>'Time Delay','scroll'=>'Scroll %','exit_intent'=>'Exit Intent','click'=>'On Click'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($popup['trigger_type'] ?? 'delay') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Value</label>
                        <input type="text" name="trigger_value" class="form-input" value="<?= h($popup['trigger_value'] ?? '3') ?>" placeholder="3 (seconds or %)">
                        <div class="form-hint" id="triggerHint">Seconds before showing</div>
                    </div>
                </div>
            </div>

            <!-- Targeting -->
            <div class="pb-section">
                <div class="pb-section-title">🎯 Targeting</div>
                <div class="form-group">
                    <label class="form-label">Show on URLs (one per line, * = all)</label>
                    <textarea name="show_on" class="form-input" rows="2" placeholder="*"><?= h($popup['show_on'] ?? '*') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Hide on URLs (one per line)</label>
                    <textarea name="hide_on" class="form-input" rows="2"><?= h($popup['hide_on'] ?? '') ?></textarea>
                </div>
                <div class="pb-toggle">
                    <input type="checkbox" name="show_once" id="fShowOnce" <?= ($popup['show_once'] ?? 1) ? 'checked' : '' ?>>
                    <label for="fShowOnce" style="font-size:.85rem">Show only once per visitor</label>
                </div>
            </div>

            <!-- CTA Action -->
            <div class="pb-section">
                <div class="pb-section-title">🔗 CTA Action</div>
                <div class="form-group">
                    <label class="form-label">Action</label>
                    <select name="cta_action" id="fCtaAction" class="form-select">
                        <option value="close" <?= ($popup['cta_action'] ?? 'close') === 'close' ? 'selected' : '' ?>>Close popup</option>
                        <option value="url" <?= ($popup['cta_action'] ?? '') === 'url' ? 'selected' : '' ?>>Open URL</option>
                        <option value="form" <?= ($popup['cta_action'] ?? '') === 'form' ? 'selected' : '' ?>>Show form</option>
                    </select>
                </div>
                <div class="form-group" id="ctaUrlGroup" style="display:none">
                    <label class="form-label">Destination URL</label>
                    <input type="text" name="cta_url" class="form-input" value="<?= h($popup['cta_url'] ?? '') ?>" placeholder="https://...">
                </div>
                <div id="ctaFormGroup" style="display:none">
                    <label class="form-label" style="margin-bottom:8px">Form Fields</label>
                    <div style="display:flex;gap:16px">
                        <div class="pb-toggle">
                            <input type="checkbox" name="form_field_email" id="ffEmail" <?= in_array('email', $formFields) ? 'checked' : '' ?>>
                            <label for="ffEmail" style="font-size:.85rem">Email</label>
                        </div>
                        <div class="pb-toggle">
                            <input type="checkbox" name="form_field_name" id="ffName" <?= in_array('name', $formFields) ? 'checked' : '' ?>>
                            <label for="ffName" style="font-size:.85rem">Name</label>
                        </div>
                        <div class="pb-toggle">
                            <input type="checkbox" name="form_field_phone" id="ffPhone" <?= in_array('phone', $formFields) ? 'checked' : '' ?>>
                            <label for="ffPhone" style="font-size:.85rem">Phone</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coupon Integration -->
            <div class="pb-section">
                <div class="pb-section-title">🏷️ Coupon</div>
                <div class="pb-toggle" style="margin-bottom:10px">
                    <input type="checkbox" name="generate_coupon" id="fGenCoupon" <?= !empty($popupSettings['generate_coupon']) ? 'checked' : '' ?>>
                    <label for="fGenCoupon" style="font-size:.85rem">Generate coupon code on submission</label>
                </div>
                <div class="form-group" id="couponValueGroup" style="display:<?= !empty($popupSettings['generate_coupon']) ? 'block' : 'none' ?>">
                    <label class="form-label">Coupon Discount %</label>
                    <input type="number" name="coupon_value" class="form-input" value="<?= h((string)($popupSettings['coupon_value'] ?? 10)) ?>" min="1" max="100" step="1" placeholder="10">
                    <div class="form-hint">Percentage discount, single use, valid 7 days</div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="pb-section">
                <div class="pb-section-title">📅 Schedule</div>
                <div class="pb-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-input" value="<?= h($popup['start_date'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="datetime-local" name="end_date" class="form-input" value="<?= h($popup['end_date'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Active + Submit -->
            <div style="display:flex;align-items:center;gap:16px;padding-top:8px">
                <div class="pb-toggle">
                    <input type="checkbox" name="active" id="fActive" <?= ($popup['active'] ?? 0) ? 'checked' : '' ?>>
                    <label for="fActive" style="font-size:.85rem;font-weight:600">Active</label>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-left:auto">
                    <?= $isEdit ? '💾 Update Pop-up' : '🎯 Create Pop-up' ?>
                </button>
                <a href="/admin/popups" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
(function(){
    const $ = s => document.querySelector(s);
    const preview = $('#previewArea');
    const overlay = $('#pvOverlay');
    const popup = $('#pvPopup');
    const pvImg = $('#pvImg');
    const pvH = $('#pvHeading');
    const pvB = $('#pvBody');
    const pvBtn = $('#pvBtn');
    const pvForm = $('#pvForm');

    function updatePreview(){
        // Colors
        const bg = $('#fBgColor').value;
        const txt = $('#fTextColor').value;
        const btn = $('#fBtnColor').value;
        popup.style.background = bg;
        popup.style.color = txt;
        pvBtn.style.background = btn;
        pvBtn.style.color = '#fff';

        // Content
        pvH.textContent = $('#fHeading').value || 'Your Heading';
        pvB.textContent = $('#fBody').value || 'Your message goes here...';
        pvBtn.textContent = $('#fCtaText').value || 'Subscribe';

        // Image
        const imgUrl = $('#fImage').value.trim();
        if(imgUrl){pvImg.src=imgUrl;pvImg.style.display='block';pvImg.onerror=function(){this.style.display='none'}}
        else{pvImg.style.display='none'}

        // Type classes
        const type = $('#fType').value;
        const pos = $('#fPosition').value;
        preview.className='pb-preview';
        if(type==='slide_in') preview.classList.add('pv-slide-in');
        else if(type==='bar'){preview.classList.add('pv-bar');if(pos==='bottom')preview.classList.add('pv-bottom')}
        else if(type==='fullscreen') preview.classList.add('pv-fullscreen');
        else{
            if(pos==='bottom') preview.classList.add('pv-bottom');
            if(pos==='bottom_right') preview.classList.add('pv-bottom-right');
            if(pos==='bottom_left') preview.classList.add('pv-bottom-left');
            if(pos==='top') preview.classList.add('pv-top');
        }

        // CTA Action visibility
        const action = $('#fCtaAction').value;
        $('#ctaUrlGroup').style.display = action==='url' ? 'block' : 'none';
        $('#ctaFormGroup').style.display = action==='form' ? 'block' : 'none';

        // Form fields in preview
        if(action==='form'){
            pvForm.style.display='flex';
            pvForm.innerHTML='';
            if($('#ffEmail').checked) pvForm.innerHTML+='<input type="email" placeholder="Email" disabled>';
            if($('#ffName').checked) pvForm.innerHTML+='<input type="text" placeholder="Name" disabled>';
            if($('#ffPhone').checked) pvForm.innerHTML+='<input type="tel" placeholder="Phone" disabled>';
        }else{
            pvForm.style.display='none';
        }

        // Trigger hint
        const tType = $('#fTriggerType').value;
        const hints = {delay:'Seconds before showing',scroll:'Scroll percentage (0-100)',exit_intent:'Shows on mouse exit',click:'Triggered by .popup-trigger click'};
        $('#triggerHint').textContent = hints[tType]||'';
    }

    // Bind all inputs
    document.querySelectorAll('#popupForm input, #popupForm select, #popupForm textarea').forEach(el=>{
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    updatePreview();

    // Coupon toggle
    const genCoupon = document.getElementById('fGenCoupon');
    const couponGroup = document.getElementById('couponValueGroup');
    if (genCoupon && couponGroup) {
        genCoupon.addEventListener('change', function() {
            couponGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
})();
</script>

<?php $content = ob_get_clean(); require CMS_APP . '/views/admin/layouts/topbar.php';
