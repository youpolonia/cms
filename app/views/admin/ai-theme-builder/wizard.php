<?php
/**
 * AI Theme Builder — 5-Step Content-First Wizard UI
 * Catppuccin Mocha Dark Theme
 */
$username = $username ?? \Core\Session::getAdminUsername() ?? "Admin";
csrf_boot('admin');
$csrfToken = csrf_token();
$presetsJson = json_encode($stylePresets ?? [], JSON_HEX_APOS);
$sectionsJson = json_encode($availableSections ?? [], JSON_HEX_APOS);
$tonesJson = json_encode($contentTones ?? [], JSON_HEX_APOS);
$modelsJson = json_encode($aiModels ?? [], JSON_HEX_APOS);
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Theme Builder — Wizard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/fontawesome.min.css">
<style>
:root {
    --ctp-base:#1e1e2e;--ctp-mantle:#181825;--ctp-crust:#11111b;
    --ctp-surface0:#313244;--ctp-surface1:#45475a;--ctp-surface2:#585b70;
    --ctp-overlay0:#6c7086;--ctp-overlay1:#7f849c;
    --ctp-text:#cdd6f4;--ctp-subtext0:#a6adc8;--ctp-subtext1:#bac2de;
    --ctp-blue:#89b4fa;--ctp-green:#a6e3a1;--ctp-mauve:#cba6f7;
    --ctp-red:#f38ba8;--ctp-peach:#fab387;--ctp-yellow:#f9e2af;
    --ctp-teal:#94e2d5;--ctp-lavender:#b4befe;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:"Inter",sans-serif;background:var(--ctp-base);color:var(--ctp-text);min-height:100vh;display:flex;flex-direction:column}

/* Topbar */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;background:var(--ctp-mantle);border-bottom:1px solid var(--ctp-surface0);flex-shrink:0}
.topbar a{color:var(--ctp-subtext0);text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;transition:color .2s}
.topbar a:hover{color:var(--ctp-text)}
.topbar-title{font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px}
.topbar-title .badge{background:var(--ctp-surface1);color:var(--ctp-subtext0);font-size:9px;font-weight:600;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em}

/* Step indicator */
.steps-bar{display:flex;align-items:center;justify-content:center;gap:0;padding:16px 24px;background:var(--ctp-mantle);border-bottom:1px solid var(--ctp-surface0)}
.step-item{display:flex;align-items:center;gap:8px;cursor:default;opacity:.4;transition:opacity .3s}
.step-item.active,.step-item.done{opacity:1}
.step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;background:var(--ctp-surface0);color:var(--ctp-overlay0);transition:all .3s}
.step-item.active .step-num{background:var(--ctp-blue);color:var(--ctp-crust)}
.step-item.done .step-num{background:var(--ctp-green);color:var(--ctp-crust)}
.step-label{font-size:13px;font-weight:500;color:var(--ctp-subtext0)}
.step-item.active .step-label{color:var(--ctp-text)}
.step-item.done .step-label{color:var(--ctp-green)}
.step-connector{width:60px;height:2px;background:var(--ctp-surface0);margin:0 8px}
.step-item.done+.step-connector{background:var(--ctp-green)}

/* Main layout */
.main{display:flex;flex:1;min-height:0;overflow:hidden}
.panel{width:100%;max-width:720px;margin:0 auto;padding:32px 24px;overflow-y:auto}
.panel.split{width:480px;min-width:480px;max-width:480px;margin:0;border-right:1px solid var(--ctp-surface0)}
.preview-panel{flex:1;display:flex;flex-direction:column;background:var(--ctp-crust)}
.preview-toolbar{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;border-bottom:1px solid var(--ctp-surface0);background:var(--ctp-mantle)}
.preview-toolbar .url-bar{flex:1;margin:0 12px;padding:6px 14px;background:var(--ctp-surface0);border-radius:8px;font-size:12px;color:var(--ctp-overlay0);text-align:center}
.preview-frame{flex:1;display:flex;justify-content:center;overflow:hidden}
.preview-frame iframe{width:100%;height:100%;border:none;background:#fff}
.device-btns button{background:none;border:1px solid var(--ctp-surface0);color:var(--ctp-overlay0);padding:4px 8px;border-radius:4px;cursor:pointer;font-size:12px}
.device-btns button.active{border-color:var(--ctp-blue);color:var(--ctp-blue)}

/* Form elements */
.field{margin-bottom:16px}
.label{display:block;font-size:11px;font-weight:500;color:var(--ctp-overlay1);text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}
textarea.input{width:100%;min-height:100px;padding:12px 14px;background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:8px;color:var(--ctp-text);font:inherit;font-size:14px;resize:vertical;line-height:1.6}
textarea.input:focus{outline:none;border-color:var(--ctp-blue)}
select.input{width:100%;padding:10px 14px;background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:8px;color:var(--ctp-text);font:inherit;font-size:14px;cursor:pointer;-webkit-appearance:none}
select.input:focus{outline:none;border-color:var(--ctp-blue)}
input.input{width:100%;padding:10px 14px;background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:8px;color:var(--ctp-text);font:inherit;font-size:14px}
input.input:focus{outline:none;border-color:var(--ctp-blue)}
.row-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* Chips */
.chips{display:flex;flex-wrap:wrap;gap:6px}
.chip{padding:5px 12px;border-radius:6px;font-size:12px;cursor:pointer;background:transparent;border:1px solid var(--ctp-surface1);color:var(--ctp-subtext0);transition:all .15s;display:flex;align-items:center;gap:4px;user-select:none}
.chip:hover{border-color:var(--ctp-overlay0);color:var(--ctp-text)}
.chip.selected{background:rgba(137,180,250,.08);border-color:var(--ctp-blue);color:var(--ctp-blue);font-weight:500}
.chip-group-label{width:100%;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--ctp-overlay0);margin:6px 0 2px;padding-top:6px;border-top:1px solid rgba(255,255,255,.04)}
.chip-group-label:first-child{border:0;margin-top:0;padding-top:0}

/* Section heading */
.section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.section-title{font-size:13px;font-weight:600;color:var(--ctp-subtext1)}
.section-hint{font-size:11px;color:var(--ctp-surface2);font-style:italic}
.divider{height:1px;background:var(--ctp-surface0);margin:20px 0}

/* Buttons */
.btn{padding:10px 20px;border-radius:8px;font:inherit;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all .15s;display:inline-flex;align-items:center;gap:8px}
.btn-primary{background:var(--ctp-blue);color:var(--ctp-crust)}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-secondary{background:var(--ctp-surface0);color:var(--ctp-text);border:1px solid var(--ctp-surface1)}
.btn-secondary:hover{border-color:var(--ctp-overlay0)}
.btn-success{background:var(--ctp-green);color:var(--ctp-crust)}
.btn-danger{background:var(--ctp-red);color:var(--ctp-crust)}
.btn-row{display:flex;gap:10px;margin-top:24px}
.btn .spinner{display:none;width:16px;height:16px;border:2px solid rgba(0,0,0,.2);border-top-color:var(--ctp-crust);border-radius:50%;animation:spin .6s linear infinite}
.btn.loading .spinner{display:block}
.btn.loading .btn-label{display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* Presets */
.presets-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;margin-bottom:16px}
.preset-card{padding:12px 14px;border-radius:8px;background:var(--ctp-surface0);border:1px solid transparent;cursor:pointer;transition:all .15s}
.preset-card:hover{border-color:var(--ctp-surface2)}
.preset-card.selected{border-color:var(--ctp-blue);background:rgba(137,180,250,.06)}
.preset-card .preset-colors{display:flex;gap:3px;margin-bottom:6px}
.preset-card .preset-colors span{width:16px;height:16px;border-radius:4px;border:1px solid rgba(255,255,255,.06)}
.preset-card .preset-name{font-size:13px;font-weight:600}
.preset-card .preset-meta{font-size:11px;color:var(--ctp-overlay0);margin-top:2px}

/* Checkboxes */
.check-list{display:flex;flex-direction:column;gap:4px}
.check-item{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:8px;background:var(--ctp-surface0);border:1px solid transparent;cursor:pointer;transition:all .15s}
.check-item:hover{border-color:var(--ctp-surface1)}
.check-item.checked{background:rgba(137,180,250,.08);border-color:rgba(137,180,250,.2)}
.check-item.locked{opacity:.7;cursor:default}
.check-item input{display:none}
.check-box{width:20px;height:20px;border-radius:5px;border:2px solid var(--ctp-surface2);display:flex;align-items:center;justify-content:center;font-size:12px;color:transparent;transition:all .15s;flex-shrink:0}
.check-item.checked .check-box{background:var(--ctp-blue);border-color:var(--ctp-blue);color:var(--ctp-crust)}
.check-icon{width:24px;text-align:center;color:var(--ctp-overlay0);font-size:14px}
.check-label{flex:1;font-size:13px;font-weight:500}
.check-desc{font-size:11px;color:var(--ctp-overlay0)}

/* Color palette */
.palette{display:flex;gap:8px;margin:12px 0}
.palette-swatch{width:48px;height:48px;border-radius:10px;border:2px solid var(--ctp-surface1);cursor:pointer;position:relative;overflow:hidden}
.palette-swatch input[type=color]{position:absolute;inset:-4px;width:calc(100% + 8px);height:calc(100% + 8px);cursor:pointer;border:none;padding:0}
.palette-label{font-size:10px;color:var(--ctp-overlay0);text-align:center;margin-top:4px}

/* Brief review card */
.brief-card{background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:12px;padding:20px;margin-bottom:20px}
.brief-card h3{font-size:16px;margin-bottom:12px}
.brief-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.05)}
.brief-row:last-child{border:0}
.brief-key{color:var(--ctp-subtext0)}
.brief-val{font-weight:500}
.brief-val-editable{font-weight:500;background:var(--ctp-mantle);border:1px solid var(--ctp-surface1);border-radius:6px;padding:4px 10px;color:var(--ctp-text);font:inherit;font-size:13px;min-width:180px;text-align:right}
.brief-val-editable:focus{outline:none;border-color:var(--ctp-blue)}
.brief-section-group{margin-top:12px}
.brief-section-item{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px}
.brief-section-item:last-child{border:0}
.brief-section-icon{font-size:14px;width:22px;text-align:center}
.brief-section-name{flex:1;font-weight:500}
.brief-section-badge{font-size:10px;padding:2px 8px;border-radius:4px;background:rgba(137,180,250,.1);color:var(--ctp-blue);text-transform:uppercase;letter-spacing:.04em}
.brief-regen-btn{background:none;border:1px solid var(--ctp-surface1);color:var(--ctp-overlay0);padding:3px 8px;border-radius:5px;cursor:pointer;font-size:11px;display:inline-flex;align-items:center;gap:4px;transition:all .15s}
.brief-regen-btn:hover{border-color:var(--ctp-blue);color:var(--ctp-blue)}
.brief-regen-btn.loading{opacity:.5;pointer-events:none}
.palette-hex{font-size:9px;color:var(--ctp-overlay0);text-align:center;margin-top:2px;font-family:monospace}

/* Preset palettes grid */
.palette-presets{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;margin:8px 0 12px}
.palette-preset{display:flex;align-items:center;gap:0;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid var(--ctp-surface1);transition:all .15s;height:36px;position:relative}
.palette-preset:hover{border-color:var(--ctp-overlay0);transform:translateY(-1px)}
.palette-preset.selected{border-color:var(--ctp-blue);box-shadow:0 0 0 1px var(--ctp-blue)}
.palette-preset-color{flex:1;height:100%}
.palette-preset-name{position:absolute;bottom:0;left:0;right:0;text-align:center;font-size:9px;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.7);padding:2px;letter-spacing:.03em;font-weight:500}

/* Font select */
.brief-font-select{background:var(--ctp-mantle);border:1px solid var(--ctp-surface1);border-radius:6px;padding:6px 10px;color:var(--ctp-text);font:inherit;font-size:13px;min-width:200px;cursor:pointer;-webkit-appearance:none}
.brief-font-select:focus{outline:none;border-color:var(--ctp-blue)}

/* Section add */
.brief-add-section{display:flex;gap:6px;margin-top:8px}
.brief-add-select{flex:1;background:var(--ctp-mantle);border:1px solid var(--ctp-surface1);border-radius:6px;padding:5px 10px;color:var(--ctp-text);font:inherit;font-size:12px;cursor:pointer}
.brief-add-btn{background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);color:var(--ctp-blue);padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:500;transition:all .15s}
.brief-add-btn:hover{border-color:var(--ctp-blue);background:rgba(137,180,250,.08)}

/* Dropzone */
.dropzone{border:2px dashed var(--ctp-surface1);border-radius:12px;padding:40px 20px;text-align:center;cursor:pointer;transition:all .2s;color:var(--ctp-overlay0)}
/* Pattern picker grid */
.pattern-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;margin-top:8px}
.pattern-card{background:var(--ctp-surface0);border:2px solid var(--ctp-surface1);border-radius:10px;padding:10px;cursor:pointer;transition:all .2s;text-align:center;position:relative}
.pattern-card:hover{border-color:var(--ctp-blue);background:var(--ctp-surface1)}
.pattern-card.selected{border-color:var(--ctp-blue);box-shadow:0 0 0 2px rgba(137,180,250,.25)}
.pattern-card.selected::after{content:'✓';position:absolute;top:6px;right:8px;color:var(--ctp-blue);font-weight:700;font-size:14px}
.pattern-card svg{display:block;margin:0 auto 6px;border-radius:4px}
.pattern-card .pc-name{font-size:11px;font-weight:600;color:var(--ctp-text);line-height:1.2}
.pattern-card .pc-group{font-size:9px;color:var(--ctp-overlay0);margin-top:2px;text-transform:uppercase;letter-spacing:.5px}
.pattern-card.auto-card{border-style:dashed;background:var(--ctp-mantle)}
.pattern-card.auto-card .pc-name{color:var(--ctp-blue)}
.dropzone:hover,.dropzone.dragover{border-color:var(--ctp-blue);background:rgba(137,180,250,.05)}
/* Page layout style cards */
.page-layouts-section{margin-top:20px}
.page-layout-group{margin-bottom:16px;padding:12px 14px;background:var(--ctp-surface0);border-radius:10px;border:1px solid var(--ctp-surface1)}
.page-layout-group .plg-header{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.page-layout-group .plg-icon{font-size:14px;width:22px;text-align:center}
.page-layout-group .plg-name{font-size:13px;font-weight:600;color:var(--ctp-text)}
.page-layout-row{display:flex;gap:8px;flex-wrap:wrap}
.page-layout-card{flex:1;min-width:100px;max-width:160px;background:var(--ctp-mantle);border:2px solid var(--ctp-surface1);border-radius:8px;padding:8px;cursor:pointer;text-align:center;transition:all .2s;position:relative}
.page-layout-card:hover{border-color:var(--ctp-blue);background:var(--ctp-surface0)}
.page-layout-card.selected{border-color:var(--ctp-blue);box-shadow:0 0 0 2px rgba(137,180,250,.25)}
.page-layout-card.selected::after{content:'✓';position:absolute;top:4px;right:6px;color:var(--ctp-blue);font-weight:700;font-size:12px}
.page-layout-card .plc-icon{font-size:18px;margin-bottom:4px}
.page-layout-card .plc-name{font-size:10px;font-weight:600;color:var(--ctp-text);line-height:1.2}
.page-layout-card .plc-desc{font-size:9px;color:var(--ctp-overlay0);margin-top:2px;line-height:1.2}
.dropzone i{font-size:32px;margin-bottom:8px;display:block}
.dropzone p{font-size:13px}
.dropzone input{display:none}
.img-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;margin-top:12px}
.img-thumb{width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;border:2px solid transparent}
.img-thumb.selected{border-color:var(--ctp-blue)}

/* Progress */
.progress-list{display:flex;flex-direction:column;gap:6px;margin:16px 0}
.progress-item{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:8px;background:var(--ctp-surface0);font-size:13px;color:var(--ctp-overlay0);transition:all .3s}
.progress-item.running{color:var(--ctp-blue);border-left:3px solid var(--ctp-blue)}
.progress-item.done{color:var(--ctp-green);border-left:3px solid var(--ctp-green)}
.progress-item.error{color:var(--ctp-red);border-left:3px solid var(--ctp-red)}
.progress-item i{width:20px;text-align:center}

/* Tabs */
.tabs{display:flex;gap:2px;background:var(--ctp-surface0);border-radius:8px;padding:3px;margin-bottom:16px;overflow-x:auto}
.tab{padding:8px 16px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;color:var(--ctp-subtext0);transition:all .15s;white-space:nowrap;display:flex;align-items:center;gap:6px;user-select:none}
.tab:hover{color:var(--ctp-text)}
.tab.active{background:var(--ctp-blue);color:var(--ctp-crust)}
.tab .tab-status{width:8px;height:8px;border-radius:50%;background:var(--ctp-surface2)}
.tab.generated .tab-status{background:var(--ctp-green)}
.tab.generating .tab-status{background:var(--ctp-yellow);animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* Checklist */
.checklist{display:flex;flex-direction:column;gap:4px}
.checklist-item{display:flex;align-items:center;gap:8px;font-size:13px;padding:6px 0}
.checklist-item i{width:20px;text-align:center}
.checklist-item.ok i{color:var(--ctp-green)}
.checklist-item.pending i{color:var(--ctp-overlay0)}

/* Toast notification */
.toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:500;color:var(--ctp-crust);transform:translateY(100px);opacity:0;transition:all .3s;z-index:9999}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{background:var(--ctp-green)}
.toast.error{background:var(--ctp-red)}
.toast.info{background:var(--ctp-blue)}
/* AI Error Notification Panel */
.ai-error-panel{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--ctp-mantle);border:2px solid var(--ctp-red);border-radius:16px;padding:32px;max-width:520px;width:90%;z-index:10000;box-shadow:0 20px 60px rgba(0,0,0,.5);animation:errorSlideIn .3s ease}
.ai-error-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;animation:fadeIn .2s ease}
@keyframes errorSlideIn{from{transform:translate(-50%,-50%) scale(.9);opacity:0}to{transform:translate(-50%,-50%) scale(1);opacity:1}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.ai-error-panel .error-icon{font-size:48px;text-align:center;margin-bottom:16px}
.ai-error-panel .error-title{font-size:18px;font-weight:700;color:var(--ctp-red);text-align:center;margin-bottom:12px}
.ai-error-panel .error-message{font-size:14px;color:var(--ctp-text);line-height:1.6;margin-bottom:16px;text-align:center}
.ai-error-panel .error-action{background:var(--ctp-surface0);border-radius:10px;padding:12px 16px;font-size:13px;color:var(--ctp-subtext0);margin-bottom:20px;display:flex;align-items:center;gap:8px}
.ai-error-panel .error-action::before{content:"💡";font-size:16px;flex-shrink:0}
.ai-error-panel .error-link{display:block;text-align:center;margin-bottom:16px}
.ai-error-panel .error-link a{color:var(--ctp-blue);text-decoration:underline;font-size:13px}
.ai-error-panel .error-buttons{display:flex;gap:10px;justify-content:center}
.ai-error-panel .error-buttons button{padding:10px 20px;border-radius:10px;border:none;font-size:13px;font-weight:600;cursor:pointer;transition:all .2s}
.ai-error-panel .btn-retry{background:var(--ctp-blue);color:var(--ctp-crust)}
.ai-error-panel .btn-retry:hover{opacity:.85}
.ai-error-panel .btn-switch{background:var(--ctp-surface1);color:var(--ctp-text)}
.ai-error-panel .btn-switch:hover{background:var(--ctp-surface2)}

/* Provider Health Status Bar */
.provider-health{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0;padding:14px 16px;background:var(--ctp-mantle);border-radius:12px;border:1px solid var(--ctp-surface0)}
.provider-health-title{width:100%;font-size:12px;font-weight:600;color:var(--ctp-subtext0);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;display:flex;align-items:center;gap:8px}
.provider-health-title .refresh-btn{font-size:11px;cursor:pointer;color:var(--ctp-blue);opacity:.7;transition:opacity .2s}
.provider-health-title .refresh-btn:hover{opacity:1}
.ph-item{display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:500;background:var(--ctp-surface0);color:var(--ctp-text);transition:all .2s;cursor:default;flex:0 0 auto}
.ph-item[data-status="ok"]{border-left:3px solid var(--ctp-green)}
.ph-item[data-status="warning"],.ph-item[data-status="rate_limited"]{border-left:3px solid var(--ctp-yellow)}
.ph-item[data-status="no_credits"],.ph-item[data-status="low_balance"],.ph-item[data-status="auth_error"],.ph-item[data-status="error"],.ph-item[data-status="not_enabled"]{border-left:3px solid var(--ctp-red)}
.ph-item[data-status="not_configured"]{border-left:3px solid var(--ctp-surface2);opacity:.5}
.ph-item[data-status="checking"]{border-left:3px solid var(--ctp-blue);opacity:.6}
.ph-item .ph-icon{font-size:14px}
.ph-item .ph-name{font-weight:600}
.ph-item .ph-msg{color:var(--ctp-subtext0);font-size:11px}
.ph-item .ph-link{font-size:10px;color:var(--ctp-blue);text-decoration:underline;margin-left:4px}
.ph-item .ph-balance{font-weight:700;color:var(--ctp-green)}
.ph-item[data-status="low_balance"] .ph-balance,.ph-item[data-status="warning"] .ph-balance{color:var(--ctp-yellow)}
.ph-item[data-status="no_credits"] .ph-balance{color:var(--ctp-red)}
.ai-error-panel .btn-dismiss{background:transparent;color:var(--ctp-subtext0);font-size:12px}
.ai-error-panel .error-type-badge{display:inline-block;background:var(--ctp-surface1);color:var(--ctp-subtext0);font-size:11px;padding:3px 10px;border-radius:20px;margin-bottom:12px;text-align:center;width:fit-content;margin-left:auto;margin-right:auto}


/* Templates collapse */
.templates-collapse{margin-bottom:16px}
.templates-collapse summary{font-size:12px;font-weight:500;color:var(--ctp-overlay1);cursor:pointer;padding:6px 0;user-select:none;list-style:none}
.templates-collapse summary::-webkit-details-marker{display:none}
.templates-collapse summary::before{content:"+ ";font-weight:400}
.templates-collapse[open] summary::before{content:"− "}
.templates-collapse summary:hover{color:var(--ctp-text)}

/* Prompt suggestions */
.prompt-suggestions{display:none;gap:8px;margin-top:10px;flex-wrap:wrap}
.prompt-suggestions.visible{display:flex}
.prompt-suggestion{flex:1;min-width:calc(50% - 4px);padding:10px 14px;background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:10px;cursor:pointer;transition:all .15s;display:flex;gap:10px;align-items:flex-start}
.prompt-suggestion:hover{border-color:var(--ctp-blue);background:rgba(137,180,250,.05);transform:translateY(-1px)}
.prompt-suggestion .ps-icon{font-size:20px;flex-shrink:0;line-height:1.3}
.prompt-suggestion .ps-body{flex:1;min-width:0}
.prompt-suggestion .ps-label{font-size:12px;font-weight:600;color:var(--ctp-text);margin-bottom:2px}
.prompt-suggestion .ps-text{font-size:11px;color:var(--ctp-subtext0);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.prompt-suggestion .ps-tags{display:flex;gap:4px;margin-top:4px}
.prompt-suggestion .ps-tag{font-size:9px;padding:1px 6px;border-radius:4px;background:rgba(137,180,250,.08);color:var(--ctp-blue);text-transform:uppercase;letter-spacing:.04em}
.btn-surprise{background:linear-gradient(135deg,var(--ctp-mauve),var(--ctp-blue));color:var(--ctp-crust);border:none;font-weight:600;gap:6px}
.btn-surprise:hover{opacity:.9;transform:translateY(-1px)}

/* Visibility */
.hidden{display:none!important}

/* Content Plan cards (Step 1) */
.content-plan-card{background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:10px;margin-bottom:8px;overflow:hidden}
.cpc-header{display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer}
.cpc-header:hover{background:rgba(137,180,250,.03)}
.cpc-icon{width:28px;text-align:center;color:var(--ctp-blue);font-size:14px}
.cpc-title{flex:1;background:transparent;border:none;color:var(--ctp-text);font:inherit;font-size:14px;font-weight:600;padding:0}
.cpc-title:focus{outline:none;border-bottom:1px solid var(--ctp-blue)}
.cpc-status{font-size:11px;color:var(--ctp-overlay0)}
.cpc-toggle{background:none;border:none;color:var(--ctp-overlay0);cursor:pointer;font-size:12px;padding:4px}
.cpc-body{padding:0 14px 14px;display:none}
.content-plan-card.expanded .cpc-body{display:block}
.content-plan-card.expanded .cpc-toggle i{transform:rotate(180deg)}
.cpc-outline{display:flex;flex-direction:column;gap:4px}
.cpc-outline-item{display:flex;align-items:center;gap:8px;padding:6px 10px;background:var(--ctp-mantle);border-radius:6px;font-size:13px}
.cpc-outline-item input{flex:1;background:transparent;border:none;color:var(--ctp-text);font:inherit;font-size:13px}
.cpc-outline-item input:focus{outline:none}
.cpc-outline-item .cpc-remove{background:none;border:none;color:var(--ctp-surface2);cursor:pointer;font-size:11px}
.cpc-outline-item .cpc-remove:hover{color:var(--ctp-red)}
.cpc-meta{display:flex;gap:16px;font-size:11px;color:var(--ctp-overlay0);margin-top:8px}
.cpc-meta input{background:transparent;border:none;border-bottom:1px solid var(--ctp-surface1);color:var(--ctp-subtext0);font:inherit;font-size:11px;width:200px}
.cpc-meta input:focus{outline:none;border-color:var(--ctp-blue)}

/* Content Studio cards (Step 2) */
.content-page-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;background:var(--ctp-surface0);border:1px solid transparent;cursor:pointer;transition:all .15s;margin-bottom:4px}
.content-page-card:hover{border-color:var(--ctp-surface1)}
.content-page-card.active{border-color:var(--ctp-blue);background:rgba(137,180,250,.08)}
.content-page-card .cpc-page-icon{width:28px;text-align:center;font-size:14px}
.content-page-card .cpc-page-info{flex:1;min-width:0}
.content-page-card .cpc-page-title{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.content-page-card .cpc-page-brief{font-size:11px;color:var(--ctp-overlay0);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.content-page-card .cpc-page-status{font-size:10px;padding:2px 8px;border-radius:4px;font-weight:500;white-space:nowrap}
.cpc-page-status.not-generated{background:var(--ctp-surface1);color:var(--ctp-overlay0)}
.cpc-page-status.generating{background:rgba(249,226,175,.15);color:var(--ctp-yellow);animation:pulse 1s infinite}
.cpc-page-status.ready{background:rgba(166,227,161,.15);color:var(--ctp-green)}
.content-page-card .cpc-page-actions{display:flex;gap:4px}
.cpc-page-btn{background:none;border:1px solid var(--ctp-surface1);color:var(--ctp-overlay0);padding:4px 8px;border-radius:5px;cursor:pointer;font-size:11px;transition:all .15s}
.cpc-page-btn:hover{border-color:var(--ctp-blue);color:var(--ctp-blue)}
.check-inline{display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--ctp-subtext0);cursor:pointer;padding:4px 0}
.check-inline input{accent-color:var(--ctp-blue);cursor:pointer}
.check-inline span{user-select:none}
.btn-rewrite{background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);color:var(--ctp-subtext0);padding:4px 10px;border-radius:5px;cursor:pointer;font-size:11px;display:inline-flex;align-items:center;gap:4px;transition:all .15s;white-space:nowrap}
.btn-rewrite:hover{border-color:var(--ctp-blue);color:var(--ctp-blue)}
.content-tab.active{background:var(--ctp-surface0)!important;color:var(--ctp-text)!important}

/* Image Picker (Step 2) */
.image-picker-section{background:var(--ctp-surface0);border-radius:10px;padding:16px;margin-top:4px}
.image-picker-search{display:flex;gap:8px;margin-bottom:12px}
.image-picker-search input{flex:1;background:var(--ctp-mantle);border:1px solid var(--ctp-surface1);border-radius:6px;padding:8px 12px;color:var(--ctp-text);font:13px "Inter",sans-serif;outline:none;transition:border-color .2s}
.image-picker-search input:focus{border-color:var(--ctp-blue)}
.image-picker-search input::placeholder{color:var(--ctp-overlay0)}
.image-picker-search button{background:var(--ctp-blue);color:var(--ctp-crust);border:none;border-radius:6px;padding:8px 14px;cursor:pointer;font:500 13px "Inter",sans-serif;display:flex;align-items:center;gap:6px;transition:opacity .15s;white-space:nowrap}
.image-picker-search button:hover{opacity:.85}
.image-picker-search button:disabled{opacity:.5;cursor:not-allowed}
.image-picker-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;max-height:420px;overflow-y:auto;padding:2px}
.image-picker-grid::-webkit-scrollbar{width:6px}
.image-picker-grid::-webkit-scrollbar-thumb{background:var(--ctp-surface2);border-radius:3px}
.image-picker-item{position:relative;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:all .2s;aspect-ratio:3/2}
.image-picker-item:hover{border-color:var(--ctp-surface2);transform:translateY(-1px)}
.image-picker-item.selected{border-color:var(--ctp-blue);box-shadow:0 0 0 2px rgba(137,180,250,.25)}
.image-picker-item img{width:100%;height:100%;object-fit:cover;display:block}
.image-picker-item .ip-check{position:absolute;top:6px;right:6px;width:22px;height:22px;border-radius:50%;background:rgba(0,0,0,.5);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;transition:all .2s;font-size:11px;color:transparent}
.image-picker-item.selected .ip-check{background:var(--ctp-blue);border-color:var(--ctp-blue);color:var(--ctp-crust)}
.image-picker-item .ip-photographer{position:absolute;bottom:0;left:0;right:0;padding:3px 6px;background:linear-gradient(transparent,rgba(0,0,0,.7));font-size:9px;color:rgba(255,255,255,.8);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:0;transition:opacity .2s}
.image-picker-item:hover .ip-photographer{opacity:1}
.image-picker-footer{display:flex;align-items:center;justify-content:space-between;margin-top:10px;font-size:11px;color:var(--ctp-overlay0)}
.image-picker-footer a{color:var(--ctp-blue);text-decoration:none}
.image-picker-footer a:hover{text-decoration:underline}
.image-picker-status{display:flex;align-items:center;gap:6px}
.image-picker-status .count{font-weight:600;color:var(--ctp-text)}
.image-picker-status .count.warn{color:var(--ctp-yellow)}
.image-picker-status .count.ok{color:var(--ctp-green)}
.image-picker-load-more{display:flex;justify-content:center;padding:8px}
.image-picker-load-more button{background:var(--ctp-surface1);color:var(--ctp-subtext0);border:none;border-radius:6px;padding:6px 16px;cursor:pointer;font-size:12px;transition:all .15s}
.image-picker-load-more button:hover{background:var(--ctp-surface2);color:var(--ctp-text)}
.ip-empty{text-align:center;padding:32px;color:var(--ctp-overlay0);font-size:13px}
.ip-loading{text-align:center;padding:24px;color:var(--ctp-blue);font-size:13px}
.ip-loading .spinner{display:inline-block;width:16px;height:16px;border:2px solid var(--ctp-surface1);border-top-color:var(--ctp-blue);border-radius:50%;animation:spin .6s linear infinite;margin-right:6px;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}

/* Business Profile */
.biz-profile{margin-top:4px}
.biz-profile .biz-section{margin-bottom:16px;padding:16px;background:var(--ctp-surface0);border-radius:10px;border:1px solid var(--ctp-surface1)}
.biz-profile .biz-section-head{display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;user-select:none}
.biz-profile .biz-section-head i.section-icon{width:20px;text-align:center;color:var(--ctp-blue);font-size:14px}
.biz-profile .biz-section-head .biz-section-title{font-size:13px;font-weight:600;color:var(--ctp-subtext1);flex:1}
.biz-profile .biz-section-head .biz-section-badge{font-size:9px;padding:2px 8px;border-radius:4px;background:rgba(137,180,250,.1);color:var(--ctp-blue);text-transform:uppercase;letter-spacing:.04em}
.biz-profile .biz-section-head .biz-collapse-icon{color:var(--ctp-overlay0);font-size:11px;transition:transform .2s}
.biz-profile .biz-section.collapsed .biz-section-body{display:none}
.biz-profile .biz-section.collapsed .biz-collapse-icon{transform:rotate(-90deg)}
.biz-profile .biz-section-body{display:block}
.biz-profile .biz-hint{font-size:11px;color:var(--ctp-overlay0);margin-bottom:10px;line-height:1.4}

/* Repeater (services, team, testimonials) */
.biz-repeater{display:flex;flex-direction:column;gap:8px}
.biz-repeater-item{display:flex;gap:8px;align-items:flex-start;padding:10px 12px;background:var(--ctp-mantle);border-radius:8px;border:1px solid var(--ctp-surface0);position:relative}
.biz-repeater-item .biz-ri-fields{flex:1;display:flex;flex-direction:column;gap:6px}
.biz-repeater-item .biz-ri-fields input,
.biz-repeater-item .biz-ri-fields textarea{width:100%;background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:6px;padding:7px 10px;color:var(--ctp-text);font:inherit;font-size:13px}
.biz-repeater-item .biz-ri-fields input:focus,
.biz-repeater-item .biz-ri-fields textarea:focus{outline:none;border-color:var(--ctp-blue)}
.biz-repeater-item .biz-ri-fields textarea{min-height:40px;resize:vertical;line-height:1.5}
.biz-repeater-item .biz-ri-fields .biz-ri-row{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.biz-repeater-item .biz-ri-remove{background:none;border:none;color:var(--ctp-surface2);cursor:pointer;font-size:13px;padding:4px;transition:color .15s;flex-shrink:0;margin-top:2px}
.biz-repeater-item .biz-ri-remove:hover{color:var(--ctp-red)}
.biz-repeater-add{display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;border:1px dashed var(--ctp-surface1);background:transparent;color:var(--ctp-overlay0);cursor:pointer;font-size:12px;font-weight:500;transition:all .15s;width:100%}
.biz-repeater-add:hover{border-color:var(--ctp-blue);color:var(--ctp-blue);background:rgba(137,180,250,.04)}

/* Opening hours grid */
.biz-hours-grid{display:flex;flex-direction:column;gap:4px}
.biz-hours-row{display:grid;grid-template-columns:80px 1fr 16px 1fr auto;gap:4px;align-items:center;font-size:12px}
.biz-hours-row .biz-day{font-weight:500;color:var(--ctp-subtext0)}
.biz-hours-row .biz-dash{text-align:center;color:var(--ctp-overlay0)}
.biz-hours-row input[type="text"]{background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:5px;padding:5px 4px;color:var(--ctp-text);font:inherit;font-size:12px;text-align:center;min-width:0}
.biz-hours-row input[type="text"]:focus{outline:none;border-color:var(--ctp-blue)}
.biz-hours-row .biz-closed-toggle{display:flex;align-items:center;gap:3px;font-size:10px;color:var(--ctp-overlay0);cursor:pointer;user-select:none;white-space:nowrap}
.biz-hours-row .biz-closed-toggle input{accent-color:var(--ctp-blue);cursor:pointer}
.biz-hours-row.is-closed input[type="text"]{opacity:.3;pointer-events:none}

/* Industry-specific sections */
.biz-industry-section{display:none}
.biz-industry-section.visible{display:block}

/* Completion indicator */
.biz-completion{display:flex;align-items:center;gap:8px;padding:10px 14px;background:var(--ctp-mantle);border-radius:8px;margin-bottom:12px;font-size:12px}
.biz-completion-bar{flex:1;height:4px;background:var(--ctp-surface0);border-radius:2px;overflow:hidden}
.biz-completion-fill{height:100%;background:var(--ctp-blue);border-radius:2px;transition:width .3s}
.biz-completion-text{color:var(--ctp-overlay0);white-space:nowrap}
.biz-completion-text strong{color:var(--ctp-blue)}

@media(max-width:900px){
    .panel.split{width:100%;min-width:0;max-width:100%;border:0}
    .preview-panel{display:none}
    .presets-grid{grid-template-columns:1fr 1fr}
    .image-picker-grid{grid-template-columns:repeat(2,1fr)}
    .biz-hours-row{grid-template-columns:70px 1fr 16px 1fr 50px}
    .biz-repeater-item .biz-ri-fields .biz-ri-row{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:16px">
        <a href="/admin"><i class="fas fa-arrow-left"></i> Admin</a>
        <div class="topbar-title"><img src="/assets/images/jessie-logo.svg" alt="Jessie" width="28" height="28" style="vertical-align:middle;margin-right:8px">AI Theme Builder <span class="badge">Wizard</span></div>
    </div>
    <div style="font-size:12px;color:var(--ctp-subtext0)"><?= esc($username) ?></div>
</div>

<!-- Step indicator -->
<div class="steps-bar">
    <div class="step-item active" data-step="1"><div class="step-num">1</div><span class="step-label">Configure</span></div>
    <div class="step-connector"></div>
    <div class="step-item" data-step="2"><div class="step-num">2</div><span class="step-label">Content</span></div>
    <div class="step-connector"></div>
    <div class="step-item" data-step="3"><div class="step-num">3</div><span class="step-label">Design</span></div>
    <div class="step-connector"></div>
    <div class="step-item" data-step="4"><div class="step-num">4</div><span class="step-label">Build</span></div>
    <div class="step-connector"></div>
    <div class="step-item" data-step="5"><div class="step-num">5</div><span class="step-label">Review</span></div>
</div>

<!-- ═══════════════════════════════════════════
     STEP 1: Configure
     ═══════════════════════════════════════════ -->
<div class="main step-content" id="step1Content">
<div class="panel">

    <h2 style="margin-bottom:4px;font-size:20px;font-weight:600">Configure Theme</h2>
    <p style="font-size:13px;color:var(--ctp-overlay1);margin-bottom:20px">Describe your project, configure design options, then build. AI generates everything.</p>

    <!-- Quick Start — Industry Kits -->
    <div class="field">
        <div class="section-head"><span class="section-title">Quick Start</span><span class="section-hint">one click fills everything</span></div>
        <div style="display:flex;flex-wrap:wrap;gap:6px" id="industryKits"></div>
    </div>

    <div class="divider"></div>

    <!-- Prompt -->
    <div class="field">
        <label class="label">Describe your website</label>
        <textarea class="input" id="wPrompt" placeholder="e.g. A modern law firm in London specializing in corporate law. Dark and professional aesthetic with gold accents..."></textarea>
        <div class="prompt-suggestions" id="promptSuggestions"></div>
    </div>

    <!-- Templates (collapsible) -->
    <details class="templates-collapse">
        <summary>Use a template</summary>
        <div class="presets-grid" id="presetsGrid" style="margin-top:10px"></div>
    </details>

    <!-- Industry -->
    <div class="row-2">
        <div class="field">
            <label class="label">Industry</label>
            <select class="input" id="wIndustry">
                <option value="">— Select —</option>
                <optgroup label="Food & Hospitality">
                    <option value="restaurant">Restaurant</option>
                    <option value="cafe">Café / Coffee Shop</option>
                    <option value="bar">Bar / Pub / Nightclub</option>
                    <option value="bakery">Bakery / Patisserie</option>
                    <option value="foodtruck">Food Truck</option>
                    <option value="catering">Catering</option>
                    <option value="hotel">Hotel / B&B</option>
                    <option value="resort">Resort / Spa Hotel</option>
                    <option value="winery">Winery / Brewery</option>
                </optgroup>
                <optgroup label="Tech & Digital">
                    <option value="saas">SaaS</option>
                    <option value="startup">Startup</option>
                    <option value="ai">AI / Machine Learning</option>
                    <option value="app">Mobile App</option>
                    <option value="crypto">Crypto / Web3</option>
                    <option value="cybersecurity">Cybersecurity</option>
                    <option value="devtools">Developer Tools</option>
                    <option value="hosting">Hosting / Cloud</option>
                    <option value="itsupport">IT Support / MSP</option>
                    <option value="gamedev">Game Studio</option>
                </optgroup>
                <optgroup label="Creative & Media">
                    <option value="portfolio">Portfolio (General)</option>
                    <option value="photography">Photography</option>
                    <option value="videography">Videography / Film</option>
                    <option value="agency">Creative Agency</option>
                    <option value="design">Graphic / Web Design</option>
                    <option value="music">Music / Band / DJ</option>
                    <option value="art">Art / Gallery</option>
                    <option value="architecture">Architecture</option>
                    <option value="interior">Interior Design</option>
                    <option value="animation">Animation / Motion</option>
                    <option value="tattoo">Tattoo Studio</option>
                </optgroup>
                <optgroup label="Content & Publishing">
                    <option value="blog">Blog / Personal</option>
                    <option value="magazine">Online Magazine</option>
                    <option value="podcast">Podcast</option>
                    <option value="news">News Portal</option>
                    <option value="newsletter">Newsletter / Substack</option>
                    <option value="author">Author / Writer</option>
                    <option value="influencer">Influencer / Creator</option>
                </optgroup>
                <optgroup label="Commerce & Retail">
                    <option value="ecommerce">E-commerce (General)</option>
                    <option value="fashion">Fashion / Clothing</option>
                    <option value="jewelry">Jewelry / Luxury Goods</option>
                    <option value="beauty">Beauty / Cosmetics</option>
                    <option value="furniture">Furniture / Home Decor</option>
                    <option value="electronics">Electronics / Gadgets</option>
                    <option value="bookshop">Bookshop</option>
                    <option value="grocery">Grocery / Organic</option>
                    <option value="pets">Pet Shop / Services</option>
                    <option value="florist">Florist / Plant Shop</option>
                    <option value="marketplace">Marketplace / Multi-Vendor</option>
                </optgroup>
                <optgroup label="Professional Services">
                    <option value="law">Law Firm</option>
                    <option value="finance">Finance / Accounting</option>
                    <option value="insurance">Insurance</option>
                    <option value="consulting">Consulting</option>
                    <option value="marketing">Marketing Agency</option>
                    <option value="recruiting">Recruiting / HR</option>
                    <option value="translation">Translation / Localization</option>
                    <option value="coaching">Life / Business Coach</option>
                    <option value="realestate">Real Estate</option>
                    <option value="propertymanagement">Property Management</option>
                </optgroup>
                <optgroup label="Health & Wellness">
                    <option value="medical">Medical / Clinic</option>
                    <option value="dental">Dental Practice</option>
                    <option value="veterinary">Veterinary</option>
                    <option value="pharmacy">Pharmacy</option>
                    <option value="therapy">Therapy / Counseling</option>
                    <option value="spa">Spa / Wellness Center</option>
                    <option value="fitness">Gym / Fitness</option>
                    <option value="yoga">Yoga / Pilates</option>
                    <option value="nutrition">Nutrition / Dietitian</option>
                    <option value="physiotherapy">Physiotherapy</option>
                    <option value="mentalhealth">Mental Health</option>
                </optgroup>
                <optgroup label="Education & Training">
                    <option value="education">School / University</option>
                    <option value="onlinecourse">Online Courses / LMS</option>
                    <option value="tutoring">Tutoring</option>
                    <option value="language">Language School</option>
                    <option value="driving">Driving School</option>
                    <option value="childcare">Childcare / Nursery</option>
                    <option value="library">Library</option>
                    <option value="training">Corporate Training</option>
                </optgroup>
                <optgroup label="Construction & Trade">
                    <option value="construction">Construction / Builder</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="electrical">Electrical</option>
                    <option value="hvac">HVAC</option>
                    <option value="roofing">Roofing</option>
                    <option value="painting">Painting / Decorating</option>
                    <option value="landscaping">Landscaping / Garden</option>
                    <option value="cleaning">Cleaning Services</option>
                    <option value="moving">Moving / Removals</option>
                    <option value="handyman">Handyman</option>
                    <option value="solar">Solar / Renewable Energy</option>
                </optgroup>
                <optgroup label="Automotive & Transport">
                    <option value="automotive">Car Dealership</option>
                    <option value="mechanic">Auto Repair / Mechanic</option>
                    <option value="carwash">Car Wash / Detailing</option>
                    <option value="taxi">Taxi / Rideshare</option>
                    <option value="trucking">Trucking / Logistics</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="boating">Boating / Marine</option>
                </optgroup>
                <optgroup label="Events & Entertainment">
                    <option value="events">Events / Conference</option>
                    <option value="wedding">Wedding / Planner</option>
                    <option value="party">Party / Entertainment</option>
                    <option value="venue">Venue / Hall Rental</option>
                    <option value="theater">Theater / Performing Arts</option>
                    <option value="festival">Festival</option>
                    <option value="cinema">Cinema</option>
                    <option value="escape">Escape Room / Entertainment</option>
                </optgroup>
                <optgroup label="Travel & Leisure">
                    <option value="travel">Travel Agency</option>
                    <option value="tourism">Tourism / Tours</option>
                    <option value="camping">Camping / Outdoors</option>
                    <option value="skiing">Ski Resort</option>
                    <option value="diving">Diving / Water Sports</option>
                    <option value="golf">Golf Club</option>
                    <option value="sports">Sports Club / Team</option>
                    <option value="marina">Marina / Yacht Club</option>
                </optgroup>
                <optgroup label="Community & Non-Profit">
                    <option value="nonprofit">Non-Profit / Charity</option>
                    <option value="church">Church / Religious</option>
                    <option value="political">Political Campaign</option>
                    <option value="community">Community Center</option>
                    <option value="association">Association / Club</option>
                    <option value="volunteer">Volunteer Organization</option>
                </optgroup>
                <optgroup label="Government & Public">
                    <option value="government">Government / Municipal</option>
                    <option value="police">Police / Emergency</option>
                    <option value="military">Military / Defense</option>
                    <option value="embassy">Embassy / Consulate</option>
                </optgroup>
                <optgroup label="Other">
                    <option value="personal">Personal Website</option>
                    <option value="resume">Resume / CV</option>
                    <option value="wiki">Wiki / Documentation</option>
                    <option value="directory">Directory / Listing</option>
                    <option value="saas-landing">Landing Page</option>
                    <option value="comingsoon">Coming Soon</option>
                    <option value="memorial">Memorial / Tribute</option>
                    <option value="other">Other</option>
                </optgroup>
            </select>
        </div>
        <div class="field">
            <label class="label">Content Tone</label>
            <select class="input" id="wTone">
                <option value="professional">Professional</option>
                <option value="friendly">Friendly</option>
                <option value="casual">Casual</option>
                <option value="formal">Formal</option>
                <option value="witty">Witty</option>
                <option value="luxurious">Luxurious</option>
            </select>
        </div>
    </div>

    <div class="row-2">
        <div class="field">
            <div class="section-head"><span class="section-title">Style</span></div>
            <div class="chips" id="wStyle">
                <div class="chip selected" data-v="minimalist">Minimalist</div>
                <div class="chip" data-v="bold">Bold</div>
                <div class="chip" data-v="elegant">Elegant</div>
                <div class="chip" data-v="playful">Playful</div>
                <div class="chip" data-v="corporate">Corporate</div>
                <div class="chip" data-v="brutalist">Brutalist</div>
                <div class="chip" data-v="futuristic">Futuristic</div>
                <div class="chip" data-v="organic">Organic</div>
                <div class="chip" data-v="editorial">Editorial</div>
            </div>
        </div>
        <div class="field">
            <div class="section-head"><span class="section-title">Mood</span></div>
            <div class="chips" id="wMood">
                <div class="chip selected" data-v="dark">Dark</div>
                <div class="chip" data-v="light">Light</div>
                <div class="chip" data-v="colorful">Colorful</div>
                <div class="chip" data-v="warm">Warm</div>
                <div class="chip" data-v="cool">Cool</div>
                <div class="chip" data-v="luxury">Luxury</div>
                <div class="chip" data-v="earth">Earth</div>
                <div class="chip" data-v="pastel">Pastel</div>
                <div class="chip" data-v="neon">Neon</div>
                <div class="chip" data-v="monochrome">Monochrome</div>
            </div>
        </div>
    </div>

    <!-- Model + Language -->
    <div class="row-2">
        <div class="field">
            <label class="label">AI Model</label>
            <select class="input" id="wModel">
            <?php foreach ($aiModels ?? [] as $m): ?>
                <option value="<?= esc($m['provider'] ?? '') ?>:<?= esc($m['model'] ?? '') ?>" data-tier="<?= esc($m['tier'] ?? 'recommended') ?>"<?= ($m['isDefault'] ?? false) ? ' selected' : '' ?>><?= esc($m['name'] ?? $m['model'] ?? 'Unknown') ?><?= ($m['tier'] ?? '') === 'budget' ? ' ⚠️' : (($m['tier'] ?? '') === 'premium' ? ' ⭐' : '') ?></option>
            <?php endforeach; ?>
            <?php if (empty($aiModels)): ?>
                <option value="anthropic:claude-sonnet-4-20250514">Claude Sonnet 4</option>
                <option value="anthropic:claude-opus-4-0-20250515" selected>Claude Opus 4</option>
                <option value="openai:gpt-4o">GPT-4o</option>
                <option value="deepseek:deepseek-chat">DeepSeek V3</option>
            <?php endif; ?>
            </select>
            <div id="modelWarning" class="model-warning hidden" style="margin-top:6px;padding:6px 10px;border-radius:8px;background:rgba(249,115,22,0.12);color:#f59e0b;font-size:12px;line-height:1.4">
                ⚠️ Budget models have limited output (~8k tokens). Complex themes with many sections may be incomplete. Use Claude or GPT-4 for best results.
            </div>
        </div>
        <div class="field">
            <label class="label">Creativity</label>
            <select class="input" id="wCreativity">
                <option value="low">🎯 Precise — safe, follows instructions closely</option>
                <option value="medium" selected>⚖️ Balanced — good mix of quality and variety</option>
                <option value="high">🎨 Experimental — bold, more varied results</option>
            </select>
            <div style="margin-top:4px;font-size:11px;opacity:0.5">Lower = more consistent quality. Higher = more unique but less predictable.</div>
        </div>
    </div>
    <div class="row-2">
        <div class="field">
            <label class="label">Language</label>
            <select class="input" id="wLang">
                <option value="English" selected>English</option>
                <option value="Polish">Polski</option>
                <option value="German">Deutsch</option>
                <option value="French">Français</option>
                <option value="Spanish">Español</option>
                <option value="Italian">Italiano</option>
                <option value="Portuguese">Português</option>
                <option value="Dutch">Nederlands</option>
                <option value="Swedish">Svenska</option>
                <option value="Norwegian">Norsk</option>
                <option value="Danish">Dansk</option>
                <option value="Finnish">Suomi</option>
                <option value="Czech">Čeština</option>
                <option value="Slovak">Slovenčina</option>
                <option value="Hungarian">Magyar</option>
                <option value="Romanian">Română</option>
                <option value="Bulgarian">Български</option>
                <option value="Croatian">Hrvatski</option>
                <option value="Serbian">Srpski</option>
                <option value="Slovenian">Slovenščina</option>
                <option value="Ukrainian">Українська</option>
                <option value="Russian">Русский</option>
                <option value="Greek">Ελληνικά</option>
                <option value="Turkish">Türkçe</option>
                <option value="Arabic">العربية</option>
                <option value="Hebrew">עברית</option>
                <option value="Japanese">日本語</option>
                <option value="Korean">한국어</option>
                <option value="Chinese">中文</option>
                <option value="Thai">ไทย</option>
                <option value="Vietnamese">Tiếng Việt</option>
                <option value="Indonesian">Bahasa Indonesia</option>
                <option value="Malay">Bahasa Melayu</option>
                <option value="Hindi">हिन्दी</option>
            </select>
        </div>
    </div>

    <!-- Design Inspiration URL -->
    <div class="field" style="margin-top:8px">
        <label class="label"><i class="fas fa-magic" style="color:var(--ctp-mauve)"></i> Design Inspiration <span style="font-weight:400;opacity:.5">(optional)</span></label>
        <div style="display:flex;gap:6px">
            <input class="input" id="wInspirationUrl" placeholder="Paste a website URL you like — AI will analyze its style" style="flex:1">
            <button class="btn btn-secondary" id="btnAnalyzeUrl" style="white-space:nowrap;padding:6px 12px;font-size:12px" title="Analyze design">
                <i class="fas fa-search"></i> Analyze
            </button>
        </div>
        <div id="inspirationResult" class="hidden" style="margin-top:8px;padding:10px 12px;background:var(--ctp-surface0);border-radius:8px;font-size:12px;border:1px solid var(--ctp-surface1)"></div>
    </div>

    <!-- Provider Health -->
    <div class="provider-health hidden" id="providerHealth"></div>

    <div class="btn-row">
        <button class="btn btn-surprise" id="btnSurpriseMe"><i class="fas fa-dice"></i> Surprise Me</button>
        <button class="btn btn-primary" id="btnGenerateBrief">
            <span class="btn-label">Generate Brief</span>
            <span class="spinner"></span>
        </button>
    </div>

    <!-- Brief Review (hidden until generated) -->
    <div id="briefReview" class="hidden" style="margin-top:24px">
        <!-- Quick brief confirmation -->
        <div style="padding:12px 16px;background:rgba(166,227,161,.08);border:1px solid rgba(166,227,161,.2);border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
            <i class="fas fa-check-circle" style="color:var(--ctp-green);font-size:16px"></i>
            <div>
                <div style="font-size:14px;font-weight:600" id="briefConfirmName">Brief Generated</div>
                <div style="font-size:12px;color:var(--ctp-subtext0)" id="briefConfirmMeta">Design brief ready — colors, fonts, and sections selected</div>
            </div>
        </div>

        <!-- Brief Review Panel — colors, fonts, sections (quick view) -->
        <div id="briefReviewPanel3"></div>

        <div class="divider"></div>
        <div class="section-head"><span class="section-title">Content Plan</span><span class="section-hint">AI-generated outline for each page</span></div>
        <div id="contentPlanCards"></div>
        <div id="contentPlanLoading" class="hidden" style="text-align:center;padding:20px;color:var(--ctp-overlay0);font-size:13px">
            <i class="fas fa-spinner fa-spin"></i> Generating content plan...
        </div>

        <div class="btn-row" style="margin-top:12px">
            <button class="btn btn-secondary" id="btnRegenBrief"><i class="fas fa-redo"></i> Regenerate Brief</button>
        </div>

    </div><!-- /briefReview -->

    <div class="divider"></div>

    <!-- Pages Selection -->
    <div class="section-head"><span class="section-title">Pages</span><span class="section-hint">select which pages to create</span></div>
    <div class="check-list" id="pagesList"></div>

    <div class="divider"></div>

    <div class="btn-row">
        <button class="btn btn-primary" id="btnStep1Next" disabled>
            <span class="btn-label">Continue to Content <i class="fas fa-arrow-right"></i></span>
        </button>
        <div id="step1NextHint" style="font-size:11px;color:var(--ctp-overlay0);align-self:center">Generate a brief first to continue</div>
    </div>

</div>
</div>

<!-- ═══════════════════════════════════════════
     STEP 2: Content Studio (generate & edit content per page)
     ═══════════════════════════════════════════ -->
<div class="main step-content hidden" id="step2Content">
<div class="panel split">
    <h2 style="margin-bottom:4px">Content Studio</h2>
    <p style="font-size:13px;color:var(--ctp-subtext0);margin-bottom:16px">Generate and refine content for each page. AI creates SEO-optimized content based on your plan.</p>

    <!-- Page cards list -->
    <div id="contentPagesList"></div>

    <div class="divider" style="margin:24px 0"></div>

    <!-- ═══ Business Profile ═══ -->
    <div class="section-head"><span class="section-title">Business Profile</span><span class="section-hint">the more you fill in, the better your website content</span></div>

    <!-- Completion indicator -->
    <div class="biz-completion">
        <div class="biz-completion-bar"><div class="biz-completion-fill" id="bizCompletionFill" style="width:0%"></div></div>
        <div class="biz-completion-text"><strong id="bizCompletionPct">0%</strong> complete</div>
    </div>

    <div class="biz-profile" id="bizProfile">

        <!-- ─── Basic Info (always visible) ─── -->
        <div class="biz-section" id="bizSectionBasic">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-building section-icon"></i>
                <span class="biz-section-title">Basic Information</span>
                <span class="biz-section-badge">Required</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Business Name</label><input class="input biz-field" id="bizName" placeholder="My Company" data-biz="name"></div>
                <div class="field"><label class="label">Describe your business in 2-3 sentences</label><textarea class="input biz-field" id="bizDescription" rows="3" placeholder="We are a family-run bakery in the heart of London, specialising in artisan sourdough and French pastries since 1998..." data-biz="description"></textarea></div>
                <div class="row-2">
                    <div class="field"><label class="label">Tagline / Slogan</label><input class="input biz-field" id="bizTagline" placeholder="Quality you can taste" data-biz="tagline"></div>
                    <div class="field"><label class="label">Years in Business</label><input class="input biz-field" id="bizYears" placeholder="15" type="number" min="0" data-biz="years"></div>
                </div>
                <div class="field"><label class="label">What makes you unique? (one per line)</label><textarea class="input biz-field" id="bizUsps" rows="3" placeholder="Award-winning sourdough recipe&#10;All ingredients locally sourced&#10;Open kitchen — watch your bread being made" data-biz="usps"></textarea></div>
                <div class="field"><label class="label">Who are your ideal customers?</label><input class="input biz-field" id="bizAudience" placeholder="Health-conscious foodies, local families, café owners" data-biz="audience"></div>
            </div>
        </div>

        <!-- ─── Contact & Social ─── -->
        <div class="biz-section" id="bizSectionContact">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-address-card section-icon"></i>
                <span class="biz-section-title">Contact & Social</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="row-2">
                    <div class="field"><label class="label">Phone</label><input class="input biz-field" id="bizPhone" placeholder="+44 123 456 7890" data-biz="phone"></div>
                    <div class="field"><label class="label">Email</label><input class="input biz-field" id="bizEmail" placeholder="info@example.com" data-biz="email"></div>
                </div>
                <div class="field"><label class="label">Address</label><input class="input biz-field" id="bizAddress" placeholder="123 High Street, London" data-biz="address"></div>
                <div class="field"><label class="label">Website (current, if any)</label><input class="input biz-field" id="bizWebsite" placeholder="https://mycompany.com" data-biz="website"></div>
                <div class="row-2">
                    <div class="field"><label class="label">Facebook</label><input class="input biz-field" id="bizFb" placeholder="https://facebook.com/..." data-biz="facebook"></div>
                    <div class="field"><label class="label">Instagram</label><input class="input biz-field" id="bizIg" placeholder="https://instagram.com/..." data-biz="instagram"></div>
                </div>
                <div class="row-2">
                    <div class="field"><label class="label">Twitter / X</label><input class="input biz-field" id="bizTwitter" placeholder="https://x.com/..." data-biz="twitter"></div>
                    <div class="field"><label class="label">LinkedIn</label><input class="input biz-field" id="bizLinkedin" placeholder="https://linkedin.com/company/..." data-biz="linkedin"></div>
                </div>
                <div class="row-2">
                    <div class="field"><label class="label">YouTube</label><input class="input biz-field" id="bizYoutube" placeholder="https://youtube.com/@..." data-biz="youtube"></div>
                    <div class="field"><label class="label">TikTok</label><input class="input biz-field" id="bizTiktok" placeholder="https://tiktok.com/@..." data-biz="tiktok"></div>
                </div>
            </div>
        </div>

        <!-- ─── Services / Products (industry-dynamic) ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionServices" data-biz-groups="services,creative,health,legal,professional,construction,education,technology,automotive,beauty,finance,realestate,events,fitness,pets,travel,logistics,food">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-concierge-bell section-icon"></i>
                <span class="biz-section-title">Services & Products</span>
                <span class="biz-section-badge">Recommended</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <p class="biz-hint">List your main services or products. AI will use these to create your Services page and feature sections.</p>
                <div class="biz-repeater" id="bizServicesRepeater">
                    <!-- items added dynamically -->
                </div>
                <button class="biz-repeater-add" onclick="addBizRepeaterItem('bizServicesRepeater','service')"><i class="fas fa-plus"></i> Add service or product</button>
            </div>
        </div>

        <!-- ─── Team Members ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionTeam" data-biz-groups="services,creative,health,legal,professional,construction,education,technology,finance,realestate,events">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-users section-icon"></i>
                <span class="biz-section-title">Team Members</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <p class="biz-hint">Add key team members. AI will feature them on About and Team pages.</p>
                <div class="biz-repeater" id="bizTeamRepeater">
                    <!-- items added dynamically -->
                </div>
                <button class="biz-repeater-add" onclick="addBizRepeaterItem('bizTeamRepeater','team')"><i class="fas fa-plus"></i> Add team member</button>
            </div>
        </div>

        <!-- ─── Testimonials / Reviews ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionTestimonials" data-biz-groups="services,creative,health,legal,professional,construction,education,technology,automotive,beauty,finance,realestate,events,fitness,pets,food,travel,logistics,retail">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-star section-icon"></i>
                <span class="biz-section-title">Testimonials & Reviews</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <p class="biz-hint">Real testimonials make your website much more credible. AI will use these instead of generating fake ones.</p>
                <div class="biz-repeater" id="bizTestimonialsRepeater">
                    <!-- items added dynamically -->
                </div>
                <button class="biz-repeater-add" onclick="addBizRepeaterItem('bizTestimonialsRepeater','testimonial')"><i class="fas fa-plus"></i> Add testimonial</button>
            </div>
        </div>

        <!-- ─── Opening Hours ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionHours" data-biz-groups="food,retail,beauty,health,fitness,automotive,pets,services">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-clock section-icon"></i>
                <span class="biz-section-title">Opening Hours</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="biz-hours-grid" id="bizHoursGrid">
                    <div class="biz-hours-row" data-day="monday"><span class="biz-day">Monday</span><input type="text" class="biz-hour-open" placeholder="09:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="17:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="tuesday"><span class="biz-day">Tuesday</span><input type="text" class="biz-hour-open" placeholder="09:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="17:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="wednesday"><span class="biz-day">Wednesday</span><input type="text" class="biz-hour-open" placeholder="09:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="17:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="thursday"><span class="biz-day">Thursday</span><input type="text" class="biz-hour-open" placeholder="09:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="17:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="friday"><span class="biz-day">Friday</span><input type="text" class="biz-hour-open" placeholder="09:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="17:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="saturday"><span class="biz-day">Saturday</span><input type="text" class="biz-hour-open" placeholder="10:00"><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder="16:00"><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed"> Closed</label></div>
                    <div class="biz-hours-row" data-day="sunday"><span class="biz-day">Sunday</span><input type="text" class="biz-hour-open" placeholder=""><span class="biz-dash">–</span><input type="text" class="biz-hour-close" placeholder=""><label class="biz-closed-toggle"><input type="checkbox" class="biz-hour-closed" checked> Closed</label></div>
                </div>
            </div>
        </div>

        <!-- ─── Food & Restaurant specific ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionFood" data-biz-groups="food">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-utensils section-icon"></i>
                <span class="biz-section-title">Restaurant & Food Details</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="row-2">
                    <div class="field"><label class="label">Cuisine Type</label><input class="input biz-field" id="bizCuisine" placeholder="Italian, French, Japanese..." data-biz="cuisine"></div>
                    <div class="field"><label class="label">Price Range</label>
                        <select class="input biz-field" id="bizPriceRange" data-biz="price_range">
                            <option value="">Select...</option>
                            <option value="budget">£ — Budget-friendly</option>
                            <option value="moderate">££ — Moderate</option>
                            <option value="upscale">£££ — Upscale</option>
                            <option value="fine-dining">££££ — Fine Dining</option>
                        </select>
                    </div>
                </div>
                <div class="field"><label class="label">Menu Highlights / Signature Dishes</label><textarea class="input biz-field" id="bizMenuHighlights" rows="3" placeholder="Our famous truffle risotto&#10;Dry-aged ribeye steak&#10;Homemade tiramisu" data-biz="menu_highlights"></textarea></div>
                <div class="row-2">
                    <div class="field"><label class="label">Reservations</label>
                        <select class="input biz-field" id="bizReservations" data-biz="reservations">
                            <option value="">Select...</option>
                            <option value="required">Required</option>
                            <option value="recommended">Recommended</option>
                            <option value="walk-in">Walk-in only</option>
                            <option value="both">Both accepted</option>
                        </select>
                    </div>
                    <div class="field"><label class="label">Seating Capacity</label><input class="input biz-field" id="bizSeating" placeholder="80 covers" data-biz="seating"></div>
                </div>
            </div>
        </div>

        <!-- ─── Healthcare specific ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionHealth" data-biz-groups="health">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-heartbeat section-icon"></i>
                <span class="biz-section-title">Healthcare Details</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Specialties</label><textarea class="input biz-field" id="bizSpecialties" rows="3" placeholder="General dentistry&#10;Cosmetic procedures&#10;Orthodontics" data-biz="specialties"></textarea></div>
                <div class="field"><label class="label">Insurance / NHS</label><input class="input biz-field" id="bizInsurance" placeholder="NHS accepted, all major insurers" data-biz="insurance"></div>
                <div class="field"><label class="label">Emergency services?</label><input class="input biz-field" id="bizEmergency" placeholder="24/7 emergency dental care available" data-biz="emergency"></div>
            </div>
        </div>

        <!-- ─── Creative / Portfolio specific ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionCreative" data-biz-groups="creative">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-palette section-icon"></i>
                <span class="biz-section-title">Creative & Portfolio Details</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Specialisation</label><input class="input biz-field" id="bizCreativeSpec" placeholder="Wedding photography, brand identity, UI/UX design..." data-biz="creative_specialisation"></div>
                <div class="field"><label class="label">Notable Clients / Projects</label><textarea class="input biz-field" id="bizNotableClients" rows="2" placeholder="Vogue UK, Nike London, BBC Studios" data-biz="notable_clients"></textarea></div>
                <div class="field"><label class="label">Awards & Recognition</label><textarea class="input biz-field" id="bizAwards" rows="2" placeholder="Wedding Photographer of the Year 2024&#10;Design Week Award finalist" data-biz="awards"></textarea></div>
            </div>
        </div>

        <!-- ─── Legal / Professional specific ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionLegal" data-biz-groups="legal,professional,finance">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-gavel section-icon"></i>
                <span class="biz-section-title">Professional Details</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Practice Areas / Expertise</label><textarea class="input biz-field" id="bizPracticeAreas" rows="3" placeholder="Family law&#10;Criminal defence&#10;Immigration" data-biz="practice_areas"></textarea></div>
                <div class="field"><label class="label">Accreditations & Memberships</label><textarea class="input biz-field" id="bizAccreditations" rows="2" placeholder="Law Society, SRA regulated, Legal 500 recommended" data-biz="accreditations"></textarea></div>
                <div class="field"><label class="label">Free Consultation?</label><input class="input biz-field" id="bizFreeConsult" placeholder="Yes — 30-minute free initial consultation" data-biz="free_consultation"></div>
            </div>
        </div>

        <!-- ─── Retail / E-commerce specific ─── -->
        <div class="biz-section biz-industry-section" id="bizSectionRetail" data-biz-groups="retail">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-shopping-bag section-icon"></i>
                <span class="biz-section-title">Retail & E-commerce Details</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Main Product Categories</label><textarea class="input biz-field" id="bizProducts" rows="3" placeholder="Organic skincare&#10;Hand-poured candles&#10;Linen homeware" data-biz="products"></textarea></div>
                <div class="row-2">
                    <div class="field"><label class="label">Shipping</label><input class="input biz-field" id="bizShipping" placeholder="Free UK delivery over £50" data-biz="shipping"></div>
                    <div class="field"><label class="label">Returns Policy</label><input class="input biz-field" id="bizReturns" placeholder="30-day hassle-free returns" data-biz="returns"></div>
                </div>
            </div>
        </div>

        <!-- ─── Extra Details (always visible) ─── -->
        <div class="biz-section" id="bizSectionExtra">
            <div class="biz-section-head" onclick="toggleBizSection(this)">
                <i class="fas fa-plus-circle section-icon"></i>
                <span class="biz-section-title">Additional Details</span>
                <span class="biz-section-badge">Optional</span>
                <i class="fas fa-chevron-down biz-collapse-icon"></i>
            </div>
            <div class="biz-section-body">
                <div class="field"><label class="label">Certifications & Awards</label><textarea class="input biz-field" id="bizCertifications" rows="2" placeholder="ISO 9001, TrustPilot 5-star, Best of Houzz 2024" data-biz="certifications"></textarea></div>
                <div class="field"><label class="label">Areas / Cities Served</label><input class="input biz-field" id="bizAreas" placeholder="London, Surrey, Kent, Sussex" data-biz="areas_served"></div>
                <div class="field"><label class="label">Anything else AI should know?</label><textarea class="input biz-field" id="bizExtra" rows="3" placeholder="We're eco-friendly and plastic-free. We donate 5% of profits to local charities. We speak Polish and Spanish." data-biz="extra_notes"></textarea></div>
            </div>
        </div>

    </div><!-- /biz-profile -->

    <div class="divider" style="margin:24px 0"></div>

    <!-- Image Upload -->
    <div class="section-head"><span class="section-title">Your Images</span><span class="section-hint">optional — AI uses stock photos as fallback</span></div>
    <div class="dropzone" id="dropzone">
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Drop images here or click to browse</p>
        <input type="file" id="fileInput" multiple accept="image/*">
    </div>
    <div class="img-grid" id="imgGrid"></div>

    <div class="divider" style="margin:24px 0"></div>

    <!-- Pexels Image Picker -->
    <div class="section-head"><span class="section-title">Stock Images</span><span class="section-hint">select images for hero, sections & gallery (min 6)</span></div>
    <div class="image-picker-section" id="imagePicker">
        <div class="image-picker-search">
            <input type="text" id="ipSearchInput" placeholder="Search Pexels for images…">
            <button id="ipSearchBtn"><i class="fas fa-search"></i> Search</button>
        </div>
        <div class="image-picker-grid" id="ipGrid"></div>
        <div class="image-picker-load-more" id="ipLoadMore" style="display:none">
            <button id="ipLoadMoreBtn">Load more images</button>
        </div>
        <div class="image-picker-footer">
            <div class="image-picker-status">Selected: <span class="count" id="ipCount">0</span> / 6 minimum</div>
            <a href="https://www.pexels.com" target="_blank" rel="noopener">Photos provided by <strong>Pexels</strong></a>
        </div>
    </div>

        <div class="btn-row" style="flex-wrap:wrap;margin-top:16px">
        <button class="btn btn-secondary" id="btnStep2Back"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-secondary" id="btnGenAllContent">
            <i class="fas fa-layer-group"></i>
            <span class="btn-label">Generate All Content</span>
            <span class="spinner"></span>
        </button>
        <button class="btn btn-primary" id="btnStep2Next">
            <span class="btn-label">Design <i class="fas fa-arrow-right"></i></span>
        </button>
    </div>
</div>
<div class="preview-panel">
    <div class="preview-toolbar">
        <div style="display:flex;align-items:center;gap:12px">
            <span id="contentPreviewTitle" style="font-size:13px;font-weight:500">Select a page</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <span id="contentWordCount" style="font-size:11px;color:var(--ctp-overlay0)"></span>
            <span id="contentSeoScore" style="font-size:11px;font-weight:600"></span>
        </div>
    </div>
    <!-- Content editing area -->
    <div id="contentEditorPanel" style="flex:1;display:flex;flex-direction:column;overflow:hidden">
        <!-- Rewrite toolbar -->
        <div style="display:flex;align-items:center;gap:6px;padding:8px 12px;background:var(--ctp-mantle);border-bottom:1px solid var(--ctp-surface0);flex-wrap:wrap">
            <span style="font-size:11px;color:var(--ctp-overlay0);margin-right:4px">Rewrite:</span>
            <button class="btn-rewrite" data-mode="expand" title="Add more detail"><i class="fas fa-expand-arrows-alt"></i> Expand</button>
            <button class="btn-rewrite" data-mode="simplify" title="Make simpler"><i class="fas fa-compress-arrows-alt"></i> Simplify</button>
            <button class="btn-rewrite" data-mode="seo" title="SEO optimize"><i class="fas fa-search"></i> SEO</button>
            <button class="btn-rewrite" data-mode="formalize" title="More professional"><i class="fas fa-user-tie"></i> Formal</button>
            <button class="btn-rewrite" data-mode="casual" title="More casual"><i class="fas fa-comments"></i> Casual</button>
            <button class="btn-rewrite" data-mode="paraphrase" title="Rewrite differently"><i class="fas fa-sync-alt"></i> Rewrite</button>
            <div style="margin-left:auto;display:flex;gap:6px;align-items:center">
                <select id="contentToneSelect" style="background:var(--ctp-surface0);border:1px solid var(--ctp-surface1);border-radius:5px;color:var(--ctp-text);font-size:11px;padding:4px 8px">
                    <option value="neutral">Neutral tone</option>
                    <option value="professional">Professional</option>
                    <option value="friendly">Friendly</option>
                    <option value="casual">Casual</option>
                    <option value="formal">Formal</option>
                    <option value="enthusiastic">Enthusiastic</option>
                    <option value="educational">Educational</option>
                    <option value="persuasive">Persuasive</option>
                    <option value="empathetic">Empathetic</option>
                    <option value="authoritative">Authoritative</option>
                </select>
                <button class="btn-rewrite" id="btnSeoCheck" title="Run SEO analysis"><i class="fas fa-chart-line"></i> SEO Check</button>
            </div>
        </div>
        <!-- Toggle: Preview / Edit -->
        <div style="display:flex;border-bottom:1px solid var(--ctp-surface0)">
            <button class="content-tab active" data-view="preview" style="flex:1;padding:8px;background:var(--ctp-surface0);border:none;color:var(--ctp-text);font:inherit;font-size:12px;font-weight:500;cursor:pointer">Preview</button>
            <button class="content-tab" data-view="edit" style="flex:1;padding:8px;background:transparent;border:none;color:var(--ctp-overlay0);font:inherit;font-size:12px;font-weight:500;cursor:pointer">Edit HTML</button>
        </div>
        <!-- Preview iframe -->
        <div id="contentPreviewView" style="flex:1;overflow:auto;padding:24px;background:#fff">
            <div id="contentPreviewHtml" style="max-width:800px;margin:0 auto;font-family:Georgia,serif;color:#1a1a1a;line-height:1.7"></div>
        </div>
        <!-- HTML Editor -->
        <div id="contentEditView" style="flex:1;display:none;overflow:hidden">
            <textarea id="contentHtmlEditor" style="width:100%;height:100%;padding:16px;background:var(--ctp-mantle);color:var(--ctp-text);border:none;font-family:monospace;font-size:13px;line-height:1.6;resize:none"></textarea>
        </div>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════
     STEP 3: Design (Brief Review — colors, fonts, sections)
     ═══════════════════════════════════════════ -->
<div class="main step-content hidden" id="step3Content">
<div class="panel">
    <h2 style="margin-bottom:4px;font-size:20px;font-weight:600">Design Your Theme</h2>
    <p style="font-size:13px;color:var(--ctp-overlay1);margin-bottom:20px">Review and customize the design brief — colors, fonts, and layout. AI uses these settings to generate your theme.</p>

    <!-- Brief Review Panel (populated by JS from state.brief) -->
    <div id="designBriefPanel"></div>

    <!-- Header Pattern Selection -->
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-bars" style="margin-right:6px"></i> Header Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designHeaderLayout" value="auto">
    <div class="pattern-grid" id="headerPatternGrid"></div>

    <!-- Hero Pattern Selection -->
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-star" style="margin-right:6px"></i> Hero Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designHeroLayout" value="auto">
    <div class="pattern-grid" id="heroPatternGrid"></div>

    <!-- Footer Pattern Selection -->
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-shoe-prints" style="margin-right:6px"></i> Footer Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designFooterLayout" value="auto">
    <div class="pattern-grid" id="footerPatternGrid"></div>

    <!-- Section Pattern Selections (dynamically shown based on selected homepage sections) -->
    <div class="section-pattern-block" data-section-pattern="features,services">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-th-large" style="margin-right:6px"></i> Features Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designFeaturesLayout" value="auto">
    <div class="pattern-grid" id="featuresPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="about">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-info-circle" style="margin-right:6px"></i> About Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designAboutLayout" value="auto">
    <div class="pattern-grid" id="aboutPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="testimonials">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-quote-left" style="margin-right:6px"></i> Testimonials Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designTestimonialsLayout" value="auto">
    <div class="pattern-grid" id="testimonialsPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="pricing">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-tags" style="margin-right:6px"></i> Pricing Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designPricingLayout" value="auto">
    <div class="pattern-grid" id="pricingPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="cta,newsletter">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-bullhorn" style="margin-right:6px"></i> CTA Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designCTALayout" value="auto">
    <div class="pattern-grid" id="ctaPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="faq">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-question-circle" style="margin-right:6px"></i> FAQ Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designFAQLayout" value="auto">
    <div class="pattern-grid" id="faqPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="stats">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-chart-bar" style="margin-right:6px"></i> Stats Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designStatsLayout" value="auto">
    <div class="pattern-grid" id="statsPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="clients,partners">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-handshake" style="margin-right:6px"></i> Clients Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designClientsLayout" value="auto">
    <div class="pattern-grid" id="clientsPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="gallery,portfolio">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-images" style="margin-right:6px"></i> Gallery Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designGalleryLayout" value="auto">
    <div class="pattern-grid" id="galleryPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="team">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-users" style="margin-right:6px"></i> Team Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designTeamLayout" value="auto">
    <div class="pattern-grid" id="teamPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="blog,articles">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-blog" style="margin-right:6px"></i> Blog Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designBlogLayout" value="auto">
    <div class="pattern-grid" id="blogPatternGrid"></div>
    </div>

    <div class="section-pattern-block" data-section-pattern="contact">
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-envelope" style="margin-right:6px"></i> Contact Layout</span><span class="section-hint">click to select</span></div>
    <input type="hidden" id="designContactLayout" value="auto">
    <div class="pattern-grid" id="contactPatternGrid"></div>
    </div>

    <!-- Sub-Page Layout Styles (dynamically shown for selected pages) -->
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-file-alt" style="margin-right:6px"></i> Page Layout Styles</span><span class="section-hint">choose layout for each sub-page</span></div>
    <div id="pageLayoutsContainer" class="page-layouts-section"></div>

    <!-- Sidebar Toggle -->
    <div class="divider"></div>
    <div class="section-head"><span class="section-title"><i class="fas fa-columns" style="margin-right:6px"></i> Sidebar</span></div>
    <label style="display:flex;align-items:center;gap:10px;padding:8px 0;cursor:pointer">
        <input type="checkbox" id="designSidebar" style="width:18px;height:18px;accent-color:var(--ctp-blue)">
        <span style="font-size:13px">Include sidebar on sub-pages</span>
        <span style="font-size:11px;color:var(--ctp-overlay0)">(contact info, recent posts, categories)</span>
    </label>

    <div class="divider"></div>

    <!-- Model + Creativity for build -->
    <div class="row-2">
        <div class="field">
            <label class="label">AI Model for Build</label>
            <select class="input" id="designModel">
            <?php foreach ($aiModels ?? [] as $m): ?>
                <option value="<?= esc($m['provider'] ?? '') ?>:<?= esc($m['model'] ?? '') ?>" data-tier="<?= esc($m['tier'] ?? 'recommended') ?>"<?= ($m['isDefault'] ?? false) ? ' selected' : '' ?>><?= esc($m['name'] ?? $m['model'] ?? 'Unknown') ?><?= ($m['tier'] ?? '') === 'budget' ? ' ⚠️' : (($m['tier'] ?? '') === 'premium' ? ' ⭐' : '') ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label class="label">Creativity</label>
            <select class="input" id="designCreativity">
                <option value="low">🎯 Precise</option>
                <option value="medium" selected>⚖️ Balanced</option>
                <option value="high">🎨 Experimental</option>
            </select>
        </div>
    </div>

    <div class="btn-row">
        <button class="btn btn-secondary" id="btnStep3Back"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn btn-primary" id="btnBuildTheme">
            <span class="btn-label">Build Theme <i class="fas fa-arrow-right"></i></span>
            <span class="spinner"></span>
        </button>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════
     STEP 4: Build (SSE Streaming + Preview)
     ═══════════════════════════════════════════ -->
<div class="main step-content hidden" id="step4Content">
<div class="panel split">
    <h2 style="margin-bottom:16px">Building Theme</h2>
    <p style="font-size:13px;color:var(--ctp-subtext0);margin-bottom:16px">AI is generating your website — homepage, header, footer, and CSS.</p>
    <div class="progress-list" id="layoutProgress"></div>

    <div id="layoutActions" class="hidden">
        <div class="divider"></div>
        <p style="font-size:13px;color:var(--ctp-green);margin-bottom:16px"><i class="fas fa-check-circle"></i> Website generated! Preview it on the right, then continue to review.</p>
        <div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap">
            <div style="flex:1;min-width:140px">
                <label class="label" style="font-size:10px;margin-bottom:2px">Creativity</label>
                <select class="input" id="regenCreativity" style="font-size:12px;padding:6px 8px">
                    <option value="low">🎯 Precise</option>
                    <option value="medium" selected>⚖️ Balanced</option>
                    <option value="high">🎨 Experimental</option>
                </select>
            </div>
            <div style="flex:1;min-width:140px">
                <label class="label" style="font-size:10px;margin-bottom:2px">Regenerate with</label>
                <select class="input" id="regenModel" style="font-size:12px;padding:6px 8px"></select>
            </div>
        </div>
        <label style="font-size:11px;color:var(--ctp-subtext0);display:flex;align-items:center;gap:6px;margin-bottom:12px;cursor:pointer">
            <input type="checkbox" id="regenNewBrief" checked> <span>Fresh design (new colors, fonts &amp; layout)</span>
        </label>
        <div class="btn-row">
            <button class="btn btn-secondary" id="btnStep4Back"><i class="fas fa-arrow-left"></i> Back</button>
            <button class="btn btn-secondary" id="btnRegenLayout"><i class="fas fa-redo"></i> Regenerate</button>
            <button class="btn btn-primary" id="btnStep4Next"><span class="btn-label">Review <i class="fas fa-arrow-right"></i></span></button>
        </div>
    </div>
</div>
<div class="preview-panel" id="previewPanel4">
    <div class="preview-toolbar">
        <div class="device-btns">
            <button class="active" data-device="desktop"><i class="fas fa-desktop"></i></button>
            <button data-device="tablet"><i class="fas fa-tablet-alt"></i></button>
            <button data-device="mobile"><i class="fas fa-mobile-alt"></i></button>
        </div>
        <div class="url-bar" id="previewUrl4">—</div>
        <a href="#" target="_blank" id="previewLink4" style="font-size:12px;color:var(--ctp-blue);text-decoration:none"><i class="fas fa-external-link-alt"></i> Open</a>
    </div>
    <div class="preview-frame" id="previewFrame4">
        <iframe id="previewIframe4" src="about:blank"></iframe>
    </div>
</div>
</div>

<!-- ═══════════════════════════════════════════
     STEP 5: Review & Publish
     ═══════════════════════════════════════════ -->
<div class="main step-content hidden" id="step5Content">
<div class="panel split" style="width:360px;min-width:360px">
    <h2 style="margin-bottom:16px">Review &amp; Publish</h2>
    <p style="font-size:13px;color:var(--ctp-subtext0);margin-bottom:16px">Preview all pages, then apply the theme to make it live.</p>

    <!-- Page tabs for preview -->
    <div class="section-head"><span class="section-title">Preview Pages</span></div>
    <div class="tabs" id="reviewPageTabs" style="flex-direction:column"></div>

    <div class="divider"></div>

    <div class="section-head"><span class="section-title">Generation Summary</span></div>
    <div class="checklist" id="finalChecklist"></div>

    <div class="divider"></div>

    <div class="btn-row" style="flex-direction:column">
        <button class="btn btn-success" id="btnApply" style="width:100%;justify-content:center"><i class="fas fa-check"></i> Apply Theme</button>
        <button class="btn btn-secondary" id="btnExport" style="width:100%;justify-content:center"><i class="fas fa-download"></i> Download .zip</button>
        <button class="btn btn-secondary" id="btnStartFresh" style="width:100%;justify-content:center"><i class="fas fa-redo"></i> Start Fresh</button>
        <button class="btn btn-secondary" id="btnStep5Back" style="width:100%;justify-content:center"><i class="fas fa-arrow-left"></i> Back to Build</button>
    </div>
</div>
<div class="preview-panel">
    <div class="preview-toolbar">
        <div class="device-btns">
            <button class="active" data-device="desktop"><i class="fas fa-desktop"></i></button>
            <button data-device="tablet"><i class="fas fa-tablet-alt"></i></button>
            <button data-device="mobile"><i class="fas fa-mobile-alt"></i></button>
        </div>
        <div class="url-bar" id="previewUrl4_review">—</div>
    </div>
    <div class="preview-frame" id="previewFrame4_review">
        <iframe id="previewIframe4_review" src="about:blank"></iframe>
    </div>
</div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
// ═══════════════════════════════════════════
// GLOBALS
// ═══════════════════════════════════════════
const CSRF = <?= json_encode($csrfToken) ?>;
const PRESETS = <?= $presetsJson ?>;
const SECTIONS = <?= $sectionsJson ?>;
const TONES = <?= $tonesJson ?>;

const _savedState = (() => { try { return JSON.parse(localStorage.getItem("atb_wizard_state")) || {}; } catch(e) { return {}; } })();
const state = {
    step: _savedState.step || 1,
    brief: _savedState.brief || null,
    contentPlan: _savedState.contentPlan || {},
    pageContent: _savedState.pageContent || {},
    pastedContent: _savedState.pastedContent || {},
    slug: _savedState.slug || null,
    pages: _savedState.pages || ["home","about","services","gallery","blog","contact"],
    homeSections: _savedState.homeSections || [],
    businessInfo: _savedState.businessInfo || {},
    userImages: _savedState.userImages || [],
    generatedPages: _savedState.generatedPages || {},
    seededPages: _savedState.seededPages || null,
    reviewAccepted: _savedState.reviewAccepted || {},
    currentPage: _savedState.currentPage || null,
    tone: _savedState.tone || "professional",
    selectedImages: _savedState.selectedImages || [],
};

// Auto-save state to localStorage on changes
function saveState() {
    try { localStorage.setItem("atb_wizard_state", JSON.stringify(state)); } catch(e) {}
}
// Clear saved state (for "Start Fresh")
function clearSavedState() {
    try { localStorage.removeItem("atb_wizard_state"); } catch(e) {}
}

// ═══════════════════════════════════════════
// BUSINESS PROFILE — Industry groups, repeaters, visibility
// ═══════════════════════════════════════════

// Map every CMS industry → one or more biz-groups for section visibility
const INDUSTRY_BIZ_GROUP = {
    // Food & Drink
    restaurant:'food', cafe:'food', bakery:'food', pizzeria:'food', 'food-truck':'food', catering:'food',
    bar:'food', brewery:'food', winery:'food', 'juice-bar':'food', 'ice-cream':'food', 'meal-prep':'food',
    'coffee-roaster':'food', 'food-blog':'food', 'cooking-school':'food', 'sushi-bar':'food',
    steakhouse:'food', bistro:'food', 'tea-room':'food', deli:'food', butcher:'food',
    // Health & Medical
    dentist:'health', doctor:'health', physiotherapy:'health', veterinary:'health', pharmacy:'health',
    chiropractor:'health', optician:'health', 'mental-health':'health', osteopath:'health',
    podiatry:'health', dermatology:'health', 'fertility-clinic':'health', 'hearing-aid':'health',
    acupuncture:'health', 'sports-medicine':'health', nutritionist:'health',
    // Legal & Professional
    solicitor:'legal', lawyer:'legal', 'law-firm':'legal', barrister:'legal', notary:'legal',
    accountant:'professional', consultant:'professional', 'financial-advisor':'finance',
    'insurance-broker':'finance', 'mortgage-broker':'finance', 'wealth-management':'finance',
    'tax-advisor':'professional', bookkeeper:'professional',
    // Creative & Portfolio
    photography:'creative', 'graphic-design':'creative', architect:'creative', 'interior-design':'creative',
    videography:'creative', 'art-gallery':'creative', 'web-design':'creative', 'illustration':'creative',
    'animation-studio':'creative', 'music-studio':'creative', 'tattoo-studio':'creative',
    'film-production':'creative', 'branding-agency':'creative',
    // Retail & E-commerce
    shop:'retail', boutique:'retail', ecommerce:'retail', 'online-store':'retail', florist:'retail',
    jewellery:'retail', 'gift-shop':'retail', bookshop:'retail', 'antique-shop':'retail',
    'furniture-store':'retail', 'pet-shop':'retail', 'bike-shop':'retail', 'wine-shop':'retail',
    // Beauty & Wellness
    'hair-salon':'beauty', 'beauty-salon':'beauty', 'nail-salon':'beauty', spa:'beauty',
    barbershop:'beauty', 'aesthetics-clinic':'beauty', 'massage-therapy':'beauty',
    'beauty-brand':'beauty', 'skincare':'beauty', 'makeup-artist':'beauty',
    // Construction & Trade
    construction:'construction', plumber:'construction', electrician:'construction',
    'roofing':'construction', 'painting-decorating':'construction', landscaping:'construction',
    'fencing':'construction', 'paving':'construction', 'kitchen-fitter':'construction',
    'bathroom-fitter':'construction', 'window-installer':'construction', 'solar-installer':'construction',
    'general-contractor':'construction', 'steel-fabrication':'construction', 'scaffolding':'construction',
    'water-treatment':'construction', 'electrical-testing':'construction', 'hvac':'construction',
    // Technology
    'software-company':'technology', 'saas':'technology', 'it-support':'technology',
    'cybersecurity':'technology', 'app-development':'technology', 'data-analytics':'technology',
    'ai-company':'technology', 'cloud-services':'technology', 'tech-startup':'technology',
    // Education
    school:'education', 'language-school':'education', 'tutoring':'education',
    'online-academy':'education', 'driving-school':'education', 'music-school':'education',
    'dance-school':'education', university:'education', 'training-provider':'education',
    // Automotive
    'car-dealer':'automotive', 'auto-repair':'automotive', 'car-wash':'automotive',
    'tyre-shop':'automotive', 'mot-centre':'automotive', 'car-rental':'automotive',
    'motorcycle-dealer':'automotive', 'auto-detailing':'automotive',
    // Real Estate
    'estate-agent':'realestate', 'property-management':'realestate', 'home-staging':'realestate',
    'surveyor':'realestate', 'letting-agent':'realestate',
    // Events & Entertainment
    'event-planner':'events', 'wedding-planner':'events', 'event-production':'events',
    'theatre':'events', dj:'events', 'photo-booth':'events', 'live-music':'events',
    'party-supplies':'events', 'magician':'events',
    // Fitness & Sports
    gym:'fitness', 'personal-trainer':'fitness', 'yoga-studio':'fitness', 'martial-arts':'fitness',
    'swimming-pool':'fitness', 'sports-club':'fitness', 'pilates':'fitness', 'crossfit':'fitness',
    // Pets
    'dog-grooming':'pets', 'dog-walking':'pets', 'pet-sitting':'pets', kennel:'pets',
    'pet-shop':'pets', 'veterinary':'pets',
    // Travel & Hospitality
    hotel:'travel', 'bed-breakfast':'travel', 'travel-agency':'travel', 'tour-operator':'travel',
    'holiday-rental':'travel', 'adventure-travel':'travel', 'motorcycle-tours':'travel',
    'walking-holiday':'travel', 'city-tours':'travel', hostel:'travel',
    // Logistics & Transport
    'courier':'logistics', 'removal-company':'logistics', 'taxi':'logistics',
    'trucking':'logistics', 'freight':'logistics', 'storage-facility':'logistics',
    // Services (catch-all)
    cleaning:'services', 'pest-control':'services', 'locksmith':'services',
    'security':'services', 'laundry':'services', 'gardening':'services',
    'handyman':'services', 'skip-hire':'services', 'waste-management':'services',
    'chimney-sweep':'services', 'carpet-cleaning':'services',
    // Non-profit & community
    charity:'services', church:'services', 'community-group':'services',
    // Marketing & media
    'marketing-agency':'services', 'seo-agency':'technology', 'pr-agency':'services',
    'social-media-agency':'services', 'advertising-agency':'services', 'podcast':'creative',
    // Generic fallbacks
    business:'services', portfolio:'creative', blog:'creative', nonprofit:'services',
};

function getIndustryBizGroup(industry) {
    if (!industry) return 'services';
    const normalized = industry.toLowerCase().replace(/[\s_]+/g, '-');
    return INDUSTRY_BIZ_GROUP[normalized] || 'services';
}

// Show/hide industry-specific sections based on selected industry
function initBusinessProfile(industry) {
    const group = getIndustryBizGroup(industry);
    document.querySelectorAll('.biz-industry-section').forEach(sec => {
        const groups = (sec.dataset.bizGroups || '').split(',').map(g => g.trim());
        sec.classList.toggle('visible', groups.includes(group));
    });
    // Update services section icon & title based on industry
    const svcSection = document.getElementById('bizSectionServices');
    if (svcSection) {
        const icon = svcSection.querySelector('.section-icon');
        const title = svcSection.querySelector('.biz-section-title');
        if (group === 'food') { icon.className = 'fas fa-utensils section-icon'; title.textContent = 'Menu & Services'; }
        else if (group === 'retail') { icon.className = 'fas fa-shopping-bag section-icon'; title.textContent = 'Products & Services'; }
        else if (group === 'creative') { icon.className = 'fas fa-paint-brush section-icon'; title.textContent = 'Services & Packages'; }
        else if (group === 'health') { icon.className = 'fas fa-stethoscope section-icon'; title.textContent = 'Treatments & Services'; }
        else if (group === 'construction') { icon.className = 'fas fa-hard-hat section-icon'; title.textContent = 'Services & Specialties'; }
        else if (group === 'technology') { icon.className = 'fas fa-laptop-code section-icon'; title.textContent = 'Products & Services'; }
        else { icon.className = 'fas fa-concierge-bell section-icon'; title.textContent = 'Services & Products'; }
    }
    // Restore from state
    restoreBusinessProfile();
    updateBizCompletion();
}

// Collapse/expand sections
function toggleBizSection(headEl) {
    const section = headEl.closest('.biz-section');
    section.classList.toggle('collapsed');
}

// ─── Repeater ───
function addBizRepeaterItem(repeaterId, type, data) {
    const container = document.getElementById(repeaterId);
    if (!container) return;
    const item = document.createElement('div');
    item.className = 'biz-repeater-item';
    item.dataset.type = type;

    let fieldsHtml = '';
    if (type === 'service') {
        fieldsHtml = `
            <div class="biz-ri-fields">
                <input type="text" placeholder="Service name" class="biz-rp-name" value="${esc(data?.name || '')}">
                <textarea placeholder="Brief description (optional)" class="biz-rp-desc" rows="2">${esc(data?.description || '')}</textarea>
            </div>`;
    } else if (type === 'team') {
        fieldsHtml = `
            <div class="biz-ri-fields">
                <div class="biz-ri-row">
                    <input type="text" placeholder="Full name" class="biz-rp-name" value="${esc(data?.name || '')}">
                    <input type="text" placeholder="Role / Title" class="biz-rp-role" value="${esc(data?.role || '')}">
                </div>
                <textarea placeholder="Short bio (optional)" class="biz-rp-desc" rows="2">${esc(data?.bio || '')}</textarea>
            </div>`;
    } else if (type === 'testimonial') {
        fieldsHtml = `
            <div class="biz-ri-fields">
                <textarea placeholder="What they said..." class="biz-rp-quote" rows="2">${esc(data?.quote || '')}</textarea>
                <div class="biz-ri-row">
                    <input type="text" placeholder="Author name" class="biz-rp-name" value="${esc(data?.name || '')}">
                    <input type="text" placeholder="Company / Role" class="biz-rp-role" value="${esc(data?.company || '')}">
                </div>
            </div>`;
    }

    item.innerHTML = fieldsHtml + '<button class="biz-ri-remove" onclick="removeBizRepeaterItem(this)" title="Remove"><i class="fas fa-times"></i></button>';
    container.appendChild(item);

    // Listen for changes → auto-save
    item.querySelectorAll('input,textarea').forEach(inp => {
        inp.addEventListener('input', () => { collectBusinessInfo(); saveState(); updateBizCompletion(); });
    });

    updateBizCompletion();
}

function removeBizRepeaterItem(btn) {
    const item = btn.closest('.biz-repeater-item');
    item.remove();
    collectBusinessInfo();
    saveState();
    updateBizCompletion();
}

// HTML escape for repeater values
function esc(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Opening Hours ───
function initHoursClosedToggle() {
    document.querySelectorAll('.biz-hour-closed').forEach(cb => {
        const row = cb.closest('.biz-hours-row');
        row.classList.toggle('is-closed', cb.checked);
        cb.addEventListener('change', () => {
            row.classList.toggle('is-closed', cb.checked);
            collectBusinessInfo();
            saveState();
            updateBizCompletion();
        });
    });
    document.querySelectorAll('.biz-hour-open, .biz-hour-close').forEach(inp => {
        inp.addEventListener('input', () => { collectBusinessInfo(); saveState(); updateBizCompletion(); });
    });
}

// ─── Collect ALL business profile data into state ───
function collectBusinessInfo() {
    const info = {};

    // All simple fields
    document.querySelectorAll('.biz-field').forEach(field => {
        const key = field.dataset.biz;
        if (!key) return;
        const val = field.value.trim();
        if (val) info[key] = val;
    });

    // Social media (grouped)
    const social = {};
    ['facebook','instagram','twitter','linkedin','youtube','tiktok'].forEach(platform => {
        const el = document.querySelector(`.biz-field[data-biz="${platform}"]`);
        if (el && el.value.trim()) social[platform] = el.value.trim();
    });
    if (Object.keys(social).length > 0) info.social = social;

    // Services repeater
    const services = [];
    document.querySelectorAll('#bizServicesRepeater .biz-repeater-item').forEach(item => {
        const name = item.querySelector('.biz-rp-name')?.value?.trim() || '';
        const desc = item.querySelector('.biz-rp-desc')?.value?.trim() || '';
        if (name) services.push({ name, description: desc });
    });
    if (services.length > 0) info.services = services;

    // Team repeater
    const team = [];
    document.querySelectorAll('#bizTeamRepeater .biz-repeater-item').forEach(item => {
        const name = item.querySelector('.biz-rp-name')?.value?.trim() || '';
        const role = item.querySelector('.biz-rp-role')?.value?.trim() || '';
        const bio = item.querySelector('.biz-rp-desc')?.value?.trim() || '';
        if (name) team.push({ name, role, bio });
    });
    if (team.length > 0) info.team = team;

    // Testimonials repeater
    const testimonials = [];
    document.querySelectorAll('#bizTestimonialsRepeater .biz-repeater-item').forEach(item => {
        const quote = item.querySelector('.biz-rp-quote')?.value?.trim() || '';
        const name = item.querySelector('.biz-rp-name')?.value?.trim() || '';
        const company = item.querySelector('.biz-rp-role')?.value?.trim() || '';
        if (quote) testimonials.push({ quote, name, company });
    });
    if (testimonials.length > 0) info.testimonials = testimonials;

    // Opening hours
    const hours = {};
    document.querySelectorAll('.biz-hours-row').forEach(row => {
        const day = row.dataset.day;
        const closed = row.querySelector('.biz-hour-closed')?.checked || false;
        const open = row.querySelector('.biz-hour-open')?.value?.trim() || '';
        const close = row.querySelector('.biz-hour-close')?.value?.trim() || '';
        if (closed) {
            hours[day] = 'Closed';
        } else if (open || close) {
            hours[day] = `${open} – ${close}`;
        }
    });
    if (Object.keys(hours).length > 0) info.hours = hours;

    state.businessInfo = info;
}

// ─── Restore form from state ───
function restoreBusinessProfile() {
    const info = state.businessInfo || {};

    // Simple fields
    document.querySelectorAll('.biz-field').forEach(field => {
        const key = field.dataset.biz;
        if (!key) return;
        // Social media is nested
        if (['facebook','instagram','twitter','linkedin','youtube','tiktok'].includes(key)) {
            field.value = info.social?.[key] || info[key] || '';
        } else {
            field.value = info[key] || '';
        }
    });

    // Services
    const svcContainer = document.getElementById('bizServicesRepeater');
    if (svcContainer && info.services?.length) {
        svcContainer.innerHTML = '';
        info.services.forEach(s => addBizRepeaterItem('bizServicesRepeater', 'service', s));
    }

    // Team
    const teamContainer = document.getElementById('bizTeamRepeater');
    if (teamContainer && info.team?.length) {
        teamContainer.innerHTML = '';
        info.team.forEach(t => addBizRepeaterItem('bizTeamRepeater', 'team', t));
    }

    // Testimonials
    const testContainer = document.getElementById('bizTestimonialsRepeater');
    if (testContainer && info.testimonials?.length) {
        testContainer.innerHTML = '';
        info.testimonials.forEach(t => addBizRepeaterItem('bizTestimonialsRepeater', 'testimonial', t));
    }

    // Hours
    if (info.hours) {
        document.querySelectorAll('.biz-hours-row').forEach(row => {
            const day = row.dataset.day;
            const val = info.hours[day];
            if (!val) return;
            if (val === 'Closed') {
                const cb = row.querySelector('.biz-hour-closed');
                if (cb) { cb.checked = true; row.classList.add('is-closed'); }
            } else {
                const parts = val.split('–').map(s => s.trim());
                const openInp = row.querySelector('.biz-hour-open');
                const closeInp = row.querySelector('.biz-hour-close');
                if (openInp && parts[0]) openInp.value = parts[0];
                if (closeInp && parts[1]) closeInp.value = parts[1];
            }
        });
    }
}

// ─── Completion % indicator ───
function updateBizCompletion() {
    // Weight: basic fields high, optional lower
    const weights = {
        name: 3, description: 5, tagline: 2, years: 1, usps: 3, audience: 2,
        phone: 2, email: 2, address: 2,
        services: 4, team: 2, testimonials: 3,
    };
    let total = 0, filled = 0;
    const info = state.businessInfo || {};

    for (const [key, weight] of Object.entries(weights)) {
        total += weight;
        if (key === 'services' || key === 'team' || key === 'testimonials') {
            if (info[key]?.length > 0) filled += weight;
        } else {
            if (info[key]) filled += weight;
        }
    }

    const pct = total > 0 ? Math.round((filled / total) * 100) : 0;
    const fillEl = document.getElementById('bizCompletionFill');
    const textEl = document.getElementById('bizCompletionPct');
    if (fillEl) fillEl.style.width = pct + '%';
    if (textEl) textEl.textContent = pct + '%';
}

// Auto-save on field changes (simple biz-field inputs)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.biz-field').forEach(field => {
        field.addEventListener('input', () => {
            collectBusinessInfo();
            saveState();
            updateBizCompletion();
        });
    });
    initHoursClosedToggle();
});

// Restore UI from saved state on load — show banner with Continue / Start Fresh
if (_savedState.step && _savedState.step > 1 && _savedState.brief) {
    setTimeout(() => {
        const themeName = _savedState.brief?.name || "your theme";
        const stepNames = {1:"Configure",2:"Content",3:"Design",4:"Build",5:"Review"};
        const lastStep = stepNames[_savedState.step] || "Step " + _savedState.step;

        const banner = document.createElement("div");
        banner.id = "restoreBanner";
        banner.style.cssText = "position:fixed;top:0;left:0;right:0;z-index:99999;background:var(--ctp-surface0,#313244);border-bottom:2px solid var(--ctp-blue,#89b4fa);padding:16px 24px;display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;box-shadow:0 4px 24px rgba(0,0,0,.3)";
        banner.innerHTML = ''
            + '<div style="flex:1;min-width:200px">'
            + '<div style="font-size:14px;font-weight:600;color:var(--ctp-text,#cdd6f4)">Previous session found</div>'
            + '<div style="font-size:12px;color:var(--ctp-subtext0,#a6adc8)">Theme: <strong>' + themeName + '</strong> — last step: ' + lastStep + '</div>'
            + '</div>'
            + '<button id="_restoreContinue" style="padding:8px 20px;border-radius:8px;border:none;background:var(--ctp-blue,#89b4fa);color:var(--ctp-crust,#11111b);cursor:pointer;font-weight:600;font-size:13px">Continue</button>'
            + '<button id="_restoreFresh" style="padding:8px 20px;border-radius:8px;border:1px solid var(--ctp-surface2,#585b70);background:transparent;color:var(--ctp-text,#cdd6f4);cursor:pointer;font-size:13px">Start Fresh</button>';
        document.body.appendChild(banner);

        document.getElementById("_restoreContinue").onclick = () => {
            banner.remove();
            document.getElementById("btnStep1Next").disabled = false;
            document.getElementById("step1NextHint").style.display = "none";
            if (state.brief) {
                document.getElementById("briefReview").classList.remove("hidden");
                document.getElementById("briefConfirmName").textContent = state.brief.name || "Brief Restored";
            }
            toast("Session restored — continue from " + lastStep, "success");
        };
        document.getElementById("_restoreFresh").onclick = () => {
            banner.remove();
            clearSavedState();
            window.location.reload();
        };
    }, 300);
}

// ═══════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════
function ucfirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

// WCAG contrast ratio calculator
function getContrastRatio(hex1, hex2) {
    function hexToRgb(h) {
        h = h.replace("#","");
        if (h.length === 3) h = h[0]+h[0]+h[1]+h[1]+h[2]+h[2];
        return [parseInt(h.slice(0,2),16), parseInt(h.slice(2,4),16), parseInt(h.slice(4,6),16)];
    }
    function luminance(r,g,b) {
        const [rs,gs,bs] = [r,g,b].map(c => { c /= 255; return c <= 0.03928 ? c/12.92 : Math.pow((c+0.055)/1.055, 2.4); });
        return 0.2126*rs + 0.7152*gs + 0.0722*bs;
    }
    const [r1,g1,b1] = hexToRgb(hex1);
    const [r2,g2,b2] = hexToRgb(hex2);
    const l1 = luminance(r1,g1,b1);
    const l2 = luminance(r2,g2,b2);
    const lighter = Math.max(l1,l2);
    const darker = Math.min(l1,l2);
    return (lighter + 0.05) / (darker + 0.05);
}
function getPageIcon(pageId) {
    const icons = {home:'fas fa-home',about:'fas fa-info-circle',services:'fas fa-concierge-bell',contact:'fas fa-envelope',blog:'fas fa-blog',portfolio:'fas fa-briefcase',pricing:'fas fa-tags',faq:'fas fa-question-circle',team:'fas fa-users',testimonials:'fas fa-star',gallery:'fas fa-images',careers:'fas fa-user-plus',events:'fas fa-calendar',process:'fas fa-cogs',partners:'fas fa-handshake'};
    return icons[pageId] || 'fas fa-file';
}

// ═══════════════════════════════════════════
// INDUSTRY KITS — one-click full configuration
// ═══════════════════════════════════════════
const INDUSTRY_KITS = {
    restaurant: {prompt:"An upscale restaurant with a warm, inviting atmosphere. Signature dishes, chef's story, wine list, and online reservations.",industry:"restaurant",style:"elegant",mood:"warm",tone:"luxurious",pages:["home","about","services","gallery","contact","blog"],sections:["hero","about","menu","gallery","testimonials","cta"],palette:"Fine Dining"},
    cafe: {prompt:"A cozy neighborhood café serving artisan coffee, fresh pastries, and light brunch. Relaxed atmosphere with free WiFi.",industry:"cafe",style:"organic",mood:"warm",tone:"friendly",pages:["home","about","services","gallery","contact","blog"],sections:["hero","about","menu","gallery","cta"],palette:"Café Latte"},
    saas: {prompt:"A modern SaaS platform for team productivity. Features include real-time collaboration, task management, analytics dashboard, and API integrations.",industry:"saas",style:"minimalist",mood:"cool",tone:"professional",pages:["home","about","services","pricing","contact","blog","faq"],sections:["hero","features","stats","pricing","testimonials","cta"],palette:"Tech Startup"},
    law: {prompt:"A prestigious law firm specializing in corporate law, mergers & acquisitions, and intellectual property. 30+ years of experience.",industry:"law",style:"corporate",mood:"dark",tone:"formal",pages:["home","about","services","team","contact","blog","faq"],sections:["hero","about","services","team","testimonials","cta"],palette:"Legal Authority"},
    medical: {prompt:"A modern medical clinic offering family medicine, diagnostics, specialist consultations, and preventive health programs.",industry:"medical",style:"minimalist",mood:"light",tone:"professional",pages:["home","about","services","team","contact","blog","faq"],sections:["hero","services","team","stats","testimonials","cta"],palette:"Medical Trust"},
    dental: {prompt:"A friendly dental practice with cosmetic dentistry, orthodontics, implants, and family dental care. State-of-the-art equipment.",industry:"dental",style:"minimalist",mood:"light",tone:"friendly",pages:["home","about","services","team","contact","blog","faq","pricing"],sections:["hero","services","team","testimonials","cta"],palette:"Dental Fresh"},
    fitness: {prompt:"A high-energy gym with personal training, group classes, nutrition coaching, and 24/7 access. Transform your body and mind.",industry:"fitness",style:"bold",mood:"dark",tone:"casual",pages:["home","about","services","team","pricing","contact","blog","gallery"],sections:["hero","services","stats","pricing","testimonials","gallery","cta"],palette:"Fitness Power"},
    realestate: {prompt:"A luxury real estate agency with premium property listings, virtual tours, market analysis, and personalized property search.",industry:"realestate",style:"elegant",mood:"light",tone:"professional",pages:["home","about","services","portfolio","team","contact","blog"],sections:["hero","features","portfolio","stats","testimonials","cta"],palette:"Real Estate"},
    photography: {prompt:"A professional photography studio specializing in weddings, portraits, events, and commercial shoots. Award-winning photographer.",industry:"photography",style:"minimalist",mood:"dark",tone:"professional",pages:["home","about","services","portfolio","gallery","contact","blog","pricing"],sections:["hero","portfolio","about","services","testimonials","cta"],palette:"Photography"},
    agency: {prompt:"A creative digital agency offering branding, web design, digital marketing, and social media management for ambitious brands.",industry:"agency",style:"bold",mood:"dark",tone:"witty",pages:["home","about","services","portfolio","team","contact","blog"],sections:["hero","services","portfolio","stats","team","testimonials","cta"],palette:"Agency Bold"},
    wedding: {prompt:"An elegant wedding planning service with full coordination, venue selection, floral design, and bespoke decor for unforgettable celebrations.",industry:"wedding",style:"elegant",mood:"warm",tone:"luxurious",pages:["home","about","services","portfolio","gallery","contact","blog","pricing","testimonials"],sections:["hero","services","portfolio","gallery","testimonials","cta"],palette:"Wedding Blush"},
    construction: {prompt:"A trusted construction company with 20+ years experience in residential, commercial, and renovation projects. Licensed and insured.",industry:"construction",style:"corporate",mood:"light",tone:"professional",pages:["home","about","services","portfolio","team","contact","blog","faq"],sections:["hero","services","portfolio","stats","testimonials","cta"],palette:"Construction"},
    ecommerce: {prompt:"A curated online store with handpicked products, fast shipping, easy returns, and excellent customer service. Shop with confidence.",industry:"ecommerce",style:"minimalist",mood:"light",tone:"friendly",pages:["home","about","services","contact","blog","faq"],sections:["hero","features","products","stats","testimonials","cta"],palette:"Arctic Clean"},
    education: {prompt:"An innovative online learning platform with expert-led courses, interactive workshops, certifications, and a thriving student community.",industry:"onlinecourse",style:"playful",mood:"colorful",tone:"friendly",pages:["home","about","services","pricing","contact","blog","faq","testimonials"],sections:["hero","features","services","stats","testimonials","cta"],palette:"Education Bright"},
    nonprofit: {prompt:"A passionate environmental nonprofit working on ocean conservation, wildlife protection, and community education programs worldwide.",industry:"nonprofit",style:"organic",mood:"warm",tone:"friendly",pages:["home","about","services","team","gallery","contact","blog"],sections:["hero","about","stats","services","gallery","cta"],palette:"Eco Green"},
};

// ═══════════════════════════════════════════
// PROMPT SUGGESTIONS (4 per industry group)
// ═══════════════════════════════════════════
const PROMPT_SUGGESTIONS = {
    // ═══════════════════════════════════════════════════════
    // FOOD & HOSPITALITY
    // ═══════════════════════════════════════════════════════
    restaurant: [
        {icon:"🍝",label:"Italian Trattoria",text:"Cozy Italian trattoria with rustic brick walls, handmade pasta, and a curated regional wine list. Warm candlelit atmosphere with an open kitchen.",style:"elegant",mood:"warm",tone:"friendly"},
        {icon:"🍣",label:"Japanese Omakase",text:"Modern Japanese omakase restaurant with minimalist zen interior, counter seating, and seasonal tasting menus. Clean lines, natural materials.",style:"minimalist",mood:"dark",tone:"luxurious"},
        {icon:"🔥",label:"BBQ Smokehouse",text:"Trendy BBQ smokehouse with industrial decor, exposed ductwork, craft beer selection, and live music on weekends. Bold and unapologetic.",style:"bold",mood:"warm",tone:"casual"},
        {icon:"🥗",label:"Farm-to-Table Bistro",text:"Health-focused farm-to-table bistro with bright airy interior, organic seasonal menu, and sustainability mission. Fresh and modern.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🌮",label:"Mexican Cantina",text:"Vibrant Mexican cantina with hand-painted murals, street-style tacos, mezcal bar, and live mariachi on weekends.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🍱",label:"Korean BBQ",text:"Modern Korean BBQ restaurant with tabletop grills, banchan bar, soju flights, and K-pop inspired neon interior.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"🦐",label:"Seafood Shack",text:"Upscale coastal seafood restaurant with raw bar, daily market catches, nautical decor, and harbor-view terrace dining.",style:"elegant",mood:"cool",tone:"luxurious"},
        {icon:"🍽️",label:"Fine Dining",text:"Michelin-aspiring fine dining restaurant with degustation menu, molecular gastronomy techniques, and sommelier's table.",style:"minimalist",mood:"luxury",tone:"luxurious"},
    ],
    cafe: [
        {icon:"☕",label:"Specialty Coffee",text:"Third-wave specialty coffee shop with Scandinavian interior, single-origin beans, pour-over bar, and minimalist pastry display.",style:"minimalist",mood:"light",tone:"casual"},
        {icon:"📚",label:"Book Café",text:"Cozy literary café with floor-to-ceiling bookshelves, vintage armchairs, artisan teas, and weekly author readings.",style:"retro",mood:"warm",tone:"friendly"},
        {icon:"🌿",label:"Vegan Café",text:"Plant-based café with living walls, reclaimed wood furniture, cold-pressed juices, and zero-waste philosophy.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🎨",label:"Art Café",text:"Creative café doubling as art gallery, rotating local exhibitions, specialty lattes, and DJ sets on weekends.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🍵",label:"Japanese Tea House",text:"Serene Japanese tea house serving matcha ceremonies, wagashi sweets, and seasonal kaiseki sets in a bamboo garden setting.",style:"minimalist",mood:"warm",tone:"luxurious"},
        {icon:"🐱",label:"Cat Café",text:"Whimsical cat café with adoptable rescue cats, themed drinks, and cozy nooks for reading while furry friends roam freely.",style:"playful",mood:"pastel",tone:"friendly"},
        {icon:"💻",label:"Digital Nomad Hub",text:"Co-working café with fast Wi-Fi, standing desks, meeting pods, specialty coffee, and monthly networking mixers for remote workers.",style:"futuristic",mood:"cool",tone:"casual"},
        {icon:"🧇",label:"Brunch Spot",text:"Instagram-worthy brunch café with Belgian waffles, açaí bowls, bottomless mimosas, and a flower wall photo backdrop.",style:"playful",mood:"colorful",tone:"friendly"},
    ],
    bar: [
        {icon:"🍸",label:"Cocktail Lounge",text:"Upscale cocktail lounge with art deco interior, bespoke cocktails crafted by award-winning mixologists, and live jazz.",style:"artdeco",mood:"dark",tone:"luxurious"},
        {icon:"🍺",label:"Craft Beer Pub",text:"Neighborhood craft beer pub with 30 rotating taps, rustic industrial decor, pub quiz nights, and a dog-friendly patio.",style:"bold",mood:"warm",tone:"casual"},
        {icon:"🍷",label:"Wine Bar",text:"Intimate wine bar with sommelier-curated selection, candlelit cellar atmosphere, cheese boards, and weekly tasting events.",style:"elegant",mood:"dark",tone:"luxurious"},
        {icon:"🎵",label:"Music Bar",text:"Underground music venue and bar with neon-lit interior, live bands, DJ nights, craft cocktails, and late-night kitchen.",style:"brutalist",mood:"neon",tone:"casual"},
        {icon:"🥃",label:"Whiskey Library",text:"Gentleman's whiskey library with 200+ rare bottles, leather wingback chairs, cigar terrace, and guided tasting flights.",style:"elegant",mood:"dark",tone:"luxurious"},
        {icon:"🍹",label:"Tiki Bar",text:"Retro tiki bar with carved totems, flaming cocktails, rum flights from 30 Caribbean islands, and themed luau nights.",style:"retro",mood:"colorful",tone:"casual"},
        {icon:"🎮",label:"Gaming Bar",text:"Arcade gaming bar with retro consoles, pinball machines, board game library, craft beers, and esports viewing parties.",style:"neubrutalism",mood:"neon",tone:"witty"},
        {icon:"🍶",label:"Sake Lounge",text:"Intimate Japanese sake lounge with curated seasonal pours, izakaya small plates, and a minimalist zen garden patio.",style:"minimalist",mood:"warm",tone:"luxurious"},
    ],
    bakery: [
        {icon:"🥐",label:"French Patisserie",text:"Elegant French patisserie with marble counters, handcrafted croissants, macarons, and custom celebration cakes. Parisian charm.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"🍞",label:"Artisan Bakery",text:"Rustic artisan bakery with stone oven, sourdough breads, organic flours, and Saturday morning farmers market stall.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🎂",label:"Custom Cake Studio",text:"Creative custom cake studio specializing in wedding cakes, themed birthday cakes, and intricate sugar art. Portfolio-driven.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🧁",label:"Cupcake Boutique",text:"Charming cupcake boutique with pastel decor, seasonal flavors, DIY decorating workshops, and catering for events.",style:"playful",mood:"pastel",tone:"friendly"},
        {icon:"🥯",label:"Bagel Shop",text:"New York-style bagel shop with wood-fired ovens, house-cured lox, schmear flights, and early-bird breakfast specials.",style:"retro",mood:"warm",tone:"casual"},
        {icon:"🍩",label:"Donut Lab",text:"Experimental donut laboratory with wild flavor combinations, vegan options, and a glass-walled kitchen showing the magic.",style:"neubrutalism",mood:"colorful",tone:"witty"},
        {icon:"🫓",label:"Gluten-Free Bakery",text:"Dedicated gluten-free bakery with celiac-safe facility, allergen transparency, fresh daily bread, and online nationwide delivery.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🍰",label:"Cheesecake Factory",text:"Artisan cheesecake shop with 30+ flavors, seasonal specials, corporate gifting program, and same-day local delivery service.",style:"elegant",mood:"luxury",tone:"luxurious"},
    ],
    foodtruck: [
        {icon:"🚚",label:"Gourmet Burger Truck",text:"Award-winning gourmet burger food truck with smash burgers, truffle fries, and craft sodas. Festival circuit staple and catering.",style:"bold",mood:"warm",tone:"casual"},
        {icon:"🌮",label:"Taco Truck",text:"Authentic street taco truck with handmade tortillas, slow-cooked meats, five house salsas, and horchata. Catering for events.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🍕",label:"Wood-Fired Pizza Van",text:"Mobile wood-fired pizza van with Neapolitan-style pies, fresh mozzarella, and basil from our rooftop garden. Weddings and festivals.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🧆",label:"Falafel Truck",text:"Middle Eastern street food truck serving fresh falafel wraps, shawarma plates, hummus bowls, and baklava. Vegan-friendly menu.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🍦",label:"Ice Cream Van",text:"Artisan ice cream truck with liquid-nitrogen made-to-order flavors, waffle cones, and affogato bar. Private events and festivals.",style:"playful",mood:"pastel",tone:"friendly"},
        {icon:"🥡",label:"Asian Fusion Truck",text:"Asian fusion food truck with bao buns, ramen bowls, kimchi fries, and bubble tea. Pop-up locations posted daily on social.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"🥩",label:"BBQ Smoker Truck",text:"Competition BBQ smoker truck with 12-hour brisket, pulled pork, mac and cheese, and Carolina vinegar sauce. Catering specialists.",style:"retro",mood:"warm",tone:"casual"},
        {icon:"🍳",label:"Breakfast Truck",text:"Early-morning breakfast food truck with loaded burritos, avocado toast, specialty coffee, and fresh juice. Office park circuit.",style:"minimalist",mood:"light",tone:"friendly"},
    ],
    hotel: [
        {icon:"🏨",label:"Boutique Hotel",text:"Luxury boutique hotel in a historic building, 20 individually designed rooms, rooftop bar, and concierge service. Heritage meets modern comfort.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🏔️",label:"Mountain Lodge",text:"Alpine mountain lodge with panoramic views, spa facilities, farm-to-table restaurant, and guided hiking tours.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🏖️",label:"Beach Resort",text:"Tropical beach resort with overwater bungalows, infinity pool, dive center, and sunset cocktail ceremonies.",style:"minimalist",mood:"light",tone:"luxurious"},
        {icon:"🏛️",label:"Art Deco Hotel",text:"1920s Art Deco grand hotel with gilded lobbies, ballroom, fine dining, and roaring twenties themed events.",style:"artdeco",mood:"luxury",tone:"luxurious"},
        {icon:"🏕️",label:"Eco Lodge",text:"Off-grid eco lodge in the rainforest canopy with solar power, wildlife tours, organic farm dinners, and treehouse suites.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🌃",label:"City Design Hotel",text:"Ultra-modern city design hotel with rooftop infinity pool, co-working lobby, smart rooms, and rotating art exhibitions.",style:"futuristic",mood:"dark",tone:"casual"},
        {icon:"🏰",label:"Castle Hotel",text:"Converted medieval castle hotel with banquet halls, falconry experiences, candlelit dining, and countryside horseback riding.",style:"elegant",mood:"warm",tone:"luxurious"},
        {icon:"❄️",label:"Ice Hotel",text:"Seasonal ice hotel rebuilt each winter with sculpted suites, northern lights viewing deck, ice bar, and husky sled tours.",style:"brutalist",mood:"cool",tone:"luxurious"},
    ],

    // ═══════════════════════════════════════════════════════
    // TECH & DIGITAL
    // ═══════════════════════════════════════════════════════
    saas: [
        {icon:"📊",label:"Analytics Platform",text:"AI-powered analytics platform for e-commerce. Real-time dashboards, predictive insights, and one-click integrations with Shopify and WooCommerce.",style:"minimalist",mood:"dark",tone:"professional"},
        {icon:"💬",label:"Team Chat App",text:"Modern team communication platform with channels, threads, video calls, and 500+ integrations. Built for remote-first teams.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"📋",label:"Project Management",text:"Intuitive project management tool with Kanban boards, Gantt charts, time tracking, and AI-powered sprint planning.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🔒",label:"Security Suite",text:"Enterprise cybersecurity platform with threat detection, compliance monitoring, and zero-trust architecture. SOC 2 certified.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🎯",label:"CRM Platform",text:"AI-powered CRM for sales teams with pipeline automation, lead scoring, email sequences, and revenue forecasting dashboards.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"📧",label:"Email Marketing Tool",text:"Drag-and-drop email marketing platform with smart segmentation, A/B testing, automation workflows, and deliverability optimization.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🗂️",label:"HR Management",text:"All-in-one HR platform with payroll, benefits administration, performance reviews, and employee engagement surveys for growing teams.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"🎨",label:"Design Collaboration",text:"Real-time design collaboration tool with version control, developer handoff, design tokens, and interactive prototyping for product teams.",style:"glassmorphism",mood:"pastel",tone:"casual"},
    ],
    startup: [
        {icon:"🚀",label:"Fintech Startup",text:"Next-gen digital banking platform for freelancers. Instant invoicing, multi-currency accounts, and AI expense categorization.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🧬",label:"Biotech Startup",text:"Biotech startup pioneering personalized medicine through genomic analysis. Transforming healthcare one genome at a time.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🌍",label:"CleanTech",text:"Clean energy startup building affordable solar microgrids for emerging markets. Impact-driven, technology-powered.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🤖",label:"AI Startup",text:"AI automation platform that turns natural language into working code. Empowering non-technical founders to build their MVPs.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"🛰️",label:"SpaceTech Startup",text:"Satellite data startup providing real-time Earth observation analytics for agriculture, insurance, and climate risk assessment.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🍽️",label:"FoodTech Startup",text:"Ghost kitchen platform connecting restaurants with delivery-only brands, AI menu optimization, and shared commercial kitchen spaces.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🏥",label:"HealthTech Startup",text:"Telehealth startup connecting patients with specialists via AI triage, instant video consultations, and digital prescription delivery.",style:"minimalist",mood:"cool",tone:"friendly"},
        {icon:"📦",label:"Logistics Startup",text:"Last-mile delivery platform using autonomous robots and AI route optimization for same-day e-commerce fulfillment in urban areas.",style:"futuristic",mood:"dark",tone:"professional"},
    ],
    cybersecurity: [
        {icon:"🛡️",label:"Endpoint Protection",text:"Next-gen endpoint protection platform with AI threat detection, zero-day exploit prevention, and automated incident response for enterprises.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🔐",label:"Identity & Access",text:"Zero-trust identity management with passwordless authentication, SSO, biometric verification, and compliance reporting for regulated industries.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🕵️",label:"Penetration Testing",text:"Ethical hacking and penetration testing firm offering red team exercises, vulnerability assessments, and compliance audits for Fortune 500.",style:"brutalist",mood:"dark",tone:"professional"},
        {icon:"📡",label:"Network Security",text:"Enterprise network security with next-gen firewall, intrusion detection, DDoS mitigation, and 24/7 SOC monitoring services.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🔍",label:"Threat Intelligence",text:"Cyber threat intelligence platform aggregating dark web monitoring, breach alerts, and APT tracking for security operations teams.",style:"futuristic",mood:"neon",tone:"professional"},
        {icon:"📋",label:"Compliance Platform",text:"Automated compliance platform for SOC 2, ISO 27001, GDPR, and HIPAA. Evidence collection, gap analysis, and audit-ready reports.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🎓",label:"Security Training",text:"Cybersecurity awareness training platform with phishing simulations, interactive modules, and compliance tracking for employees.",style:"bold",mood:"cool",tone:"friendly"},
        {icon:"💾",label:"Backup & Recovery",text:"Cloud backup and disaster recovery platform with ransomware protection, instant failover, and one-click restore for businesses.",style:"corporate",mood:"cool",tone:"professional"},
    ],
    hosting: [
        {icon:"☁️",label:"Cloud Hosting",text:"Developer-first cloud hosting with one-click deploys, auto-scaling, global CDN, and 99.99% uptime SLA for modern web apps.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🖥️",label:"Managed WordPress",text:"Managed WordPress hosting with automatic updates, daily backups, staging environments, and expert WordPress support team.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏢",label:"Dedicated Servers",text:"Bare-metal dedicated servers with custom configurations, RAID storage, DDoS protection, and 24/7 on-site engineering support.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"📧",label:"Email Hosting",text:"Professional email hosting with custom domains, 50GB mailboxes, spam filtering, encryption, and Microsoft 365 integration.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"🎮",label:"Game Server Hosting",text:"Low-latency game server hosting for Minecraft, ARK, and Valheim with mod support, instant setup, and DDoS protection.",style:"bold",mood:"neon",tone:"casual"},
        {icon:"🛒",label:"E-commerce Hosting",text:"Optimized e-commerce hosting with PCI compliance, SSL certificates, one-click WooCommerce/Magento install, and performance monitoring.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"📊",label:"VPS Hosting",text:"High-performance VPS hosting with NVMe storage, full root access, snapshots, and automatic failover across global data centers.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🌐",label:"Domain Registrar",text:"Domain registrar with AI name suggestions, privacy protection, DNS management, and bulk pricing for agencies and resellers.",style:"bold",mood:"cool",tone:"friendly"},
    ],

    // ═══════════════════════════════════════════════════════
    // CREATIVE & MEDIA
    // ═══════════════════════════════════════════════════════
    portfolio: [
        {icon:"🎨",label:"Graphic Designer",text:"Freelance graphic designer specializing in brand identity, packaging design, and editorial illustration. Minimalist approach with bold accents.",style:"minimalist",mood:"monochrome",tone:"casual"},
        {icon:"💻",label:"Full-Stack Developer",text:"Senior full-stack developer portfolio showcasing web apps, open-source projects, and technical writing. Clean, code-inspired aesthetic.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🎬",label:"Motion Designer",text:"Motion designer and animator creating brand stories, explainer videos, and social media content. Playful with cinematic quality.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"📐",label:"UX Designer",text:"UX/UI designer focused on accessible, human-centered design. Case studies, design system work, and speaking engagements.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🖊️",label:"Calligrapher",text:"Master calligrapher and lettering artist offering wedding invitations, brand logotypes, murals, and online workshop tutorials.",style:"editorial",mood:"warm",tone:"luxurious"},
        {icon:"🎻",label:"Musician Portfolio",text:"Professional cellist and composer portfolio with concert recordings, film score credits, commission inquiries, and tour schedule.",style:"elegant",mood:"dark",tone:"professional"},
        {icon:"🪡",label:"Textile Artist",text:"Contemporary textile artist creating large-scale woven installations, tapestries, and fiber art for galleries and public commissions.",style:"organic",mood:"earth",tone:"casual"},
        {icon:"🏗️",label:"3D Artist",text:"Freelance 3D artist specializing in architectural visualization, product renders, and immersive virtual environments for brands.",style:"futuristic",mood:"neon",tone:"professional"},
    ],
    photography: [
        {icon:"📸",label:"Wedding Photographer",text:"Fine art wedding photographer capturing timeless, editorial-style moments. Destination weddings across Europe. Muted, cinematic palette.",style:"editorial",mood:"monochrome",tone:"luxurious"},
        {icon:"🌃",label:"Street Photographer",text:"Urban street photographer documenting city life in raw black and white. Gritty, authentic, unposed. Gallery exhibitions worldwide.",style:"brutalist",mood:"monochrome",tone:"casual"},
        {icon:"🍔",label:"Food Photographer",text:"Commercial food and beverage photographer for restaurants, cookbooks, and brands. Warm, appetizing, story-driven imagery.",style:"elegant",mood:"warm",tone:"professional"},
        {icon:"🏔️",label:"Landscape Photographer",text:"Nature and landscape photographer selling fine art prints. Dramatic wide-angle vistas, long exposures, and aerial drone work.",style:"minimalist",mood:"earth",tone:"professional"},
        {icon:"👶",label:"Newborn Photographer",text:"Specialist newborn and family portrait photographer with a cozy home studio, organic props, and gentle posing techniques.",style:"organic",mood:"pastel",tone:"friendly"},
        {icon:"🏠",label:"Real Estate Photographer",text:"Architectural and real estate photographer offering HDR interiors, twilight exteriors, drone aerials, and Matterport 3D tours.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"🐾",label:"Pet Photographer",text:"Professional pet photographer capturing personality-filled portraits in studio and outdoor settings, with patience and endless treats.",style:"playful",mood:"colorful",tone:"witty"},
        {icon:"📰",label:"Photojournalist",text:"Award-winning photojournalist documenting conflict zones, social movements, and humanitarian stories for international publications.",style:"brutalist",mood:"dark",tone:"professional"},
    ],
    videography: [
        {icon:"🎥",label:"Wedding Videographer",text:"Cinematic wedding videographer creating emotional films with drone aerials, same-day edits, and documentary-style storytelling.",style:"editorial",mood:"warm",tone:"luxurious"},
        {icon:"🎬",label:"Commercial Director",text:"Commercial video director producing brand films, product launches, and social media campaigns for fashion and tech clients.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"📹",label:"Documentary Filmmaker",text:"Independent documentary filmmaker exploring human stories of resilience, culture, and social justice. Festival circuit and streaming platforms.",style:"brutalist",mood:"monochrome",tone:"professional"},
        {icon:"🎙️",label:"YouTube Creator",text:"YouTube content creator studio with professional filming, editing, thumbnail design, and channel growth strategy for creators.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🏠",label:"Real Estate Video",text:"Real estate videography with cinematic property tours, drone flyovers, agent interviews, and social media reels for listings.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🎓",label:"Course Video Producer",text:"E-learning video production studio creating professional online course content with animations, screen recordings, and studio presentations.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🎪",label:"Event Videographer",text:"Live event videography for conferences, concerts, and corporate galas with multi-camera setups, live streaming, and highlight reels.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"✈️",label:"Drone Cinematographer",text:"Specialist drone cinematographer with FAA Part 107, FPV racing drones, and cinema-grade aerial cameras for film and real estate.",style:"futuristic",mood:"cool",tone:"professional"},
    ],
    agency: [
        {icon:"🎯",label:"Digital Marketing Agency",text:"Full-service digital marketing agency specializing in SEO, PPC, social media, and conversion optimization for DTC brands.",style:"bold",mood:"colorful",tone:"professional"},
        {icon:"✏️",label:"Branding Studio",text:"Boutique branding studio creating distinctive visual identities for premium brands. Strategy, design, and storytelling under one roof.",style:"elegant",mood:"dark",tone:"luxurious"},
        {icon:"📱",label:"App Development Agency",text:"Mobile app development agency building native iOS and Android apps for startups and enterprises. Agile, transparent, results-driven.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🎪",label:"Creative Studio",text:"Experimental creative studio blending art direction, interactive design, and immersive experiences. We break conventions.",style:"neubrutalism",mood:"colorful",tone:"witty"},
        {icon:"📣",label:"PR Agency",text:"Strategic public relations agency managing media relations, crisis communications, influencer partnerships, and brand reputation for tech clients.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🎥",label:"Video Production",text:"Cinematic video production agency creating commercials, documentaries, branded content, and social-first vertical video campaigns.",style:"editorial",mood:"dark",tone:"professional"},
        {icon:"🔍",label:"SEO Agency",text:"Data-driven SEO agency with technical audits, content strategy, link building, and measurable organic growth for B2B SaaS companies.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"🎨",label:"UX Design Agency",text:"Human-centered UX design agency crafting intuitive digital products through research sprints, prototyping, and usability testing.",style:"glassmorphism",mood:"light",tone:"friendly"},
    ],
    tattoo: [
        {icon:"🖤",label:"Fine Line Studio",text:"Fine line tattoo studio specializing in delicate botanical, geometric, and minimalist designs. Private studio, appointment only.",style:"minimalist",mood:"monochrome",tone:"casual"},
        {icon:"🐉",label:"Japanese Traditional",text:"Traditional Japanese tattoo master (Tebori) creating full-sleeve irezumi with dragons, koi, and cherry blossoms using hand-poke technique.",style:"elegant",mood:"dark",tone:"professional"},
        {icon:"💀",label:"Blackwork Specialist",text:"Blackwork tattoo specialist with bold tribal, ornamental, and dotwork designs. Custom geometric mandalas and dark art compositions.",style:"brutalist",mood:"dark",tone:"casual"},
        {icon:"🌹",label:"Neo-Traditional Artist",text:"Neo-traditional tattoo artist creating bold, colorful pieces with modern twists on classic Americana, florals, and portrait work.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🎨",label:"Watercolor Tattoos",text:"Watercolor and illustrative tattoo artist creating painterly, flowing pieces with no outlines. Abstract art meets body canvas.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"✍️",label:"Script & Lettering",text:"Script and lettering tattoo specialist with calligraphy, gothic, typewriter, and handwritten styles. Memorial tributes and quotes.",style:"editorial",mood:"warm",tone:"friendly"},
        {icon:"🔬",label:"Micro-Realism Studio",text:"Micro-realism tattoo studio creating photorealistic tiny portraits, animals, and objects with incredible detail using single-needle technique.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"⚡",label:"Walk-In Tattoo Shop",text:"Classic street-shop tattoo parlor with flash designs, walk-ins welcome, piercing services, and Friday the 13th flash sales.",style:"retro",mood:"warm",tone:"casual"},
    ],
    architecture: [
        {icon:"🏗️",label:"Modern Architecture",text:"Award-winning architecture firm designing sustainable commercial and residential spaces. Clean geometry meets environmental responsibility.",style:"geometric",mood:"light",tone:"professional"},
        {icon:"🏠",label:"Residential Architect",text:"Boutique residential architecture practice specializing in custom homes, renovations, and heritage restorations.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🌆",label:"Urban Design",text:"Urban planning and architecture studio shaping cities through mixed-use developments, public spaces, and transit-oriented design.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🌿",label:"Sustainable Architecture",text:"Eco-focused architecture firm building passive houses, green roofs, and net-zero commercial buildings. LEED Platinum specialists.",style:"organic",mood:"earth",tone:"professional"},
        {icon:"🏢",label:"Skyscraper Design",text:"International architecture practice designing iconic skyscrapers, mixed-use towers, and landmark cultural buildings on three continents.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🏛️",label:"Heritage Restoration",text:"Conservation architects restoring listed buildings, Gothic churches, and Victorian landmarks with period-accurate craftsmanship.",style:"elegant",mood:"warm",tone:"formal"},
        {icon:"🎪",label:"Pavilion Architecture",text:"Experimental architecture studio building temporary pavilions, pop-up installations, and parametric structures using computational design.",style:"brutalist",mood:"monochrome",tone:"witty"},
        {icon:"🏡",label:"Tiny Home Designer",text:"Specialist tiny home architects designing off-grid micro-dwellings, container conversions, and mobile living spaces under 40 sqm.",style:"playful",mood:"light",tone:"casual"},
    ],
    interior: [
        {icon:"🛋️",label:"Luxury Interior Design",text:"High-end interior design studio creating bespoke residential interiors with custom furniture, art curation, and turnkey project management.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🏢",label:"Office Interior Design",text:"Commercial interior design firm specializing in open-plan offices, co-working spaces, and biophilic workplace environments for tech companies.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🏨",label:"Hospitality Design",text:"Hospitality interior design studio creating immersive hotel lobbies, restaurant concepts, and boutique retail experiences worldwide.",style:"artdeco",mood:"warm",tone:"luxurious"},
        {icon:"🌿",label:"Sustainable Interiors",text:"Eco-conscious interior design using reclaimed materials, non-toxic finishes, energy-efficient lighting, and biophilic design principles.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🏠",label:"Home Staging",text:"Professional home staging company transforming empty properties into aspirational spaces using rented furniture and strategic styling.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🎨",label:"Color Consultant",text:"Architectural color consultant advising on paint palettes, material finishes, and lighting plans for residential and commercial projects.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🪴",label:"Scandinavian Design",text:"Scandinavian interior design studio with hygge-inspired spaces featuring natural materials, neutral tones, and functional minimalism.",style:"minimalist",mood:"warm",tone:"casual"},
        {icon:"💎",label:"Art Deco Specialist",text:"Art Deco interior specialist recreating glamorous 1920s interiors with gold accents, geometric patterns, and velvet upholstery for luxury homes.",style:"artdeco",mood:"luxury",tone:"luxurious"},
    ],

    // ═══════════════════════════════════════════════════════
    // CONTENT & PUBLISHING
    // ═══════════════════════════════════════════════════════
    blog: [
        {icon:"✍️",label:"Tech Blog",text:"Personal tech blog covering web development, AI tools, and open-source projects. Code tutorials, opinion pieces, and project logs.",style:"neubrutalism",mood:"dark",tone:"casual"},
        {icon:"🧳",label:"Travel Blog",text:"Solo travel blog documenting adventures across Southeast Asia and South America. Photography-heavy, authentic storytelling.",style:"editorial",mood:"warm",tone:"casual"},
        {icon:"🍳",label:"Food Blog",text:"Home cooking blog with tested recipes, step-by-step photos, and seasonal meal planning guides. Warm, accessible, family-friendly.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"💰",label:"Finance Blog",text:"Personal finance blog teaching millennials about investing, FIRE movement, budgeting tools, and crypto basics. No jargon.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🎮",label:"Gaming Blog",text:"Indie gaming blog with honest reviews, developer interviews, retro game retrospectives, and weekly podcast episodes about game design.",style:"bold",mood:"neon",tone:"witty"},
        {icon:"🌿",label:"Wellness Blog",text:"Holistic wellness blog covering meditation, breathwork, herbalism, and slow living. Beautifully photographed guides and seasonal rituals.",style:"organic",mood:"pastel",tone:"friendly"},
        {icon:"🏋️",label:"Fitness Blog",text:"Evidence-based fitness blog with workout programming, nutrition science breakdowns, supplement reviews, and transformation case studies.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"🔬",label:"Science Blog",text:"Popular science blog explaining quantum physics, space exploration, and biotech breakthroughs through illustrated explainers and animations.",style:"futuristic",mood:"cool",tone:"witty"},
    ],
    podcast: [
        {icon:"🎙️",label:"True Crime Podcast",text:"True crime podcast investigating cold cases with original research, police interviews, and crowdsourced tips. Weekly episodes with companion blog.",style:"brutalist",mood:"dark",tone:"professional"},
        {icon:"💼",label:"Business Podcast",text:"Weekly business podcast interviewing startup founders, VCs, and CEOs about scaling, fundraising, and leadership lessons learned.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"😂",label:"Comedy Podcast",text:"Irreverent comedy podcast with two hosts riffing on pop culture, listener stories, and absurd hypotheticals. Live tour shows.",style:"bold",mood:"colorful",tone:"witty"},
        {icon:"🧠",label:"Science Podcast",text:"Science communication podcast explaining cutting-edge research in AI, neuroscience, and climate with expert guests and clear analogies.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"🎵",label:"Music Podcast",text:"Music discovery podcast with artist interviews, album deep-dives, and curated Spotify playlists spanning indie, jazz, and electronic.",style:"retro",mood:"warm",tone:"casual"},
        {icon:"📰",label:"News Commentary",text:"Daily news commentary podcast with balanced analysis, expert panels, and listener Q&A on politics, tech, and global affairs.",style:"minimalist",mood:"dark",tone:"professional"},
        {icon:"❤️",label:"Relationships Podcast",text:"Modern relationships podcast discussing dating, communication, attachment styles, and therapy insights with licensed psychologist co-host.",style:"organic",mood:"pastel",tone:"friendly"},
        {icon:"🏃",label:"Fitness Podcast",text:"Endurance sports podcast with marathon training advice, nutrition science, gear reviews, and interviews with elite athletes and coaches.",style:"bold",mood:"earth",tone:"casual"},
    ],
    magazine: [
        {icon:"📰",label:"Digital Magazine",text:"Premium digital magazine covering culture, design, and innovation with long-form features, photo essays, and interactive storytelling.",style:"editorial",mood:"monochrome",tone:"professional"},
        {icon:"👗",label:"Fashion Magazine",text:"Online fashion magazine with runway coverage, street style galleries, designer interviews, and shoppable editorial looks.",style:"elegant",mood:"dark",tone:"luxurious"},
        {icon:"🏠",label:"Home & Design Magazine",text:"Architecture and interior design magazine with house tours, renovation stories, product guides, and designer profiles.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🍴",label:"Food & Drink Magazine",text:"Food and drink magazine with chef profiles, restaurant reviews, seasonal recipes, and wine region travel guides.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"💪",label:"Health & Fitness Magazine",text:"Digital health magazine with workout plans, nutrition guides, mental wellness features, and expert medical advice columns.",style:"bold",mood:"cool",tone:"friendly"},
        {icon:"🌍",label:"Travel Magazine",text:"Luxury travel magazine with destination guides, hotel reviews, packing lists, and photography-driven stories from six continents.",style:"elegant",mood:"warm",tone:"luxurious"},
        {icon:"💻",label:"Tech Magazine",text:"Technology magazine covering startups, gadget reviews, AI deep-dives, and future-of-work features for a curious, non-technical audience.",style:"futuristic",mood:"dark",tone:"casual"},
        {icon:"🎵",label:"Music Magazine",text:"Independent music magazine with album reviews, emerging artist spotlights, concert photography, and vinyl collector culture.",style:"retro",mood:"warm",tone:"casual"},
    ],

    // ═══════════════════════════════════════════════════════
    // COMMERCE & RETAIL
    // ═══════════════════════════════════════════════════════
    jewelry: [
        {icon:"💍",label:"Engagement Ring Studio",text:"Bespoke engagement ring studio with conflict-free diamonds, custom CAD design service, and private appointment-only showroom.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"⌚",label:"Luxury Watch Dealer",text:"Authorized luxury watch dealer carrying Rolex, Omega, and Patek Philippe. Certified pre-owned, servicing, and investment advisory.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"💎",label:"Gemstone Specialist",text:"Independent gemstone dealer importing sapphires, emeralds, and rubies direct from mines. GIA-certified stones with provenance tracking.",style:"minimalist",mood:"dark",tone:"professional"},
        {icon:"📿",label:"Pearl Jewelry House",text:"Heritage pearl jewelry house with Akoya, Tahitian, and South Sea collections. Three generations of craftsmanship in Hatton Garden.",style:"elegant",mood:"warm",tone:"luxurious"},
        {icon:"🪙",label:"Vintage Jewelry",text:"Curated vintage and antique jewelry from Art Deco, Victorian, and Mid-Century eras. Each piece authenticated with detailed provenance.",style:"artdeco",mood:"warm",tone:"luxurious"},
        {icon:"✨",label:"Custom Jewelry Designer",text:"Independent jewelry designer creating one-of-a-kind pieces in gold and silver. Wax carving, lost-wax casting, and hand-set stones.",style:"organic",mood:"earth",tone:"casual"},
        {icon:"👑",label:"Bridal Jewelry Collection",text:"Bridal jewelry collection with tiaras, necklace sets, earrings, and hair accessories. Swarovski crystals and freshwater pearls.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"🔗",label:"Men's Accessories",text:"Modern men's jewelry brand with minimalist chains, signet rings, leather bracelets, and cufflinks. Sterling silver and titanium.",style:"geometric",mood:"dark",tone:"professional"},
    ],
    beauty: [
        {icon:"🧴",label:"Clean Skincare Brand",text:"Clean beauty skincare brand with science-backed formulas, sustainable packaging, and personalized routine builder. Dermatologist approved.",style:"minimalist",mood:"pastel",tone:"friendly"},
        {icon:"🌸",label:"Natural Cosmetics",text:"Organic cosmetics line made from botanical extracts and plant oils. Cruelty-free, vegan, and packaged in recyclable glass.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🧪",label:"K-Beauty Store",text:"Korean beauty e-shop with curated K-beauty essentials: sheet masks, serums, essences, and 10-step routine kits with expert guides.",style:"playful",mood:"pastel",tone:"friendly"},
        {icon:"💅",label:"Nail Art Studio",text:"Creative nail art studio with gel extensions, nail art workshops, bridal packages, and a monthly subscription box of nail designs.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🧔",label:"Men's Grooming",text:"Premium men's grooming brand with beard oils, shaving kits, cologne, and skincare formulated for men. Barbershop heritage aesthetic.",style:"retro",mood:"dark",tone:"professional"},
        {icon:"🌹",label:"Perfume House",text:"Artisan perfume house creating small-batch fragrances from rare ingredients. Bespoke scent consultations and engraved crystal bottles.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"💇",label:"Hair Care Line",text:"Professional hair care brand for salons with sulfate-free shampoos, bond repair treatments, and color protection formulas.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"💄",label:"Makeup Artist Shop",text:"Pro makeup artist's curated shop with stage-quality foundations, pigment palettes, and online masterclass tutorials for aspiring MUAs.",style:"editorial",mood:"dark",tone:"casual"},
    ],
    furniture: [
        {icon:"🛋️",label:"Modern Furniture Store",text:"Scandinavian-inspired modern furniture store with clean-lined sofas, dining sets, and storage. Flat-pack and assembled delivery options.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🪑",label:"Antique Dealer",text:"Specialist antique furniture dealer with Georgian, Regency, and Victorian pieces. Full restoration workshop and nationwide delivery.",style:"elegant",mood:"warm",tone:"formal"},
        {icon:"🪵",label:"Custom Woodworker",text:"Bespoke woodworking studio crafting live-edge dining tables, floating shelves, and heirloom furniture from sustainably sourced timber.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🍳",label:"Kitchen Showroom",text:"Premium kitchen showroom with German-engineered cabinetry, quartz worktops, integrated appliances, and 3D design consultation service.",style:"geometric",mood:"light",tone:"professional"},
        {icon:"💼",label:"Office Furniture",text:"Ergonomic office furniture supplier with standing desks, task chairs, acoustic pods, and free workspace planning for businesses.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🌳",label:"Outdoor Furniture",text:"Teak and aluminum outdoor furniture collection with garden dining sets, sun loungers, parasols, and all-weather cushion fabrics.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"💡",label:"Lighting Design Studio",text:"Architectural lighting design studio with handblown glass pendants, sculptural floor lamps, and smart lighting systems for luxury homes.",style:"artdeco",mood:"dark",tone:"luxurious"},
        {icon:"🧶",label:"Artisan Rug Gallery",text:"Handwoven rug gallery with Moroccan Berber, Turkish kilim, and Persian silk collections. Custom sizing and restoration services.",style:"elegant",mood:"warm",tone:"luxurious"},
    ],
    electronics: [
        {icon:"🎧",label:"Audiophile Store",text:"Premium headphone and hi-fi audio equipment store with expert reviews, comparison tools, and audiophile community forum.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🖥️",label:"Custom PC Builder",text:"Custom gaming PC and workstation builder with online configurator, benchmark tests, cable management, and 3-year warranty.",style:"bold",mood:"neon",tone:"casual"},
        {icon:"📱",label:"Phone Accessories",text:"Premium phone cases, wireless chargers, screen protectors, and MagSafe accessories with minimalist design philosophy.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🎮",label:"Gaming Gear Shop",text:"Gaming peripherals shop with mechanical keyboards, ultrawide monitors, racing sim rigs, and RGB accessories for enthusiasts.",style:"neubrutalism",mood:"neon",tone:"casual"},
        {icon:"🏠",label:"Smart Home Devices",text:"Smart home technology store with voice assistants, automated blinds, security cameras, and whole-home integration consulting.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"📷",label:"Camera Store",text:"Professional camera store with DSLRs, mirrorless bodies, cinema lenses, and studio lighting. Trade-in program and rental service.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🚁",label:"Drone Shop",text:"Consumer and professional drone store with aerial photography drones, FPV racing kits, parts, and certified pilot training courses.",style:"futuristic",mood:"cool",tone:"casual"},
        {icon:"⚡",label:"EV Accessories",text:"Electric vehicle accessory shop with home chargers, portable power stations, dash cams, and Model-specific interior upgrades.",style:"geometric",mood:"light",tone:"professional"},
    ],
    pets_shop: [
        {icon:"🐕",label:"Premium Dog Food",text:"Grain-free, human-grade dog food brand with vet-formulated recipes, breed-specific nutrition plans, and subscription delivery.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🐠",label:"Aquarium Specialist",text:"Tropical fish and reef aquarium specialist with livestock, live coral, equipment, and custom tank design and maintenance service.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🐾",label:"Dog Accessories Boutique",text:"Designer dog accessories boutique with leather collars, cashmere sweaters, organic treats, and matching owner-pet collections.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"🐱",label:"Cat Boutique",text:"Dedicated cat boutique with modern cat trees, interactive toys, grain-free treats, and subscription boxes curated by cat behaviorists.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🐦",label:"Bird & Aviary Supplies",text:"Specialist bird supply store with cages, aviaries, seed mixes, toys, and expert advice for parrots, finches, and canaries.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"💊",label:"Pet Pharmacy Online",text:"Licensed online pet pharmacy with prescription medications, flea treatments, supplements, and vet telehealth consultations.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🐴",label:"Equestrian Tack Shop",text:"Premium equestrian tack shop with English and Western saddles, bridles, riding apparel, and custom monogramming for show riders.",style:"elegant",mood:"earth",tone:"professional"},
        {icon:"🦎",label:"Exotic Pet Supplies",text:"Exotic pet supply store for reptiles, amphibians, and invertebrates. Terrariums, UV lighting, live feeders, and care guides.",style:"bold",mood:"dark",tone:"casual"},
    ],
    florist: [
        {icon:"💐",label:"Wedding Florist",text:"Luxury wedding florist creating bespoke bridal bouquets, ceremony arches, and reception centerpieces. Consultation and mood board service.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"🌷",label:"Flower Subscription",text:"Weekly flower subscription delivering seasonal blooms from local farms. Choose your style: wild, classic, or exotic arrangements.",style:"organic",mood:"colorful",tone:"friendly"},
        {icon:"🌻",label:"Garden Center",text:"Family-run garden center with perennials, shrubs, trees, seeds, and expert gardening advice. Weekend workshops and kids' planting days.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🌱",label:"Plant Nursery",text:"Specialist houseplant nursery with rare tropicals, succulents, and bonsai. Plant care consultations and repotting service included.",style:"minimalist",mood:"light",tone:"casual"},
        {icon:"🪴",label:"Succulent Studio",text:"Boutique succulent and cactus studio with DIY terrarium kits, wedding favors, corporate gifts, and online plant care courses.",style:"playful",mood:"warm",tone:"friendly"},
        {icon:"🌾",label:"Dried Flower Atelier",text:"Dried flower atelier creating everlasting arrangements, wreaths, and wedding installations using naturally preserved botanicals and grasses.",style:"editorial",mood:"earth",tone:"luxurious"},
        {icon:"🎁",label:"Botanical Gift Shop",text:"Botanical-themed gift shop with scented candles, botanical prints, herb growing kits, and pressed flower jewelry.",style:"retro",mood:"warm",tone:"friendly"},
        {icon:"🏺",label:"Terrarium Maker",text:"Handcrafted terrarium studio creating miniature ecosystems in glass vessels. Workshop experiences and corporate team-building events.",style:"geometric",mood:"light",tone:"casual"},
    ],
    grocery: [
        {icon:"🥬",label:"Organic Grocer",text:"Certified organic grocery with locally sourced produce, bulk bins, refill stations, and zero-waste packaging commitment.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🧀",label:"Artisan Cheese Shop",text:"Specialist cheese shop with 200+ varieties from European farms. Tasting events, cheese boards to order, and monthly cheese club.",style:"elegant",mood:"warm",tone:"luxurious"},
        {icon:"🍷",label:"Wine Merchant",text:"Independent wine merchant with hand-picked selections from small vineyards. Tasting notes, food pairing guides, and mixed cases.",style:"elegant",mood:"dark",tone:"professional"},
        {icon:"🥩",label:"Craft Butcher",text:"Traditional craft butcher with dry-aged beef, free-range poultry, house-made sausages, and weekend BBQ packs. Farm to counter.",style:"bold",mood:"warm",tone:"friendly"},
        {icon:"🫒",label:"Deli & Charcuterie",text:"Italian deli and charcuterie with imported salumi, olive oils, fresh pasta, and custom antipasti platters for events.",style:"retro",mood:"warm",tone:"casual"},
        {icon:"🌾",label:"Health Food Store",text:"Health food store with superfoods, protein supplements, vitamins, and allergen-free groceries. Nutritionist on-site for consultations.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🌶️",label:"Spice Merchant",text:"Specialist spice merchant with single-origin spices, house blends, gift sets, and recipe cards. Sourced directly from growers worldwide.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🥘",label:"Ethnic Grocery",text:"South Asian grocery with imported spices, lentils, rice, fresh produce, and ready-to-cook meal kits for authentic home cooking.",style:"playful",mood:"colorful",tone:"friendly"},
    ],
    bookshop: [
        {icon:"📚",label:"Independent Bookshop",text:"Charming independent bookshop with hand-picked fiction, poetry corner, author signings, and a resident shop cat named Hemingway.",style:"retro",mood:"warm",tone:"friendly"},
        {icon:"📖",label:"Rare Books Dealer",text:"Antiquarian book dealer with first editions, signed copies, and rare manuscripts. Climate-controlled vault and white-glove viewing.",style:"elegant",mood:"dark",tone:"formal"},
        {icon:"💥",label:"Comic Book Store",text:"Specialist comic book store with Marvel, DC, indie titles, graphic novels, manga wall, and weekly pull-list subscription service.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🧒",label:"Children's Bookshop",text:"Dedicated children's bookshop with story-time sessions, author visits, reading challenges, and age-curated book bundles.",style:"playful",mood:"pastel",tone:"friendly"},
        {icon:"🎓",label:"Academic Bookshop",text:"University bookshop with textbooks, academic journals, research papers, and discounted student bundles for course reading lists.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"🎧",label:"Audiobook Platform",text:"Audiobook streaming platform with 100,000+ titles, celebrity narrators, offline listening, and curated playlists for commuters.",style:"futuristic",mood:"dark",tone:"casual"},
        {icon:"📦",label:"Book Subscription Box",text:"Monthly book subscription box with hand-selected novels, author letters, bookish merchandise, and exclusive signed editions.",style:"playful",mood:"warm",tone:"friendly"},
        {icon:"☕",label:"Bookshop & Café",text:"Combined bookshop and café with floor-to-ceiling shelves, leather armchairs, espresso bar, and literary-themed cocktail evenings.",style:"editorial",mood:"warm",tone:"casual"},
    ],
    ecommerce: [
        {icon:"🛒",label:"General Marketplace",text:"Multi-vendor online marketplace connecting independent sellers with buyers. Curated categories, secure checkout, and seller analytics.",style:"geometric",mood:"light",tone:"professional"},
        {icon:"🎁",label:"Gift Shop",text:"Artisan gift shop with personalized engravings, curated gift boxes, corporate gifting programs, and same-day local delivery.",style:"elegant",mood:"pastel",tone:"friendly"},
        {icon:"👟",label:"Sneaker Store",text:"Online sneaker boutique with limited editions, restock alerts, and authenticated resale. Streetwear culture and collector community.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"☕",label:"Coffee Subscription",text:"Single-origin coffee subscription service with roaster profiles, brewing guides, tasting notes, and customizable delivery schedules.",style:"retro",mood:"warm",tone:"casual"},
        {icon:"🧸",label:"Toy Store",text:"Curated toy store specializing in educational wooden toys, STEM kits, and creative play sets from independent European makers.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🕯️",label:"Home Fragrance Brand",text:"Luxury home fragrance brand with hand-poured soy candles, reed diffusers, and room sprays in seasonal collections.",style:"minimalist",mood:"warm",tone:"luxurious"},
        {icon:"🎒",label:"Travel Gear Store",text:"Adventure travel gear store with carry-on luggage, packing cubes, travel accessories, and digital nomad essentials.",style:"bold",mood:"cool",tone:"casual"},
        {icon:"🧵",label:"Craft Supplies Store",text:"Online craft supply store with fabrics, yarns, sewing patterns, embroidery kits, and video tutorials for makers of all levels.",style:"playful",mood:"colorful",tone:"friendly"},
    ],
    fashion: [
        {icon:"👗",label:"Designer Fashion",text:"Contemporary women's fashion label with seasonal collections, sustainable fabrics, and a focus on timeless silhouettes.",style:"editorial",mood:"monochrome",tone:"luxurious"},
        {icon:"👔",label:"Men's Streetwear",text:"Independent streetwear brand with limited drops, artist collaborations, and a community-driven design process.",style:"brutalist",mood:"dark",tone:"casual"},
        {icon:"👶",label:"Kids' Clothing",text:"Organic cotton kids' clothing brand with playful prints, gender-neutral designs, and hand-me-down durability guarantee.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"♻️",label:"Sustainable Fashion",text:"Zero-waste fashion brand creating clothes from recycled ocean plastics and deadstock fabrics. Style meets responsibility.",style:"minimalist",mood:"earth",tone:"professional"},
        {icon:"👠",label:"Luxury Shoes",text:"Italian luxury shoe brand with hand-stitched leather, bespoke fitting service, and behind-the-scenes atelier craftsmanship stories.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🧢",label:"Hat & Accessories",text:"Artisan hat maker crafting fedoras, berets, and custom headpieces for weddings, races, and editorial fashion shoots.",style:"artdeco",mood:"warm",tone:"witty"},
        {icon:"🩱",label:"Swimwear Brand",text:"Bold swimwear brand with inclusive sizing, reversible designs, recycled nylon fabrics, and tropical resort lookbook shoots.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🧥",label:"Vintage Clothing",text:"Curated vintage clothing store with 70s disco, 80s power suits, and 90s grunge. Each piece hand-selected and authenticated.",style:"retro",mood:"warm",tone:"casual"},
    ],

    // ═══════════════════════════════════════════════════════
    // PROFESSIONAL SERVICES
    // ═══════════════════════════════════════════════════════
    law: [
        {icon:"⚖️",label:"Corporate Law Firm",text:"Prestigious corporate law firm handling M&A, securities, and international arbitration for FTSE 100 clients.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🏠",label:"Family Law Practice",text:"Compassionate family law practice specializing in divorce mediation, child custody, and prenuptial agreements. Client-first approach.",style:"elegant",mood:"warm",tone:"professional"},
        {icon:"💼",label:"Immigration Law",text:"Immigration law firm helping families and professionals navigate visas, citizenship applications, and asylum cases.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🔨",label:"Criminal Defense",text:"Aggressive criminal defense attorney with 20+ years of trial experience. DUI, white-collar crime, and federal cases.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"💡",label:"IP & Patent Law",text:"Intellectual property firm protecting inventions, trademarks, and creative works for tech startups and pharmaceutical companies.",style:"geometric",mood:"cool",tone:"formal"},
        {icon:"🌍",label:"Human Rights Law",text:"International human rights law practice litigating before European courts, defending refugees, and advocating for systemic change.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🏢",label:"Employment Law",text:"Employment law specialists handling wrongful termination, workplace discrimination, contract disputes, and executive severance negotiations.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🌐",label:"Tech & Cyber Law",text:"Digital law firm specializing in data privacy, GDPR compliance, AI regulation, and cybercrime prosecution for tech companies.",style:"futuristic",mood:"dark",tone:"professional"},
    ],
    finance: [
        {icon:"📈",label:"Wealth Management",text:"Private wealth management firm for high-net-worth individuals. Portfolio management, estate planning, and tax optimization.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🏦",label:"Private Banking",text:"Boutique private bank with exclusive investment products, family office services, and multigenerational wealth planning.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"💳",label:"Financial Planning",text:"Independent financial planning practice helping families build wealth through retirement planning, insurance, and smart investing.",style:"corporate",mood:"cool",tone:"friendly"},
        {icon:"🪙",label:"Cryptocurrency Exchange",text:"Secure crypto trading platform with spot and futures markets, staking rewards, cold wallet custody, and institutional-grade API.",style:"futuristic",mood:"neon",tone:"professional"},
        {icon:"🏠",label:"Mortgage Broker",text:"Independent mortgage broker comparing 50+ lenders, offering first-time buyer guidance, remortgage deals, and buy-to-let finance.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"📊",label:"Hedge Fund",text:"Quantitative hedge fund combining machine learning with macro strategy, delivering consistent alpha for institutional investors.",style:"geometric",mood:"dark",tone:"formal"},
        {icon:"💼",label:"Venture Capital",text:"Early-stage venture capital firm backing bold founders in climate tech, deeptech, and frontier AI with hands-on mentorship.",style:"bold",mood:"cool",tone:"professional"},
        {icon:"🏢",label:"Business Consulting",text:"Management consulting firm specializing in digital transformation, operational efficiency, and growth strategy for mid-market companies.",style:"corporate",mood:"dark",tone:"professional"},
    ],
    accounting: [
        {icon:"📒",label:"Small Business Accountant",text:"Friendly accounting firm for small businesses with bookkeeping, VAT returns, annual accounts, and cloud accounting setup with Xero.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏢",label:"Corporate Tax Advisory",text:"Corporate tax advisory firm with international structuring, transfer pricing, R&D credits, and HMRC investigation defense.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"💰",label:"Self-Assessment Tax Service",text:"Personal tax return service for freelancers and contractors. Self-assessment filing, expense optimization, and IR35 compliance advice.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"📊",label:"Forensic Accounting",text:"Forensic accounting specialists investigating financial fraud, litigation support, expert witness testimony, and asset tracing.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🏠",label:"Property Tax Specialists",text:"Property tax consultants with stamp duty advice, capital gains planning, landlord tax returns, and property company structuring.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"💻",label:"Cloud Accounting Firm",text:"Digital-first accounting firm with automated bookkeeping, real-time dashboards, monthly management reports, and advisory calls.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"🌐",label:"International Tax Planning",text:"International tax planning for expats, non-doms, and multinational businesses. Double taxation treaties and cross-border structuring.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🎯",label:"Startup CFO Services",text:"Fractional CFO service for startups with financial modeling, fundraising decks, board reporting, and investor-ready accounts.",style:"bold",mood:"cool",tone:"professional"},
    ],
    insurance: [
        {icon:"🛡️",label:"Business Insurance Broker",text:"Independent business insurance broker with tailored policies for SMEs covering liability, property, cyber, and employee benefits.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Home Insurance Comparison",text:"Home insurance comparison platform with instant quotes from 40+ providers, claims support, and annual renewal reminders.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🚗",label:"Motor Insurance",text:"Specialist motor insurance broker for classic cars, modified vehicles, young drivers, and multi-car fleet policies.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"❤️",label:"Life Insurance Advisor",text:"Independent life insurance advisor helping families protect their futures with term life, income protection, and critical illness cover.",style:"elegant",mood:"warm",tone:"friendly"},
        {icon:"🏥",label:"Health Insurance",text:"Private health insurance broker with corporate schemes, individual plans, dental and optical cover, and fast specialist referrals.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"✈️",label:"Travel Insurance",text:"Specialist travel insurance for backpackers, extreme sports, and medical conditions. Annual multi-trip and single-trip policies.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🐕",label:"Pet Insurance",text:"Comprehensive pet insurance with accident, illness, and wellness plans. No upper age limits and direct vet payment options.",style:"playful",mood:"warm",tone:"friendly"},
        {icon:"🏢",label:"Commercial Property Insurance",text:"Commercial property insurance for landlords, developers, and investors covering buildings, contents, rent guarantee, and terrorism.",style:"corporate",mood:"dark",tone:"formal"},
    ],
    recruiting: [
        {icon:"💼",label:"Executive Search Firm",text:"Retained executive search firm placing C-suite leaders, board members, and senior directors in FTSE 250 and private equity portfolio companies.",style:"elegant",mood:"dark",tone:"formal"},
        {icon:"💻",label:"Tech Recruiter",text:"Specialist tech recruitment agency placing developers, data scientists, and CTOs in startups and scale-ups. Remote-first roles.",style:"futuristic",mood:"cool",tone:"casual"},
        {icon:"🏥",label:"Healthcare Recruitment",text:"Healthcare recruitment agency supplying nurses, doctors, and allied health professionals to NHS trusts and private hospitals.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🏗️",label:"Construction Recruitment",text:"Construction and engineering recruitment agency with trades, project managers, and quantity surveyors for infrastructure projects.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"📊",label:"Finance Recruitment",text:"Finance and accounting recruitment specialist placing CFOs, auditors, financial analysts, and compliance officers in top firms.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🌍",label:"International Recruitment",text:"Global recruitment firm with multilingual consultants placing professionals across Europe, Middle East, and Asia-Pacific markets.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"🎓",label:"Graduate Recruitment",text:"Graduate recruitment platform connecting final-year students with graduate schemes, internships, and entry-level positions at top employers.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🏠",label:"Remote Job Board",text:"Remote-only job board with verified work-from-home positions in tech, marketing, design, and customer success worldwide.",style:"minimalist",mood:"light",tone:"casual"},
    ],

    // ═══════════════════════════════════════════════════════
    // MEDICAL & HEALTH
    // ═══════════════════════════════════════════════════════
    medical: [
        {icon:"🏥",label:"Modern Clinic",text:"Multi-specialty medical clinic with state-of-the-art diagnostics, telehealth services, and patient portal. Healthcare, redefined.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🧠",label:"Neurology Practice",text:"Specialist neurology practice treating migraines, epilepsy, and movement disorders with advanced imaging and personalized care plans.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"👁️",label:"Eye Clinic",text:"Specialist ophthalmology clinic with laser eye surgery, cataract treatment, pediatric vision care, and virtual consultation booking.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🤰",label:"Fertility Clinic",text:"Advanced fertility center offering IVF, egg freezing, genetic testing, and compassionate support through every step of the journey.",style:"elegant",mood:"pastel",tone:"friendly"},
        {icon:"🧬",label:"Functional Medicine",text:"Integrative functional medicine practice using advanced lab testing, personalized protocols, and root-cause analysis for chronic conditions.",style:"organic",mood:"warm",tone:"professional"},
        {icon:"💊",label:"Online Pharmacy",text:"Licensed online pharmacy with prescription delivery, medication reminders, pharmacist video consultations, and health product marketplace.",style:"geometric",mood:"light",tone:"professional"},
        {icon:"🩺",label:"Concierge Doctor",text:"Concierge primary care practice with same-day visits, 24/7 doctor access, comprehensive annual exams, and unlimited appointments.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🏥",label:"Urgent Care Center",text:"Walk-in urgent care center with X-ray, lab testing, stitches, and minor injury treatment. Open evenings and weekends.",style:"bold",mood:"light",tone:"friendly"},
    ],
    dental: [
        {icon:"🦷",label:"Cosmetic Dentistry",text:"Premium cosmetic dentistry practice offering veneers, Invisalign, whitening, and smile makeovers in a spa-like environment.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"😁",label:"Family Dentist",text:"Friendly family dental practice with gentle cleanings, children's dentistry, NHS and private options, and emergency same-day appointments.",style:"playful",mood:"light",tone:"friendly"},
        {icon:"🏥",label:"Dental Implant Center",text:"Specialist dental implant center with 3D scanning, same-day implants, full-arch restoration, and sedation options for nervous patients.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"✨",label:"Teeth Whitening Clinic",text:"Professional teeth whitening clinic with in-office laser treatments, custom take-home trays, and maintenance plans for lasting results.",style:"minimalist",mood:"pastel",tone:"friendly"},
        {icon:"👶",label:"Pediatric Dentist",text:"Child-friendly dental practice with play area, TV ceilings, reward charts, and specialist training in children's orthodontics and anxiety management.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🔧",label:"Orthodontic Practice",text:"Specialist orthodontic practice with metal braces, ceramic braces, Invisalign, and retainers. Free consultations and flexible payment plans.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"😴",label:"Sedation Dentistry",text:"Dental anxiety specialist offering IV sedation, nitrous oxide, and oral sedation for phobic patients needing extractions or implants.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🏆",label:"Award-Winning Practice",text:"Multi-award-winning dental practice with digital smile design, CEREC same-day crowns, and a team of specialist dentists under one roof.",style:"elegant",mood:"luxury",tone:"luxurious"},
    ],
    veterinary: [
        {icon:"🐕",label:"Full-Service Vet Clinic",text:"Full-service veterinary clinic with wellness exams, vaccinations, surgery, dental care, and in-house laboratory. Because pets are family.",style:"playful",mood:"light",tone:"friendly"},
        {icon:"🚨",label:"Emergency Vet Hospital",text:"24/7 emergency veterinary hospital with critical care unit, blood bank, advanced imaging, and overnight monitoring for urgent cases.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🐴",label:"Equine Veterinary",text:"Equine veterinary practice with ambulatory care, pre-purchase exams, lameness diagnostics, and reproductive services for sport horses.",style:"elegant",mood:"earth",tone:"professional"},
        {icon:"🐱",label:"Cat-Only Clinic",text:"Feline-exclusive veterinary clinic designed to minimize stress with separate waiting areas, pheromone diffusers, and cat-savvy staff.",style:"minimalist",mood:"pastel",tone:"friendly"},
        {icon:"🦜",label:"Exotic Animal Vet",text:"Specialist exotic animal veterinarian treating birds, reptiles, rabbits, and small mammals with species-specific expertise and equipment.",style:"organic",mood:"colorful",tone:"friendly"},
        {icon:"🐕‍🦺",label:"Rehabilitation Center",text:"Veterinary rehabilitation center with hydrotherapy pool, underwater treadmill, laser therapy, and acupuncture for post-surgery recovery.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"💊",label:"Mobile Vet Service",text:"Mobile veterinary service bringing wellness exams, vaccinations, and end-of-life care to your home. Less stress for pets and owners.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🔬",label:"Veterinary Specialist",text:"Referral veterinary specialist in oncology, cardiology, and orthopedic surgery with board-certified surgeons and advanced diagnostics.",style:"corporate",mood:"cool",tone:"professional"},
    ],
    therapy: [
        {icon:"🧠",label:"Anxiety & Depression",text:"Private therapy practice specializing in anxiety, depression, and burnout using CBT, EMDR, and mindfulness-based approaches.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"💑",label:"Couples Counseling",text:"Couples and relationship counseling using Gottman Method. Rebuilding trust, improving communication, and navigating life transitions together.",style:"elegant",mood:"pastel",tone:"friendly"},
        {icon:"👶",label:"Child Psychologist",text:"Child and adolescent psychologist specializing in ADHD, autism assessment, school anxiety, and behavioral challenges. Play therapy approach.",style:"playful",mood:"light",tone:"friendly"},
        {icon:"🎗️",label:"Trauma Specialist",text:"PTSD and trauma specialist using EMDR, somatic experiencing, and prolonged exposure therapy for veterans, first responders, and survivors.",style:"minimalist",mood:"warm",tone:"professional"},
        {icon:"🏥",label:"Online Therapy Platform",text:"Teletherapy platform matching clients with licensed therapists via video, phone, or chat. Flexible scheduling and insurance accepted.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"🧘",label:"Holistic Therapist",text:"Holistic psychotherapist integrating talk therapy with breathwork, somatic practices, and nature-based therapy for whole-person healing.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"💼",label:"Executive Coach",text:"Licensed psychologist offering executive coaching, leadership development, and workplace performance optimization for C-suite professionals.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🌈",label:"LGBTQ+ Affirming",text:"LGBTQ+ affirming therapy practice with specialists in gender identity, coming out support, minority stress, and chosen family dynamics.",style:"bold",mood:"colorful",tone:"friendly"},
    ],
    fitness: [
        {icon:"🏋️",label:"CrossFit Gym",text:"CrossFit box with Olympic lifting platforms, functional fitness classes, nutrition coaching, and competitive team training.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"💪",label:"Personal Training",text:"Elite personal training studio with body composition analysis, customized programs, and online coaching worldwide.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🥊",label:"Boxing Gym",text:"Gritty downtown boxing gym with ring training, heavy bag circuits, one-on-one coaching, and amateur fight night events.",style:"brutalist",mood:"dark",tone:"casual"},
        {icon:"🚴",label:"Cycling Studio",text:"High-energy indoor cycling studio with immersive LED screens, beat-synced rides, performance tracking, and community leaderboards.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"🧗",label:"Climbing Center",text:"Indoor bouldering and climbing center with auto-belay walls, training boards, youth programs, and a café with protein shakes.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🏃",label:"Running Club",text:"Community running club with coached track sessions, trail runs, marathon training plans, and annual destination running retreats.",style:"geometric",mood:"earth",tone:"friendly"},
        {icon:"🏊",label:"Swimming Academy",text:"Competitive swimming academy with stroke clinics, masters programs, triathlon training, and heated Olympic-sized indoor pool.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🤸",label:"Martial Arts Dojo",text:"Traditional martial arts dojo teaching karate, judo, and Brazilian jiu-jitsu. Kids programs, adult classes, and grading ceremonies.",style:"bold",mood:"dark",tone:"professional"},
    ],
    yoga: [
        {icon:"🧘",label:"Yoga Studio",text:"Peaceful yoga studio offering vinyasa, yin, and prenatal classes. Sound healing sessions and weekend retreats in nature.",style:"organic",mood:"pastel",tone:"friendly"},
        {icon:"🔥",label:"Hot Yoga Studio",text:"Bikram-inspired hot yoga studio with 40°C rooms, infrared heating, and classes for all levels. Detox, strengthen, and restore.",style:"bold",mood:"warm",tone:"casual"},
        {icon:"🌊",label:"Surf & Yoga Retreat",text:"Beachside yoga and surf retreat with morning flows, surf lessons, plant-based meals, and sunset meditation circles.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🏔️",label:"Mountain Yoga Retreat",text:"Luxury mountain yoga retreat center with forest bathing, silent meditation weekends, Ayurvedic meals, and visiting master teachers.",style:"minimalist",mood:"warm",tone:"luxurious"},
        {icon:"💻",label:"Online Yoga Platform",text:"On-demand yoga streaming platform with 2,000+ classes, personalized playlists, progress tracking, and live workshops with teachers.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"🤰",label:"Prenatal Yoga",text:"Specialist prenatal and postnatal yoga studio with pregnancy-safe flows, birth preparation, and mum-and-baby bonding classes.",style:"organic",mood:"pastel",tone:"friendly"},
        {icon:"💆",label:"Spa & Wellness Center",text:"Holistic wellness center combining yoga, massage, acupuncture, float tanks, and infrared saunas under one tranquil roof.",style:"minimalist",mood:"warm",tone:"luxurious"},
        {icon:"🎵",label:"Kirtan & Meditation",text:"Meditation center with daily sitting practice, kirtan chanting evenings, dharma talks, and silent retreat weekends.",style:"organic",mood:"warm",tone:"friendly"},
    ],

    // ═══════════════════════════════════════════════════════
    // EDUCATION
    // ═══════════════════════════════════════════════════════
    education: [
        {icon:"🎓",label:"Online Academy",text:"Online learning academy with expert-led video courses, certificates, and mentorship programs in tech, business, and creative fields.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🎵",label:"Music School",text:"Contemporary music school teaching guitar, piano, vocals, and production. From bedroom producer to stage performer.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🗣️",label:"Language School",text:"Immersive language school offering English, Spanish, French, and Mandarin through conversation-first methodology and cultural exchange.",style:"corporate",mood:"cool",tone:"friendly"},
        {icon:"👶",label:"Montessori School",text:"Montessori preschool and kindergarten with nature-based curriculum, low student-teacher ratios, and parent community events.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🎨",label:"Art School",text:"Contemporary art school with BFA programs in painting, sculpture, digital media, and curatorial studies. Annual graduate exhibition.",style:"editorial",mood:"monochrome",tone:"professional"},
        {icon:"🧪",label:"STEM Academy",text:"After-school STEM academy with robotics labs, coding bootcamps, science olympiad training, and university preparation workshops.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🎭",label:"Drama School",text:"Prestigious drama school with conservatory training in acting, directing, and stagecraft. Alumni on West End and Broadway stages.",style:"artdeco",mood:"dark",tone:"formal"},
        {icon:"📖",label:"Tutoring Center",text:"Personalized tutoring center with one-on-one sessions, exam prep for A-levels and GCSEs, and proven grade improvement guarantees.",style:"minimalist",mood:"light",tone:"friendly"},
    ],
    driving: [
        {icon:"🚗",label:"Driving School",text:"Friendly driving school with patient instructors, modern dual-control cars, and intensive crash courses for quick test passes.",style:"bold",mood:"light",tone:"friendly"},
        {icon:"🏍️",label:"Motorcycle Training",text:"CBT and full motorcycle license training with bikes provided, all-weather gear, and experienced IAM-qualified instructors.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"🚛",label:"HGV Training Center",text:"Professional HGV and LGV driver training with CPC courses, DVSA-approved testing center, and job placement assistance.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🏎️",label:"Advanced Driving",text:"Advanced driving courses with track days, skid pan training, IAM preparation, and defensive driving for corporate fleets.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🚌",label:"Bus & Coach License",text:"PCV bus and coach driver training with practical road training, theory preparation, and CPC qualification for public transport.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"⚡",label:"EV Driving Experience",text:"Electric vehicle driving experience with Tesla, Porsche Taycan, and BMW iX test drives, EV basics, and home charging guidance.",style:"futuristic",mood:"cool",tone:"casual"},
        {icon:"🏗️",label:"Forklift & Plant Training",text:"Forklift, excavator, and plant machinery training with CPCS and NPORS certification for construction site operatives.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🎓",label:"Instructor Training",text:"Approved driving instructor training program with part 1, 2, and 3 preparation, trainee license support, and business mentoring.",style:"minimalist",mood:"light",tone:"professional"},
    ],

    // ═══════════════════════════════════════════════════════
    // CONSTRUCTION & TRADES
    // ═══════════════════════════════════════════════════════
    construction: [
        {icon:"🏗️",label:"General Contractor",text:"Full-service construction company specializing in commercial build-outs, ground-up construction, and design-build projects.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🏠",label:"Home Builder",text:"Custom home builder creating bespoke residences from architectural plans. Timber frame, ICF, and traditional masonry construction.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🏗️",label:"Steel Fabrication",text:"Structural steel fabrication company with CNC cutting, welding, and erection services for commercial and infrastructure projects.",style:"brutalist",mood:"monochrome",tone:"professional"},
        {icon:"🔨",label:"Carpentry Workshop",text:"Bespoke carpentry workshop crafting custom kitchens, staircases, built-in wardrobes, and restoration joinery for period properties.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🧱",label:"Masonry Contractor",text:"Specialist masonry contractor with brickwork, stonework, restoration pointing, and heritage conservation for listed buildings.",style:"elegant",mood:"warm",tone:"professional"},
        {icon:"🏢",label:"Commercial Fit-Out",text:"Commercial fit-out contractor for offices, retail stores, restaurants, and healthcare facilities. Design, build, and project management.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Home Renovation",text:"Home renovation specialists transforming kitchens, bathrooms, and basements. Before/after portfolio, free consultations, and fixed-price quotes.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🪨",label:"Concrete Specialist",text:"Decorative and structural concrete contractor with polished floors, stamped patios, foundations, and commercial slab work.",style:"brutalist",mood:"dark",tone:"professional"},
    ],
    plumbing: [
        {icon:"🚰",label:"Emergency Plumber",text:"24/7 emergency plumbing service with burst pipe repair, blocked drains, boiler breakdowns, and 1-hour response time guarantee.",style:"bold",mood:"dark",tone:"friendly"},
        {icon:"🔥",label:"Heating Engineer",text:"Gas Safe registered heating engineer with boiler installations, servicing, power flushing, and smart thermostat upgrades.",style:"corporate",mood:"warm",tone:"professional"},
        {icon:"🚿",label:"Bathroom Fitter",text:"Complete bathroom fitting service with design consultation, tiling, plumbing, underfloor heating, and wet room installations.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"❄️",label:"HVAC Contractor",text:"Commercial HVAC contractor with air conditioning installation, maintenance, ductwork, and energy-efficient system upgrades for offices.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Central Heating Installer",text:"Central heating installation specialists with radiator upgrades, underfloor heating, and heat pump installations for eco-conscious homes.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🔧",label:"Drain Specialist",text:"Drain unblocking and CCTV survey specialist with jetting equipment, root cutting, relining, and drainage reports for homebuyers.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"♻️",label:"Water Treatment",text:"Water treatment company with softener installations, filtration systems, UV purification, and commercial water hygiene compliance testing.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🏗️",label:"Commercial Plumber",text:"Commercial plumbing contractor for new builds, schools, hospitals, and multi-unit developments with planned maintenance contracts.",style:"corporate",mood:"dark",tone:"professional"},
    ],
    electrical: [
        {icon:"⚡",label:"Residential Electrician",text:"NICEIC-approved residential electrician with rewiring, consumer unit upgrades, lighting design, and electrical safety certificates.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"☀️",label:"Solar Panel Installer",text:"MCS-certified solar panel installer with battery storage, EV chargers, and smart energy management for homes and businesses.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🏢",label:"Commercial Electrician",text:"Commercial electrical contractor for offices, retail, and industrial with three-phase installations, fire alarms, and emergency lighting.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🔌",label:"EV Charger Installer",text:"Specialist EV charger installer with OZEV grant processing, home and workplace installations, and load management solutions.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"💡",label:"Smart Home Electrician",text:"Smart home wiring specialist with automated lighting, multi-room audio, CCTV, alarm systems, and home network installation.",style:"futuristic",mood:"dark",tone:"casual"},
        {icon:"🔋",label:"Battery Storage",text:"Home battery storage installer with Tesla Powerwall, GivEnergy, and SolarEdge systems for energy independence and grid trading.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Electrical Testing",text:"Electrical inspection and testing company with EICR certificates, PAT testing, thermal imaging, and landlord compliance reports.",style:"minimalist",mood:"light",tone:"professional"},
        {icon:"🏗️",label:"Industrial Electrician",text:"Industrial electrical contractor with motor controls, PLC programming, panel building, and factory automation installations.",style:"bold",mood:"dark",tone:"professional"},
    ],
    landscaping: [
        {icon:"🌳",label:"Garden Design",text:"Award-winning garden designer creating bespoke outdoor spaces with planting plans, water features, and seasonal maintenance programs.",style:"organic",mood:"earth",tone:"professional"},
        {icon:"🪴",label:"Landscape Contractor",text:"Full-service landscape contractor with patios, driveways, retaining walls, fencing, and artificial grass installation.",style:"bold",mood:"earth",tone:"professional"},
        {icon:"🌺",label:"Planting Specialist",text:"Specialist planting designer creating borders, wildflower meadows, and sensory gardens with year-round color and pollinator habitats.",style:"organic",mood:"colorful",tone:"friendly"},
        {icon:"💧",label:"Irrigation Systems",text:"Irrigation system designer and installer with sprinklers, drip systems, smart controllers, and water-efficient landscape solutions.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🪵",label:"Tree Surgeon",text:"Certified tree surgeon with crown reductions, stump grinding, emergency storm clearance, and tree health surveys for developers.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🏡",label:"Garden Maintenance",text:"Reliable garden maintenance service with weekly mowing, hedge trimming, seasonal planting, and autumn leaf clearance. Residential and commercial.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏊",label:"Pool & Spa Builder",text:"Swimming pool and spa builder with infinity pools, natural swimming ponds, pool houses, and annual maintenance contracts.",style:"elegant",mood:"cool",tone:"luxurious"},
        {icon:"🔥",label:"Outdoor Living Design",text:"Outdoor living specialist with built-in BBQs, fire pits, pergolas, and outdoor kitchens creating year-round entertainment spaces.",style:"organic",mood:"warm",tone:"friendly"},
    ],
    cleaning: [
        {icon:"🧹",label:"House Cleaning Service",text:"Professional house cleaning service with regular weekly cleans, deep cleans, move-in/out cleans, and vetted, insured cleaners.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏢",label:"Commercial Cleaning",text:"Contract commercial cleaning for offices, retail, and medical facilities with evening teams, day porters, and quality audits.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🪟",label:"Window Cleaning",text:"Professional window cleaning for homes and commercial buildings with water-fed pole, rope access, and regular schedule contracts.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🧽",label:"Carpet & Upholstery",text:"Specialist carpet and upholstery cleaning with hot water extraction, stain protection, and mattress sanitization. Pet odor removal experts.",style:"bold",mood:"light",tone:"professional"},
        {icon:"🏗️",label:"Post-Construction Clean",text:"Post-construction and renovation cleaning with dust extraction, window polish, pressure washing, and new-build sparkle cleans.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🏠",label:"End of Tenancy",text:"End of tenancy cleaning service meeting letting agent standards with oven cleaning, deep bathroom scrub, and carpet shampooing included.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"♻️",label:"Eco Cleaning Company",text:"Eco-friendly cleaning company using plant-based products, refillable containers, and microfiber technology. Allergy and chemical-free homes.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🏭",label:"Industrial Cleaning",text:"Industrial cleaning contractor for factories, warehouses, and food production facilities with pressure washing, tank cleaning, and waste disposal.",style:"corporate",mood:"dark",tone:"professional"},
    ],
    roofing: [
        {icon:"🏠",label:"Residential Roofing",text:"Licensed residential roofer specializing in slate, tile, flat roof systems, and complete roof replacements with 20-year guarantee.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🏢",label:"Commercial Roofing",text:"Commercial roofing contractor with single-ply membranes, built-up systems, green roofs, and planned maintenance contracts for large buildings.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🔧",label:"Roof Repair Specialist",text:"Emergency roof repair service with storm damage, leak detection, missing tiles, and temporary weatherproofing. Same-day callout available.",style:"bold",mood:"dark",tone:"friendly"},
        {icon:"☀️",label:"Solar Roof Integration",text:"Solar roof tile installer with integrated photovoltaic systems that replace traditional tiles. Seamless aesthetic meets energy generation.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🧱",label:"Chimney Specialist",text:"Chimney repair and restoration specialist with repointing, flue lining, pot replacement, and lead flashing for period properties.",style:"elegant",mood:"warm",tone:"professional"},
        {icon:"💧",label:"Guttering & Fascia",text:"Guttering, fascia, and soffit replacement service with uPVC, aluminum, and cast iron options. Gutter cleaning and maintenance plans.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏗️",label:"Flat Roof Specialist",text:"Flat roof specialist with fibreglass GRP, EPDM rubber, and liquid-applied systems for extensions, garages, and commercial buildings.",style:"geometric",mood:"dark",tone:"professional"},
        {icon:"🌿",label:"Green Roof Installer",text:"Green roof and living wall installer creating biodiverse roof gardens, sedum blankets, and rooftop terraces for urban buildings.",style:"organic",mood:"earth",tone:"friendly"},
    ],
    moving: [
        {icon:"📦",label:"Residential Movers",text:"Professional residential moving company with packing service, furniture disassembly, storage, and full insurance for local and long-distance moves.",style:"bold",mood:"light",tone:"friendly"},
        {icon:"🏢",label:"Office Relocation",text:"Commercial office relocation specialists with IT disconnection, furniture reconfiguration, weekend moves, and minimal business disruption.",style:"corporate",mood:"cool",tone:"professional"},
        {icon:"🌍",label:"International Moving",text:"International moving company with sea freight, air freight, customs clearance, and door-to-door service to 150+ countries.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"📦",label:"Self-Storage Facility",text:"Climate-controlled self-storage facility with flexible units from 10 to 500 sqft, 24/7 access, CCTV, and free van hire.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏠",label:"Man & Van Service",text:"Affordable man and van service for single items, small moves, eBay collections, and tip runs with transparent hourly pricing.",style:"bold",mood:"warm",tone:"casual"},
        {icon:"🎹",label:"Piano Movers",text:"Specialist piano moving service with experienced handlers, custom padding, stair equipment, and climate-controlled vehicle for grands and uprights.",style:"elegant",mood:"warm",tone:"professional"},
        {icon:"🏗️",label:"Heavy Equipment Transport",text:"Heavy and specialist equipment transport with low-loader trailers, crane lifts, and ALLMI-certified machinery moving for construction sites.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"📋",label:"Packing Service",text:"Professional packing service with quality materials, fragile item wrapping, wardrobe boxes, and color-coded labeling for organized unpacking.",style:"minimalist",mood:"light",tone:"friendly"},
    ],

    // ═══════════════════════════════════════════════════════
    // EVENTS & ENTERTAINMENT
    // ═══════════════════════════════════════════════════════
    events: [
        {icon:"🎤",label:"Event Production",text:"Full-service event production company for corporate conferences, product launches, and gala dinners. AV, staging, and logistics.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🎪",label:"Festival Organizer",text:"Independent music and arts festival with three stages, camping, craft vendors, and immersive art installations.",style:"bold",mood:"colorful",tone:"casual"},
        {icon:"🎂",label:"Birthday Party Planner",text:"Kids' birthday party planning service with themed decorations, entertainer booking, custom cakes, and venue sourcing for ages 1-12.",style:"playful",mood:"colorful",tone:"friendly"},
        {icon:"🎙️",label:"Comedy Club",text:"Stand-up comedy club with open mic nights, headline acts, improv workshops, and dinner-and-show packages in an intimate venue.",style:"retro",mood:"warm",tone:"witty"},
        {icon:"🏆",label:"Sports Event Manager",text:"Professional sports event management for marathons, triathlons, cycling tours, and corporate team-building athletic challenges.",style:"bold",mood:"cool",tone:"professional"},
        {icon:"🎶",label:"DJ & Entertainment",text:"Professional DJ and entertainment agency providing wedding DJs, corporate event bands, silent disco hire, and AV production.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"🎭",label:"Theater Company",text:"Intimate black-box theater company producing bold contemporary plays, devised work, and community workshops.",style:"editorial",mood:"dark",tone:"professional"},
        {icon:"🎟️",label:"Ticketing Platform",text:"Event ticketing platform with seating maps, early-bird pricing, group discounts, and real-time sales analytics for organizers.",style:"geometric",mood:"cool",tone:"professional"},
    ],
    wedding: [
        {icon:"💒",label:"Luxury Wedding Planner",text:"Luxury wedding planner creating bespoke celebrations across Europe. From intimate elopements to grand château affairs with 200+ guests.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"📸",label:"Wedding Photography",text:"Fine art wedding photographer and videographer team capturing editorial moments with a romantic, film-inspired aesthetic.",style:"editorial",mood:"warm",tone:"luxurious"},
        {icon:"🎂",label:"Wedding Cake Designer",text:"Bespoke wedding cake designer creating multi-tier masterpieces with sugar flowers, hand-painted details, and tasting consultations.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"💐",label:"Wedding Florist",text:"Luxury wedding florist designing ceremony arches, bridal bouquets, table garlands, and hanging installations with seasonal blooms.",style:"organic",mood:"pastel",tone:"luxurious"},
        {icon:"🏰",label:"Wedding Venue",text:"Restored Georgian manor house wedding venue with licensed ceremony rooms, manicured gardens, bridal suite, and exclusive-use weekends.",style:"elegant",mood:"warm",tone:"luxurious"},
        {icon:"👗",label:"Bridal Boutique",text:"Curated bridal boutique with designer wedding dresses, bespoke alterations, accessories, and private appointment-only shopping experience.",style:"elegant",mood:"pastel",tone:"luxurious"},
        {icon:"🎵",label:"Wedding Band",text:"Live wedding band playing pop, soul, Motown, and jazz with ceremony strings, DJ add-on, and first dance arrangement service.",style:"retro",mood:"warm",tone:"friendly"},
        {icon:"✈️",label:"Destination Weddings",text:"Destination wedding planner specializing in Tuscan villas, Greek islands, and Caribbean resorts. Travel, legal, and vendor coordination.",style:"minimalist",mood:"warm",tone:"luxurious"},
    ],

    // ═══════════════════════════════════════════════════════
    // TRAVEL & LEISURE
    // ═══════════════════════════════════════════════════════
    travel: [
        {icon:"🌍",label:"Adventure Travel",text:"Adventure travel company offering guided treks in Nepal, safaris in Kenya, and diving expeditions in the Galápagos.",style:"bold",mood:"earth",tone:"casual"},
        {icon:"🏖️",label:"Luxury Travel Agency",text:"Bespoke luxury travel agency crafting personalized itineraries for honeymoons, anniversaries, and milestone celebrations.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🎿",label:"Ski Resort",text:"Alpine ski resort with 50+ runs, snow-sure slopes, ski school, and après-ski village with restaurants and thermal spa.",style:"futuristic",mood:"cool",tone:"friendly"},
        {icon:"⛺",label:"Glamping Site",text:"Luxury glamping site with safari tents, hot tubs, campfire dining, and stargazing experiences in the English countryside.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🚢",label:"Cruise Line",text:"Boutique expedition cruise line with Arctic voyages, marine biologist guides, and all-inclusive gourmet dining.",style:"elegant",mood:"cool",tone:"luxurious"},
        {icon:"🏍️",label:"Motorcycle Tours",text:"Guided motorcycle tour company with routes through Patagonia, the Alps, and Route 66. Bike rental and lodge stays.",style:"bold",mood:"earth",tone:"casual"},
        {icon:"🗺️",label:"Walking Holiday",text:"Self-guided walking holiday company with curated trails, luggage transfers, farm-stay accommodations, and local food experiences.",style:"organic",mood:"light",tone:"friendly"},
        {icon:"🎡",label:"City Tours",text:"Small-group city tour company with food walks, street art tours, ghost tours, and local insider experiences in 50+ cities.",style:"playful",mood:"colorful",tone:"witty"},
    ],

    // ═══════════════════════════════════════════════════════
    // REAL ESTATE
    // ═══════════════════════════════════════════════════════
    realestate: [
        {icon:"🏢",label:"Luxury Real Estate",text:"Premium real estate brokerage specializing in waterfront properties, penthouses, and architectural masterpieces above £2M.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🏘️",label:"Property Management",text:"Full-service property management company handling rentals, maintenance, tenant screening, and financial reporting for landlords.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"🏗️",label:"New Developments",text:"Property developer marketing luxury new-build apartments and townhouses. Virtual tours, floor plans, and reservation system.",style:"futuristic",mood:"dark",tone:"professional"},
        {icon:"🏡",label:"Local Estate Agent",text:"Trusted local estate agent helping families buy and sell homes for 25+ years. Community expertise, honest valuations.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🏖️",label:"Holiday Lettings",text:"Holiday lettings agency managing coastal cottages and countryside retreats with professional photography and dynamic pricing.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🏢",label:"Commercial Real Estate",text:"Commercial real estate brokerage specializing in office spaces, retail units, and investment-grade properties.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"🌆",label:"Urban Development",text:"Urban regeneration developer transforming disused industrial sites into vibrant mixed-use neighborhoods with affordable housing.",style:"geometric",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Interior Staging",text:"Home staging company transforming empty properties into aspirational living spaces that sell 73% faster at asking price.",style:"elegant",mood:"pastel",tone:"luxurious"},
    ],

    // ═══════════════════════════════════════════════════════
    // NONPROFIT & COMMUNITY
    // ═══════════════════════════════════════════════════════
    nonprofit: [
        {icon:"🌱",label:"Environmental Charity",text:"Environmental charity protecting endangered habitats, planting trees, and educating communities about climate action.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"📚",label:"Education Nonprofit",text:"Nonprofit providing free coding bootcamps and digital skills training to underserved youth in urban communities.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🐾",label:"Animal Rescue",text:"Animal rescue organization saving abandoned dogs and cats, running foster programs, and operating low-cost spay/neuter clinics.",style:"playful",mood:"warm",tone:"friendly"},
        {icon:"🍲",label:"Food Bank",text:"Community food bank fighting hunger with weekly distributions, mobile pantry trucks, nutrition education, and corporate donations.",style:"bold",mood:"warm",tone:"friendly"},
        {icon:"🎗️",label:"Cancer Charity",text:"Cancer research charity funding clinical trials, providing patient support groups, and running annual fundraising marathon events.",style:"minimalist",mood:"cool",tone:"professional"},
        {icon:"🏠",label:"Homeless Shelter",text:"Homeless shelter and transitional housing nonprofit with emergency beds, job training, and addiction recovery programs.",style:"corporate",mood:"light",tone:"professional"},
        {icon:"🌍",label:"Clean Water Foundation",text:"International foundation building wells and water purification systems in developing nations with transparent impact reporting.",style:"geometric",mood:"earth",tone:"friendly"},
        {icon:"👶",label:"Children's Charity",text:"Children's charity supporting foster care, adoption services, mentoring programs, and holiday gift drives for kids in need.",style:"playful",mood:"pastel",tone:"friendly"},
    ],
    church: [
        {icon:"⛪",label:"Community Church",text:"Welcoming community church with Sunday services, youth programs, food bank, and outreach to homeless neighbors. All are welcome.",style:"organic",mood:"warm",tone:"friendly"},
        {icon:"🎵",label:"Contemporary Church",text:"Contemporary worship church with live band, multimedia sermons, small groups, and community café open seven days a week.",style:"bold",mood:"colorful",tone:"friendly"},
        {icon:"🏛️",label:"Historic Cathedral",text:"Historic cathedral with daily services, choral evensong, guided tours, and classical music concert series in medieval architecture.",style:"elegant",mood:"warm",tone:"formal"},
        {icon:"🕌",label:"Islamic Center",text:"Community Islamic center with daily prayers, Quran classes, interfaith dialogue events, and charitable food distribution programs.",style:"geometric",mood:"earth",tone:"professional"},
        {icon:"🕍",label:"Synagogue",text:"Reform synagogue with Shabbat services, Hebrew school, bar/bat mitzvah preparation, and interfaith community engagement programs.",style:"elegant",mood:"warm",tone:"friendly"},
        {icon:"🙏",label:"Meditation Center",text:"Non-denominational meditation center with daily sitting practice, mindfulness courses, silent retreats, and community sangha gatherings.",style:"minimalist",mood:"warm",tone:"friendly"},
        {icon:"🏕️",label:"Church Camp",text:"Christian youth camp with summer programs, retreats, outdoor adventure activities, worship nights, and leadership development training.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"📻",label:"Church Media Ministry",text:"Church media ministry with live-streamed services, podcast sermons, devotional app, and online community groups for remote members.",style:"futuristic",mood:"cool",tone:"casual"},
    ],

    // ═══════════════════════════════════════════════════════
    // AUTOMOTIVE
    // ═══════════════════════════════════════════════════════
    automotive: [
        {icon:"🚗",label:"Car Dealership",text:"Premium used car dealership with inspected, certified vehicles. Finance options, warranty packages, and part-exchange service.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🔧",label:"Auto Repair Shop",text:"Family-owned auto repair shop specializing in European imports. Honest diagnostics, transparent pricing, and same-day service.",style:"corporate",mood:"dark",tone:"friendly"},
        {icon:"✨",label:"Detailing Studio",text:"Professional car detailing and ceramic coating studio. Paint correction, interior restoration, and paint protection film.",style:"minimalist",mood:"dark",tone:"professional"},
        {icon:"🏎️",label:"Motorsport Team",text:"Semi-professional racing team competing in GT championships. Sponsors, race calendar, driver profiles, and behind-the-scenes content.",style:"futuristic",mood:"neon",tone:"casual"},
        {icon:"⚡",label:"EV Dealership",text:"Electric vehicle dealership with test drives, charging station map, range calculators, and trade-in valuations for petrol cars.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🚛",label:"Fleet Management",text:"Commercial fleet management with GPS tracking, maintenance scheduling, fuel analytics, and driver safety compliance for logistics.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🚗",label:"Car Rental",text:"Premium car rental service with luxury and sports cars, airport delivery, long-term leases, and chauffeur-driven experiences.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🅿️",label:"Car Wash & Valeting",text:"Hand car wash and valeting center with express wash, full valet, alloy wheel refurbishment, and monthly membership plans.",style:"bold",mood:"light",tone:"casual"},
    ],
    motorcycle: [
        {icon:"🏍️",label:"Custom Motorcycle Shop",text:"Custom motorcycle shop building cafe racers, bobbers, and scrambler conversions with hand-fabricated parts and custom paint jobs.",style:"retro",mood:"dark",tone:"casual"},
        {icon:"🏁",label:"Motorcycle Dealership",text:"Authorized motorcycle dealership with new and used bikes, test rides, finance options, and factory-trained service department.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🧥",label:"Riding Gear Store",text:"Motorcycle gear store with helmets, leather jackets, boots, gloves, and armored textiles from premium brands. Expert fitting advice.",style:"bold",mood:"dark",tone:"casual"},
        {icon:"🔧",label:"Motorcycle Mechanic",text:"Independent motorcycle mechanic specializing in Japanese sportbikes, British classics, and Italian exotics. Honest service, fair prices.",style:"retro",mood:"warm",tone:"friendly"},
        {icon:"🏔️",label:"Adventure Motorcycle Tours",text:"Guided adventure motorcycle tours across Morocco, Iceland, and Patagonia with BMW GS bikes, support vehicle, and lodge stays.",style:"bold",mood:"earth",tone:"casual"},
        {icon:"⚡",label:"Electric Motorcycle",text:"Electric motorcycle brand with urban commuters, performance sport models, and adventure tourers. Test rides and home charging setup.",style:"futuristic",mood:"cool",tone:"professional"},
        {icon:"🛞",label:"Motorcycle Parts",text:"Online motorcycle parts and accessories store with OEM and aftermarket parts, exhausts, suspension upgrades, and track day essentials.",style:"geometric",mood:"dark",tone:"casual"},
        {icon:"🏍️",label:"Motorcycle Club",text:"Community motorcycle riding club with weekend rides, charity runs, bike nights, track day events, and annual rally weekends.",style:"bold",mood:"warm",tone:"casual"},
    ],
    taxi: [
        {icon:"🚕",label:"Taxi & Private Hire",text:"Licensed taxi and private hire company with pre-booked airport transfers, corporate accounts, and 24/7 dispatch with app booking.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🚐",label:"Airport Transfer Service",text:"Premium airport transfer service with meet-and-greet, flight tracking, executive vehicles, and fixed prices to all UK airports.",style:"elegant",mood:"dark",tone:"professional"},
        {icon:"🚌",label:"Coach Hire Company",text:"Coach and minibus hire for weddings, school trips, corporate events, and sports teams with professional drivers and WiFi onboard.",style:"corporate",mood:"light",tone:"friendly"},
        {icon:"🚛",label:"Courier & Delivery",text:"Same-day courier and delivery service for businesses with real-time tracking, proof of delivery, and warehouse distribution.",style:"bold",mood:"cool",tone:"professional"},
        {icon:"🏎️",label:"Executive Chauffeur",text:"Executive chauffeur service with Mercedes S-Class and Range Rover fleet. Corporate roadshows, VIP transfers, and event transport.",style:"elegant",mood:"luxury",tone:"luxurious"},
        {icon:"🚚",label:"Trucking & Haulage",text:"National haulage company with articulated lorries, curtain-siders, flatbeds, and temperature-controlled trailers for palletized freight.",style:"bold",mood:"dark",tone:"professional"},
        {icon:"🚲",label:"Bike Courier Service",text:"Eco-friendly bicycle courier service for city-center document and parcel delivery with 30-minute express option and cargo bikes.",style:"organic",mood:"light",tone:"casual"},
        {icon:"🚗",label:"Ride-Share Platform",text:"Community ride-sharing platform matching drivers and passengers for daily commutes, long-distance trips, and event transport.",style:"futuristic",mood:"cool",tone:"friendly"},
    ],

    // ═══════════════════════════════════════════════════════
    // GOVERNMENT & PUBLIC
    // ═══════════════════════════════════════════════════════
    government: [
        {icon:"🏛️",label:"City Council",text:"Municipal city council website with services directory, planning applications, local news, and community event calendar.",style:"corporate",mood:"light",tone:"formal"},
        {icon:"🚔",label:"Police Department",text:"Community police department with crime prevention resources, neighborhood watch programs, and online reporting system.",style:"corporate",mood:"dark",tone:"formal"},
        {icon:"📜",label:"Public Library",text:"Modern public library with digital catalog, event space bookings, children's reading programs, and maker space workshops.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🎖️",label:"Veterans Organization",text:"Veterans support organization providing housing assistance, career transition programs, mental health resources, and community events.",style:"corporate",mood:"dark",tone:"professional"},
        {icon:"🏫",label:"Public School District",text:"School district portal with enrollment, bus routes, lunch menus, parent-teacher conference scheduling, and student achievement data.",style:"minimalist",mood:"light",tone:"friendly"},
        {icon:"🌳",label:"Parks Department",text:"City parks and recreation department with trail maps, facility reservations, summer camp registration, and conservation programs.",style:"organic",mood:"earth",tone:"friendly"},
        {icon:"🚒",label:"Fire Department",text:"Municipal fire department with safety education, station tour bookings, recruitment information, and real-time incident dashboard.",style:"bold",mood:"dark",tone:"formal"},
        {icon:"🗳️",label:"Electoral Commission",text:"Regional electoral commission with voter registration, polling station finder, candidate information, and multilingual election guides.",style:"geometric",mood:"cool",tone:"formal"},
    ],
};

// ═══════════════════════════════════════════════════════
// INDUSTRY → PROMPT GROUP MAPPING
// Every industry maps to its most specific matching group
// ═══════════════════════════════════════════════════════
const PROMPT_GROUP_MAP = {
    // Food & Hospitality
    restaurant:"restaurant", cafe:"cafe", bar:"bar", bakery:"bakery",
    foodtruck:"foodtruck", catering:"foodtruck",
    hotel:"hotel", resort:"hotel", winery:"bar",
    // Tech & Digital
    saas:"saas", startup:"startup", ai:"startup", app:"startup", crypto:"startup",
    cybersecurity:"cybersecurity", devtools:"saas", hosting:"hosting",
    itsupport:"saas", gamedev:"startup",
    // Creative & Media
    portfolio:"portfolio", design:"portfolio", photography:"photography",
    videography:"videography", animation:"portfolio",
    agency:"agency", marketing:"agency", music:"portfolio", film:"videography",
    art:"portfolio", architecture:"architecture", interior:"interior",
    tattoo:"tattoo",
    // Content & Publishing
    blog:"blog", personal:"blog", magazine:"magazine", news:"magazine",
    podcast:"podcast", newsletter:"podcast", author:"blog", influencer:"podcast",
    wiki:"blog",
    // Commerce & Retail
    ecommerce:"ecommerce", fashion:"fashion",
    jewelry:"jewelry", beauty:"beauty", furniture:"furniture",
    electronics:"electronics", bookshop:"bookshop", grocery:"grocery",
    florist:"florist", pets:"pets_shop", marketplace:"ecommerce",
    // Professional Services
    law:"law", finance:"finance", consulting:"finance",
    accounting:"accounting", insurance:"insurance",
    recruiting:"recruiting", translation:"recruiting",
    // Real Estate
    realestate:"realestate", propertymanagement:"realestate",
    // Medical & Health
    medical:"medical", dental:"dental", veterinary:"veterinary",
    pharmacy:"medical", therapy:"therapy", mentalhealth:"therapy",
    // Fitness & Wellness
    fitness:"fitness", yoga:"yoga", nutrition:"fitness",
    physiotherapy:"fitness", spa:"yoga", sports:"fitness",
    // Education
    education:"education", onlinecourse:"education", coaching:"education",
    tutoring:"education", language:"education", driving:"driving",
    childcare:"education", library:"education", training:"education",
    // Construction & Trades
    construction:"construction", plumbing:"plumbing", electrical:"electrical",
    hvac:"plumbing", roofing:"roofing", painting:"construction",
    landscaping:"landscaping", cleaning:"cleaning",
    moving:"moving", handyman:"construction", solar:"electrical",
    // Automotive & Transport
    automotive:"automotive", mechanic:"automotive", carwash:"automotive",
    taxi:"taxi", trucking:"taxi", motorcycle:"motorcycle", boating:"motorcycle",
    // Events & Entertainment
    events:"events", wedding:"wedding", party:"events", venue:"wedding",
    theater:"events", cinema:"events", escape:"events", festival:"events",
    // Travel & Leisure
    travel:"travel", tourism:"travel", camping:"travel", skiing:"travel",
    diving:"travel", golf:"travel", marina:"travel",
    // Nonprofit & Community
    nonprofit:"nonprofit", church:"church", volunteer:"nonprofit",
    community:"nonprofit", association:"nonprofit", memorial:"nonprofit",
    // Government & Public
    political:"government", government:"government", police:"government",
    military:"government", embassy:"government",
    // Special
    resume:"portfolio", directory:"saas", landing:"saas",
    comingsoon:"startup", "saas-landing":"saas", other:"portfolio",
};


function renderPromptSuggestions(industry) {
    const container = document.getElementById("promptSuggestions");
    const group = PROMPT_GROUP_MAP[industry];
    const suggestions = group ? PROMPT_SUGGESTIONS[group] : null;
    if (!suggestions) { container.classList.remove("visible"); return; }
    container.innerHTML = "";
    suggestions.forEach(s => {
        const card = document.createElement("div");
        card.className = "prompt-suggestion";
        card.innerHTML = `<span class="ps-icon">${s.icon}</span><div class="ps-body"><div class="ps-label">${s.label}</div><div class="ps-text">${s.text}</div><div class="ps-tags"><span class="ps-tag">${s.style}</span><span class="ps-tag">${s.mood}</span><span class="ps-tag">${s.tone}</span></div></div>`;
        card.onclick = () => {
            document.getElementById("wPrompt").value = s.text;
            selectChip("wStyle", s.style);
            selectChip("wMood", s.mood);
            document.getElementById("wTone").value = s.tone;
            container.querySelectorAll(".prompt-suggestion").forEach(c => c.style.borderColor = "");
            card.style.borderColor = "var(--ctp-blue)";
            document.getElementById("wPrompt").focus();
        };
        container.appendChild(card);
    });
    container.classList.add("visible");
}

// Industry → default style/mood/tone mapping (all 129 dropdown values)
const INDUSTRY_DEFAULTS = {
    // Food & Hospitality
    restaurant:{style:"elegant",mood:"warm",tone:"luxurious"}, cafe:{style:"organic",mood:"warm",tone:"friendly"},
    bar:{style:"bold",mood:"dark",tone:"casual"}, bakery:{style:"organic",mood:"warm",tone:"friendly"},
    foodtruck:{style:"bold",mood:"colorful",tone:"casual"}, catering:{style:"elegant",mood:"light",tone:"professional"},
    hotel:{style:"elegant",mood:"warm",tone:"luxurious"}, resort:{style:"elegant",mood:"luxury",tone:"luxurious"},
    winery:{style:"elegant",mood:"warm",tone:"luxurious"},
    // Tech & Digital
    saas:{style:"minimalist",mood:"cool",tone:"professional"}, startup:{style:"bold",mood:"cool",tone:"witty"},
    ai:{style:"futuristic",mood:"dark",tone:"professional"}, app:{style:"minimalist",mood:"cool",tone:"friendly"},
    crypto:{style:"futuristic",mood:"dark",tone:"professional"}, cybersecurity:{style:"corporate",mood:"dark",tone:"professional"},
    devtools:{style:"minimalist",mood:"dark",tone:"professional"}, hosting:{style:"corporate",mood:"cool",tone:"professional"},
    itsupport:{style:"corporate",mood:"light",tone:"professional"}, gamedev:{style:"bold",mood:"neon",tone:"casual"},
    // Creative & Media
    portfolio:{style:"minimalist",mood:"dark",tone:"professional"}, photography:{style:"minimalist",mood:"dark",tone:"professional"},
    videography:{style:"bold",mood:"dark",tone:"professional"}, agency:{style:"bold",mood:"dark",tone:"witty"},
    design:{style:"minimalist",mood:"cool",tone:"professional"}, music:{style:"bold",mood:"dark",tone:"casual"},
    art:{style:"editorial",mood:"dark",tone:"professional"}, architecture:{style:"minimalist",mood:"light",tone:"professional"},
    interior:{style:"elegant",mood:"light",tone:"luxurious"}, animation:{style:"playful",mood:"colorful",tone:"casual"},
    tattoo:{style:"brutalist",mood:"dark",tone:"casual"},
    // Content & Publishing
    blog:{style:"editorial",mood:"light",tone:"friendly"}, magazine:{style:"editorial",mood:"light",tone:"professional"},
    podcast:{style:"bold",mood:"dark",tone:"casual"}, news:{style:"corporate",mood:"light",tone:"professional"},
    newsletter:{style:"minimalist",mood:"light",tone:"friendly"}, author:{style:"editorial",mood:"warm",tone:"professional"},
    influencer:{style:"playful",mood:"colorful",tone:"casual"},
    // Commerce & Retail
    ecommerce:{style:"minimalist",mood:"light",tone:"friendly"}, fashion:{style:"elegant",mood:"dark",tone:"luxurious"},
    jewelry:{style:"elegant",mood:"luxury",tone:"luxurious"}, beauty:{style:"elegant",mood:"pastel",tone:"friendly"},
    furniture:{style:"minimalist",mood:"light",tone:"professional"}, electronics:{style:"minimalist",mood:"cool",tone:"professional"},
    bookshop:{style:"organic",mood:"warm",tone:"friendly"}, grocery:{style:"organic",mood:"light",tone:"friendly"},
    pets:{style:"playful",mood:"colorful",tone:"friendly"}, florist:{style:"organic",mood:"warm",tone:"friendly"},
    marketplace:{style:"minimalist",mood:"light",tone:"professional"},
    // Professional Services
    law:{style:"corporate",mood:"dark",tone:"formal"}, finance:{style:"corporate",mood:"cool",tone:"professional"},
    insurance:{style:"corporate",mood:"light",tone:"professional"}, consulting:{style:"corporate",mood:"cool",tone:"professional"},
    marketing:{style:"bold",mood:"cool",tone:"witty"}, recruiting:{style:"corporate",mood:"light",tone:"professional"},
    translation:{style:"minimalist",mood:"cool",tone:"professional"}, coaching:{style:"organic",mood:"warm",tone:"friendly"},
    realestate:{style:"elegant",mood:"light",tone:"professional"}, propertymanagement:{style:"corporate",mood:"light",tone:"professional"},
    // Health & Wellness
    medical:{style:"minimalist",mood:"light",tone:"professional"}, dental:{style:"minimalist",mood:"light",tone:"friendly"},
    veterinary:{style:"playful",mood:"warm",tone:"friendly"}, pharmacy:{style:"minimalist",mood:"light",tone:"professional"},
    therapy:{style:"organic",mood:"warm",tone:"friendly"}, spa:{style:"elegant",mood:"warm",tone:"luxurious"},
    fitness:{style:"bold",mood:"dark",tone:"casual"}, yoga:{style:"organic",mood:"warm",tone:"friendly"},
    nutrition:{style:"organic",mood:"light",tone:"friendly"}, physiotherapy:{style:"minimalist",mood:"light",tone:"professional"},
    mentalhealth:{style:"organic",mood:"warm",tone:"friendly"},
    // Education & Training
    education:{style:"playful",mood:"colorful",tone:"friendly"}, onlinecourse:{style:"playful",mood:"colorful",tone:"friendly"},
    tutoring:{style:"minimalist",mood:"light",tone:"friendly"}, language:{style:"playful",mood:"colorful",tone:"friendly"},
    driving:{style:"corporate",mood:"light",tone:"professional"}, childcare:{style:"playful",mood:"colorful",tone:"friendly"},
    library:{style:"editorial",mood:"warm",tone:"professional"}, training:{style:"corporate",mood:"cool",tone:"professional"},
    // Construction & Trade
    construction:{style:"corporate",mood:"light",tone:"professional"}, plumbing:{style:"corporate",mood:"light",tone:"professional"},
    electrical:{style:"corporate",mood:"light",tone:"professional"}, hvac:{style:"corporate",mood:"light",tone:"professional"},
    roofing:{style:"corporate",mood:"light",tone:"professional"}, painting:{style:"minimalist",mood:"light",tone:"friendly"},
    landscaping:{style:"organic",mood:"earth",tone:"friendly"}, cleaning:{style:"minimalist",mood:"light",tone:"friendly"},
    moving:{style:"bold",mood:"light",tone:"friendly"}, handyman:{style:"corporate",mood:"light",tone:"friendly"},
    solar:{style:"organic",mood:"earth",tone:"professional"},
    // Automotive & Transport
    automotive:{style:"bold",mood:"dark",tone:"professional"}, mechanic:{style:"corporate",mood:"dark",tone:"professional"},
    carwash:{style:"bold",mood:"cool",tone:"casual"}, taxi:{style:"corporate",mood:"dark",tone:"professional"},
    trucking:{style:"bold",mood:"dark",tone:"professional"}, motorcycle:{style:"bold",mood:"dark",tone:"casual"},
    boating:{style:"elegant",mood:"cool",tone:"professional"},
    // Events & Entertainment
    events:{style:"elegant",mood:"dark",tone:"professional"}, wedding:{style:"elegant",mood:"warm",tone:"luxurious"},
    party:{style:"playful",mood:"colorful",tone:"casual"}, venue:{style:"elegant",mood:"dark",tone:"professional"},
    theater:{style:"editorial",mood:"dark",tone:"professional"}, festival:{style:"bold",mood:"colorful",tone:"casual"},
    cinema:{style:"bold",mood:"dark",tone:"casual"}, escape:{style:"bold",mood:"dark",tone:"casual"},
    // Travel & Leisure
    travel:{style:"bold",mood:"colorful",tone:"friendly"}, tourism:{style:"bold",mood:"warm",tone:"friendly"},
    camping:{style:"organic",mood:"earth",tone:"casual"}, skiing:{style:"bold",mood:"cool",tone:"casual"},
    diving:{style:"bold",mood:"cool",tone:"casual"}, golf:{style:"elegant",mood:"light",tone:"professional"},
    sports:{style:"bold",mood:"dark",tone:"casual"}, marina:{style:"elegant",mood:"cool",tone:"professional"},
    // Community & Non-Profit
    nonprofit:{style:"organic",mood:"warm",tone:"friendly"}, church:{style:"organic",mood:"warm",tone:"friendly"},
    political:{style:"corporate",mood:"cool",tone:"formal"}, community:{style:"organic",mood:"warm",tone:"friendly"},
    association:{style:"corporate",mood:"light",tone:"professional"}, volunteer:{style:"organic",mood:"warm",tone:"friendly"},
    // Government & Public
    government:{style:"corporate",mood:"light",tone:"formal"}, police:{style:"corporate",mood:"dark",tone:"formal"},
    military:{style:"corporate",mood:"dark",tone:"formal"}, embassy:{style:"corporate",mood:"light",tone:"formal"},
    // Other
    personal:{style:"minimalist",mood:"light",tone:"friendly"}, resume:{style:"minimalist",mood:"light",tone:"professional"},
    wiki:{style:"minimalist",mood:"light",tone:"professional"}, directory:{style:"corporate",mood:"light",tone:"professional"},
    "saas-landing":{style:"bold",mood:"cool",tone:"professional"}, comingsoon:{style:"minimalist",mood:"dark",tone:"witty"},
    memorial:{style:"elegant",mood:"warm",tone:"formal"}, other:{style:"minimalist",mood:"light",tone:"professional"},
};

// Update style/mood/tone + suggestions when industry changes
document.getElementById("wIndustry").addEventListener("change", function() {
    const val = this.value;
    renderPromptSuggestions(val);
    const defaults = INDUSTRY_DEFAULTS[val];
    if (defaults) {
        if (defaults.style) selectChip("wStyle", defaults.style);
        if (defaults.mood) selectChip("wMood", defaults.mood);
        if (defaults.tone) document.getElementById("wTone").value = defaults.tone;
    }
});

// Surprise Me
// ═══ Design Inspiration URL Analyzer ═══
document.getElementById("btnAnalyzeUrl").onclick = async function() {
    const url = document.getElementById("wInspirationUrl").value.trim();
    if (!url) { toast("Paste a website URL first", "error"); return; }
    if (!url.startsWith("http")) { toast("URL must start with http:// or https://", "error"); return; }

    const btn = this;
    const resultDiv = document.getElementById("inspirationResult");
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    resultDiv.classList.remove("hidden");
    resultDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing design...';

    try {
        const {provider, model} = getModelParts();
        const result = await api("/api/ai-theme-builder/wizard/analyze-inspiration", {
            url, provider, model
        });

        if (result.ok && result.analysis) {
            const a = result.analysis;
            let html = '<div style="font-weight:600;margin-bottom:6px;color:var(--ctp-green)"><i class="fas fa-check-circle"></i> Design analyzed!</div>';

            if (a.colors) {
                html += '<div style="margin-bottom:6px"><span style="opacity:.6">Colors:</span> ';
                (a.colors || []).forEach(c => {
                    html += '<span style="display:inline-block;width:20px;height:20px;border-radius:4px;background:' + c + ';vertical-align:middle;margin:0 2px;border:1px solid rgba(255,255,255,.1)" title="' + c + '"></span>';
                });
                html += '</div>';
            }
            if (a.style) html += '<div style="margin-bottom:4px"><span style="opacity:.6">Style:</span> ' + a.style + '</div>';
            if (a.mood) html += '<div style="margin-bottom:4px"><span style="opacity:.6">Mood:</span> ' + a.mood + '</div>';
            if (a.fonts) html += '<div style="margin-bottom:4px"><span style="opacity:.6">Fonts:</span> ' + a.fonts + '</div>';

            html += '<button class="btn btn-secondary" id="btnApplyInspiration" style="margin-top:8px;font-size:11px;padding:4px 10px"><i class="fas fa-paint-brush"></i> Apply to brief</button>';
            resultDiv.innerHTML = html;

            // Store analysis for later use
            state.inspirationAnalysis = a;

            // Apply button
            document.getElementById("btnApplyInspiration").onclick = function() {
                if (a.colors && a.colors.length >= 2) {
                    // Apply colors to brief when generated
                    state.inspirationColors = a.colors;
                }
                if (a.style) {
                    const styleSelect = document.getElementById("wStyle") || document.querySelector('[data-chip-group="wStyle"]');
                    // Set style chip if matching
                    document.querySelectorAll('.chip-group[data-group="wStyle"] .chip').forEach(c => {
                        if (c.dataset.value && a.style.toLowerCase().includes(c.dataset.value.toLowerCase())) c.click();
                    });
                }
                if (a.mood) {
                    document.querySelectorAll('.chip-group[data-group="wMood"] .chip').forEach(c => {
                        if (c.dataset.value && a.mood.toLowerCase().includes(c.dataset.value.toLowerCase())) c.click();
                    });
                }
                toast("Design inspiration applied!", "success");
                this.innerHTML = '<i class="fas fa-check"></i> Applied';
                this.disabled = true;
            };
        } else {
            resultDiv.innerHTML = '<span style="color:var(--ctp-red)"><i class="fas fa-exclamation-triangle"></i> ' + (result.error || 'Could not analyze URL') + '</span>';
        }
    } catch(e) {
        resultDiv.innerHTML = '<span style="color:var(--ctp-red)"><i class="fas fa-exclamation-triangle"></i> ' + e.message + '</span>';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-search"></i> Analyze';
};

document.getElementById("btnSurpriseMe").onclick = function() {
    const allGroups = Object.keys(PROMPT_SUGGESTIONS);
    const randomGroup = allGroups[Math.floor(Math.random() * allGroups.length)];
    const suggestions = PROMPT_SUGGESTIONS[randomGroup];
    const randomPrompt = suggestions[Math.floor(Math.random() * suggestions.length)];

    // Find an industry that maps to this group
    const matchingIndustries = Object.entries(PROMPT_GROUP_MAP).filter(([k,v]) => v === randomGroup).map(([k]) => k);
    // Prefer the group name itself if it's a valid industry
    const industry = matchingIndustries.includes(randomGroup) ? randomGroup : matchingIndustries[0];

    // Fill everything
    document.getElementById("wPrompt").value = randomPrompt.text;
    document.getElementById("wIndustry").value = industry;
    selectChip("wStyle", randomPrompt.style);
    selectChip("wMood", randomPrompt.mood);
    document.getElementById("wTone").value = randomPrompt.tone;
    renderPromptSuggestions(industry);

    // Visual feedback
    toast("🎲 " + randomPrompt.label + " — " + randomPrompt.style + " / " + randomPrompt.mood, "info");

    // Scroll to prompt
    document.getElementById("wPrompt").focus();
};

// ═══════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════
function toast(msg, type = "info") {
    const el = document.getElementById("toast");
    el.textContent = msg;
    el.className = "toast " + type + " show";
    setTimeout(() => el.classList.remove("show"), 3000);
}


// ─── AI Error Notification ────────────────────────────
const errorTypeIcons = {
    no_credits: "💳", rate_limit: "⏱️", auth_error: "🔑",
    not_configured: "⚙️", model_error: "🤖", content_blocked: "🚫",
    timeout: "⏰", server_error: "🔥", network_error: "🌐", unknown: "⚠️"
};

function showAIError(errorInfo, rawError) {
    document.querySelectorAll('.ai-error-overlay, .ai-error-panel').forEach(el => el.remove());
    if (!errorInfo && rawError) {
        errorInfo = { type: 'unknown', title: 'AI Error', message: rawError, action: 'Try again or switch to a different AI provider.' };
    }
    if (!errorInfo) return;
    const icon = errorTypeIcons[errorInfo.type] || "⚠️";

    const overlay = document.createElement('div');
    overlay.className = 'ai-error-overlay';
    overlay.onclick = () => dismissAIError();

    const panel = document.createElement('div');
    panel.className = 'ai-error-panel';
    panel.innerHTML = `
        <div class="error-icon">${icon}</div>
        <div class="error-type-badge">${(errorInfo.type || 'error').replace(/_/g, ' ').toUpperCase()}</div>
        <div class="error-title">${errorInfo.title || 'AI Error'}</div>
        <div class="error-message">${errorInfo.message || 'An error occurred with the AI provider.'}</div>
        <div class="error-action">${errorInfo.action || 'Try again or switch provider.'}</div>
        ${errorInfo.link ? '<div class="error-link"><a href="' + errorInfo.link + '" target="_blank">Open provider billing page ↗</a></div>' : ''}
        <div class="error-buttons">
            <button class="btn-retry" onclick="dismissAIError(); document.getElementById('btnGenerateBrief')?.click();">🔄 Retry</button>
            <button class="btn-switch" onclick="switchToNextProvider()">🔀 Switch Provider</button>
            <button class="btn-dismiss" onclick="dismissAIError()">Dismiss</button>
        </div>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(panel);
}

function dismissAIError() {
    document.querySelectorAll('.ai-error-overlay, .ai-error-panel').forEach(el => el.remove());
}

function switchToNextProvider() {
    dismissAIError();
    const sel = document.getElementById("wModel");
    if (!sel || sel.options.length < 2) { toast("No other providers available", "error"); return; }
    const currentProvider = (sel.value || "").split(":")[0];
    for (const opt of sel.options) {
        const optProvider = (opt.value || "").split(":")[0];
        if (optProvider && optProvider !== currentProvider) {
            sel.value = opt.value;
            sel.dispatchEvent(new Event("change"));
            toast("Switched to " + opt.textContent.trim(), "info");
            return;
        }
    }
    toast("No alternative providers found", "error");
}

// ─── Provider Health Check ───────────────────────────
const providerNames = {openai: "OpenAI", anthropic: "Anthropic", deepseek: "DeepSeek", google: "Google"};

async function checkProviderHealth() {
    const container = document.getElementById("providerHealth");
    if (!container) return;
    container.style.display = "";
    container.innerHTML = '<div class="provider-health-title">AI Provider Status <span class="refresh-btn" onclick="checkProviderHealth()">🔄 refresh</span></div>' +
        Object.keys(providerNames).map(p =>
            '<div class="ph-item" data-status="checking" data-provider="' + p + '">' +
            '<span class="ph-icon">⏳</span>' +
            '<span class="ph-name">' + providerNames[p] + '</span>' +
            '<span class="ph-msg">Checking...</span></div>'
        ).join("");

    try {
        const r = await fetch("/api/ai-theme-builder/check-providers", {credentials:"same-origin"});
        const data = await r.json();
        if (!data.ok) { toast("Could not check providers", "error"); return; }

        const providers = data.providers;
        let html = '<div class="provider-health-title">AI Provider Status <span class="refresh-btn" onclick="checkProviderHealth()">🔄 refresh</span></div>';

        for (const [key, info] of Object.entries(providers)) {
            const name = providerNames[key] || key;
            const status = info.status || "unknown";
            const icon = info.icon || "❓";
            const msg = info.message || "";
            const balance = info.balance !== undefined ? "$" + parseFloat(info.balance).toFixed(2) : "";
            const link = info.link ? '<a class="ph-link" href="' + info.link + '" target="_blank">fix ↗</a>' : '';

            html += '<div class="ph-item" data-status="' + status + '" data-provider="' + key + '">' +
                '<span class="ph-icon">' + icon + '</span>' +
                '<span class="ph-name">' + name + '</span>' +
                (balance ? '<span class="ph-balance">' + balance + '</span>' : '') +
                '<span class="ph-msg">' + msg + '</span>' +
                link + '</div>';
        }

        container.innerHTML = html;

        // Show warning if selected provider has issues (do NOT auto-switch)
        const modelSelect = document.getElementById("wModel");
        if (modelSelect) {
            const currentProvider = (modelSelect.value || "").split(":")[0];
            if (currentProvider && providers[currentProvider]) {
                const st = providers[currentProvider].status;
                const msg = providers[currentProvider].message || "";
                if (st === "no_credits") {
                    toast("⚠️ " + (providerNames[currentProvider] || currentProvider) + ": " + msg + ". You may want to switch providers or top up.", "error");
                } else if (st === "auth_error" || st === "not_configured") {
                    toast("⚠️ " + (providerNames[currentProvider] || currentProvider) + ": " + msg, "error");
                } else if (st === "low_balance") {
                    toast("⚠️ " + (providerNames[currentProvider] || currentProvider) + ": " + msg, "info");
                }
            }
        }
    } catch(e) {
        console.error("Provider health check failed:", e);
    }
}

// Auto-check providers on page load
setTimeout(checkProviderHealth, 500);


async function api(url, data) {
    const r = await fetch(url, {
        method: "POST",
        headers: {"Content-Type":"application/json","X-CSRF-TOKEN":CSRF},
        body: JSON.stringify(data)
    });
    if (!r.ok) {
        let errMsg = "HTTP " + r.status;
        try {
                const errBody = await r.json();
                errMsg = errBody.error || errMsg;
                if (errBody.error_info) throw Object.assign(new Error(errMsg), { errorInfo: errBody.error_info });
            } catch(e) {
                if (e.errorInfo) throw e;
                try { const errText = await r.text(); if (errText.length < 200) errMsg += ": " + errText; } catch(e2) {}
        }
        throw new Error(errMsg);
    }
    return r.json();
}

function getChipValue(id) {
    const el = document.getElementById(id);
    if (!el) return "";
    // Handle <select> elements
    if (el.tagName === "SELECT") return el.value || "";
    // Handle chip groups
    const sel = el.querySelector(".chip.selected");
    return sel ? sel.dataset.v : "";
}

function selectChip(containerId, value) {
    const c = document.getElementById(containerId);
    if (!c) return;
    // Handle <select> elements
    if (c.tagName === "SELECT") { c.value = value; return; }
    // Handle chip groups
    c.querySelectorAll(".chip").forEach(ch => {
        ch.classList.toggle("selected", ch.dataset.v === value);
    });
}

function getModelParts() {
    const val = document.getElementById("wModel").value || ":";
    const [provider, ...rest] = val.split(":");
    return { provider, model: rest.join(":") };
}

// Design step model — used for Build (Step 4) and sub-page generation
// Falls back to Step 1 model if Design step selector is empty
function getDesignModelParts() {
    const designSel = document.getElementById("designModel");
    const val = (designSel && designSel.value) ? designSel.value : (document.getElementById("wModel").value || ":");
    const [provider, ...rest] = val.split(":");
    return { provider, model: rest.join(":") };
}

// Design step creativity — falls back to Step 1
function getDesignCreativity() {
    const el = document.getElementById("designCreativity");
    return (el && el.value) ? el.value : (document.getElementById("wCreativity").value || "medium");
}

// Model tier warning
function updateModelWarning() {
    const sel = document.getElementById("wModel");
    const opt = sel.options[sel.selectedIndex];
    const tier = opt ? opt.dataset.tier : "";
    const warn = document.getElementById("modelWarning");
    if (tier === "budget") { warn.classList.remove("hidden"); } else { warn.classList.add("hidden"); }
}
document.getElementById("wModel").addEventListener("change", updateModelWarning);
updateModelWarning(); // initial check

// ═══════════════════════════════════════════
// CHIP SELECTION (single-select per group)
// ═══════════════════════════════════════════
document.querySelectorAll(".chips").forEach(container => {
    container.addEventListener("click", e => {
        const chip = e.target.closest(".chip");
        if (!chip || chip.classList.contains("chip-group-label")) return;
        container.querySelectorAll(".chip").forEach(c => c.classList.remove("selected"));
        chip.classList.add("selected");
    });
});

// ═══════════════════════════════════════════
// INDUSTRY KITS
// ═══════════════════════════════════════════
(function initIndustryKits() {
    const container = document.getElementById("industryKits");
    if (!container) return;
    const kitIcons = {restaurant:"🍽️",cafe:"☕",saas:"💻",law:"⚖️",medical:"🏥",dental:"🦷",fitness:"💪",realestate:"🏠",photography:"📷",agency:"🎨",wedding:"💒",construction:"🏗️",ecommerce:"🛒",education:"📚",nonprofit:"💚"};
    Object.entries(INDUSTRY_KITS).forEach(([key, kit]) => {
        const btn = document.createElement("button");
        btn.className = "chip";
        btn.innerHTML = (kitIcons[key] || "📋") + " " + key.charAt(0).toUpperCase() + key.slice(1);
        btn.onclick = () => {
            // Fill all fields from kit
            document.getElementById("wPrompt").value = kit.prompt;
            selectChip("wIndustry", kit.industry);
            selectChip("wStyle", kit.style);
            selectChip("wMood", kit.mood);
            if (kit.tone) document.getElementById("wTone").value = kit.tone;
            // Auto-select pages
            state.pages = kit.pages || [];
            // Auto-select sections
            state.homeSections = kit.sections || [];
            // Highlight selected kit
            container.querySelectorAll(".chip").forEach(c => c.classList.remove("selected"));
            btn.classList.add("selected");
            toast("Kit applied! Click Generate Brief to continue.", "info");
        };
        container.appendChild(btn);
    });
})();

// ═══════════════════════════════════════════
// PRESETS GRID
// ═══════════════════════════════════════════
(function initPresets() {
    const grid = document.getElementById("presetsGrid");
    PRESETS.forEach(p => {
        const colors = p.colors || [];
        const div = document.createElement("div");
        div.className = "preset-card";
        div.innerHTML = `
            <div class="preset-colors">${colors.map(c => '<span style="background:'+c+'"></span>').join("")}</div>
            <div class="preset-name">${p.name || "Preset"}</div>
            <div class="preset-meta">${p.industry || ""} · ${p.style || ""} · ${p.mood || ""}</div>
        `;
        div.onclick = () => {
            grid.querySelectorAll(".preset-card").forEach(c => c.classList.remove("selected"));
            div.classList.add("selected");
            document.getElementById("wPrompt").value = p.prompt || "";
            selectChip("wIndustry", p.industry || "");
            selectChip("wStyle", p.style || "");
            selectChip("wMood", p.mood || "");
            if (p.tone) selectChip("wTone", p.tone);
        };
        grid.appendChild(div);
    });
})();

// ═══════════════════════════════════════════
// STEP NAVIGATION
// ═══════════════════════════════════════════
function goToStep(n) {
    state.step = n;
    saveState();
    document.querySelectorAll(".step-content").forEach(el => el.classList.add("hidden"));
    document.getElementById("step" + n + "Content").classList.remove("hidden");
    document.querySelectorAll(".step-item").forEach(si => {
        const s = parseInt(si.dataset.step);
        si.classList.toggle("active", s === n);
        si.classList.toggle("done", s < n);
    });
    // Update connectors
    document.querySelectorAll(".step-connector").forEach((conn, idx) => {
        conn.style.background = idx < n - 1 ? "var(--ctp-green)" : "var(--ctp-surface0)";
    });
    // Auto-init step content when navigating (handles Back button scenarios)
    if (n === 2 && typeof initStep2 === "function") {
        initStep2();
        const industry = state.brief?.industry || '';
        initBusinessProfile(industry);
    }
    if (n === 3 && typeof initDesignStep === "function") initDesignStep();
    if (n === 5 && typeof initStep5 === "function") initStep5();
    // Scroll to top of new step
    setTimeout(() => {
        document.querySelector(".step-item.active")?.scrollIntoView({behavior:"smooth", block:"start"});
    }, 50);
}

// ═══════════════════════════════════════════
// STEP 1: CONFIGURE — GENERATE BRIEF
// ═══════════════════════════════════════════
document.getElementById("btnGenerateBrief").onclick = async function() {
    const btn = this;
    btn.classList.add("loading");
    btn.disabled = true;
    const {provider, model} = getModelParts();
    try {
        const result = await api("/api/ai-theme-builder/wizard/brief", {
            prompt: document.getElementById("wPrompt").value,
            industry: getChipValue("wIndustry"),
            style: getChipValue("wStyle"),
            mood: getChipValue("wMood"),
            tone: getChipValue("wTone"),
            language: document.getElementById("wLang").value,
            creativity: document.getElementById('wCreativity').value,
            provider, model
        });
        if (result.ok) {
            state.brief = result.brief;
            state.slug = result.slug;

            // Show brief confirmation in Step 1
            document.getElementById("briefReview").classList.remove("hidden");
            document.getElementById("briefConfirmName").textContent = result.brief.name || "Theme Brief Generated";
            const colorCount = Object.keys(result.brief.colors || {}).length;
            const sectionCount = (result.brief.homepage_sections || []).length;
            document.getElementById("briefConfirmMeta").textContent = colorCount + " colors · " + (result.brief.typography?.headingFont || "—") + " / " + (result.brief.typography?.fontFamily || "—") + " · " + sectionCount + " sections";
            setTimeout(() => {
                document.getElementById("briefReview").scrollIntoView({behavior:"smooth", block:"start"});
            }, 100);

            // Pre-fill business name
            if (result.brief.name && !document.getElementById("bizName").value) {
                document.getElementById("bizName").value = result.brief.name;
            }

            toast("Brief generated!", "success");
            saveState();

            // Enable Step 1 Next button (but show hint that content plan is loading)
            document.getElementById("step1NextHint").textContent = "Generating content plan...";
            document.getElementById("step1NextHint").style.display = "";

            // Initialize design settings (pages, sections, header/footer) in Step 1
            initStep3();

            // Generate content plan
            document.getElementById("contentPlanLoading").classList.remove("hidden");
            try {
                const planResult = await api("/api/ai-theme-builder/wizard/content-plan", {
                    prompt: document.getElementById("wPrompt").value,
                    industry: getChipValue("wIndustry"),
                    language: document.getElementById("wLang").value,
                    pages: PAGE_TYPES.filter(p => p.required || p.defaultOn).map(p => p.id),
                    tone: getChipValue("wTone"),
                    provider, model
                });
                if (planResult.ok && planResult.content_plan) {
                    state.contentPlan = planResult.content_plan;
                    // Set pages from content plan
                    state.pages = Object.keys(planResult.content_plan);
                    if (!state.pages.includes("home")) state.pages.unshift("home");
                    renderContentPlanCards();
                    toast("Content plan ready!", "success");
                } else {
                    // Fallback: create basic content plan from PAGE_TYPES
                    const defaultPages = PAGE_TYPES.filter(p => p.required || p.defaultOn).map(p => p.id);
                    state.pages = defaultPages;
                    defaultPages.forEach(pid => {
                        state.contentPlan[pid] = {
                            title: ucfirst(pid),
                            content_brief: "",
                            keywords: {},
                            outline: [],
                            meta_description: "",
                            word_count_target: 500
                        };
                    });
                    renderContentPlanCards();
                    toast("Using default content plan (API returned no plan)", "info");
                }
            } catch(planErr) {
                // Fallback: create basic content plan from PAGE_TYPES
                const defaultPages = PAGE_TYPES.filter(p => p.required || p.defaultOn).map(p => p.id);
                state.pages = defaultPages;
                defaultPages.forEach(pid => {
                    state.contentPlan[pid] = {
                        title: ucfirst(pid),
                        content_brief: "",
                        keywords: {},
                        outline: [],
                        meta_description: "",
                        word_count_target: 500
                    };
                });
                renderContentPlanCards();
                toast("Content plan: using defaults (" + planErr.message + ")", "info");
            }
            document.getElementById("contentPlanLoading").classList.add("hidden");
            // Now enable Step 1 Next fully
            document.getElementById("btnStep1Next").disabled = false;
            document.getElementById("step1NextHint").style.display = "none";
            saveState();
        } else {
            if (result.error_info) {
                showAIError(result.error_info, result.error);
            } else {
                toast("Error: " + (result.error || "Unknown error"), "error");
            }
        }
    } catch(e) {
        toast("Error: " + e.message, "error");
    }
    btn.classList.remove("loading");
    btn.disabled = false;
};

// ═══════════════════════════════════════════
// PRESET PALETTES
// ═══════════════════════════════════════════
const COLOR_PALETTES = [
    {name:"Ocean Breeze",     colors:{primary:"#0077b6",secondary:"#00b4d8",accent:"#90e0ef",background:"#ffffff",surface:"#f0f7ff",text:"#1b2a4a"}},
    {name:"Forest Night",     colors:{primary:"#2d6a4f",secondary:"#40916c",accent:"#95d5b2",background:"#0d1b0e",surface:"#1a2f1c",text:"#d8f3dc"}},
    {name:"Sunset Glow",      colors:{primary:"#e85d04",secondary:"#f48c06",accent:"#ffba08",background:"#ffffff",surface:"#fff8f0",text:"#370617"}},
    {name:"Royal Purple",     colors:{primary:"#7209b7",secondary:"#560bad",accent:"#b5179e",background:"#0f0326",surface:"#1a0a3e",text:"#e0d6ff"}},
    {name:"Terracotta",       colors:{primary:"#c2703e",secondary:"#a44a3f",accent:"#d4a373",background:"#fefae0",surface:"#f5ebe0",text:"#3d2c2e"}},
    {name:"Midnight Blue",    colors:{primary:"#3a86ff",secondary:"#5e60ce",accent:"#48bfe3",background:"#0a0e27",surface:"#141a3d",text:"#caf0f8"}},
    {name:"Rose Gold",        colors:{primary:"#b76e79",secondary:"#c9a0a0",accent:"#e8c4c4",background:"#ffffff",surface:"#fdf2f2",text:"#4a2c2a"}},
    {name:"Nordic Frost",     colors:{primary:"#5e81ac",secondary:"#81a1c1",accent:"#88c0d0",background:"#eceff4",surface:"#e5e9f0",text:"#2e3440"}},
    {name:"Neon Cyber",       colors:{primary:"#00ff87",secondary:"#60efff",accent:"#ff00e5",background:"#0a0a0a",surface:"#1a1a2e",text:"#e0e0e0"}},
    {name:"Warm Earth",       colors:{primary:"#8b5e3c",secondary:"#a0522d",accent:"#daa520",background:"#fffdf7",surface:"#f5efe6",text:"#3b2f22"}},
    {name:"Coral Reef",       colors:{primary:"#ff6b6b",secondary:"#ee5a24",accent:"#ffc048",background:"#ffffff",surface:"#fff5f5",text:"#2d3436"}},
    {name:"Sage & Cream",     colors:{primary:"#6b8f71",secondary:"#97b8a3",accent:"#aed9a8",background:"#fdfdf5",surface:"#f0f0e8",text:"#2f3e2f"}},
    {name:"Charcoal Minimal", colors:{primary:"#f8f8f8",secondary:"#cccccc",accent:"#ff4444",background:"#1a1a1a",surface:"#252525",text:"#f0f0f0"}},
    {name:"Lavender Dream",   colors:{primary:"#7c5cbf",secondary:"#9d8ec7",accent:"#c3aed6",background:"#ffffff",surface:"#f8f5ff",text:"#2d2640"}},
    {name:"Cherry Blossom",   colors:{primary:"#d63384",secondary:"#e685b5",accent:"#fbb1bd",background:"#ffffff",surface:"#fff0f5",text:"#3d1f33"}},
    {name:"Steel & Gold",     colors:{primary:"#c9a227",secondary:"#967117",accent:"#f0d060",background:"#0f1419",surface:"#1c252e",text:"#d4d8dd"}},
    {name:"Arctic Clean",     colors:{primary:"#0066ff",secondary:"#4d94ff",accent:"#99c2ff",background:"#ffffff",surface:"#f7f9fc",text:"#1a1a2e"}},
    {name:"Tropical",         colors:{primary:"#00b894",secondary:"#00cec9",accent:"#ffeaa7",background:"#ffffff",surface:"#f0fff4",text:"#2d3436"}},
    {name:"Mocha",            colors:{primary:"#6f4e37",secondary:"#8b6f47",accent:"#c4a882",background:"#1e1e2e",surface:"#2a2a3e",text:"#e0d6cc"}},
    {name:"Electric Blue",    colors:{primary:"#2563eb",secondary:"#3b82f6",accent:"#60a5fa",background:"#030712",surface:"#111827",text:"#f9fafb"}},
    {name:"Brutalist Raw",    colors:{primary:"#ff0000",secondary:"#000000",accent:"#ffff00",background:"#ffffff",surface:"#f0f0f0",text:"#000000"}},
    {name:"Art Deco Gold",    colors:{primary:"#d4af37",secondary:"#b8860b",accent:"#f0e68c",background:"#1a1a2e",surface:"#232342",text:"#f5f0eb"}},
    {name:"Glassmorphism",    colors:{primary:"#6c63ff",secondary:"#9d4edd",accent:"#c77dff",background:"#0f0c29",surface:"#1a1640",text:"#e0e0ff"}},
    {name:"Retro Vintage",    colors:{primary:"#c44536",secondary:"#e76f51",accent:"#f4a261",background:"#fdf6e3",surface:"#f0e8d0",text:"#2b2d42"}},
    {name:"Pastel Soft",      colors:{primary:"#a78bfa",secondary:"#f0abfc",accent:"#67e8f9",background:"#fefce8",surface:"#fef3c7",text:"#4c1d95"}},
    {name:"Neon Punk",        colors:{primary:"#ff00ff",secondary:"#00ffff",accent:"#ffff00",background:"#0a0a0a",surface:"#1a0a2e",text:"#ffffff"}},
    {name:"Corporate Navy",   colors:{primary:"#1e3a5f",secondary:"#3a6ea5",accent:"#ff6b35",background:"#ffffff",surface:"#f4f6f8",text:"#1a2332"}},
    {name:"Geometric Bold",   colors:{primary:"#e63946",secondary:"#457b9d",accent:"#a8dadc",background:"#f1faee",surface:"#e8f0e8",text:"#1d3557"}},
    {name:"Neubrutalism",     colors:{primary:"#ff5722",secondary:"#ffc107",accent:"#8bc34a",background:"#ffffff",surface:"#fff9c4",text:"#1a1a1a"}},
    // Industry-specific palettes
    {name:"Medical Trust",    colors:{primary:"#0891b2",secondary:"#06b6d4",accent:"#22d3ee",background:"#ffffff",surface:"#f0fdfa",text:"#134e4a"},tag:"medical"},
    {name:"Dental Fresh",     colors:{primary:"#14b8a6",secondary:"#2dd4bf",accent:"#5eead4",background:"#ffffff",surface:"#f0fdfa",text:"#115e59"},tag:"dental"},
    {name:"Legal Authority",  colors:{primary:"#92400e",secondary:"#b45309",accent:"#d4a017",background:"#1c1917",surface:"#292524",text:"#fafaf9"},tag:"law"},
    {name:"Fine Dining",      colors:{primary:"#d4a574",secondary:"#b08968",accent:"#ddb892",background:"#0f0b08",surface:"#1a1510",text:"#faf0e6"},tag:"restaurant"},
    {name:"Pizzeria Fun",     colors:{primary:"#dc2626",secondary:"#ea580c",accent:"#f59e0b",background:"#fffbeb",surface:"#fef3c7",text:"#451a03"},tag:"restaurant"},
    {name:"Sushi Zen",        colors:{primary:"#be123c",secondary:"#1a1a2e",accent:"#c9a96e",background:"#0f0f0f",surface:"#1a1a1a",text:"#f5f0eb"},tag:"restaurant"},
    {name:"Café Latte",       colors:{primary:"#8b6f47",secondary:"#a0845c",accent:"#c4a882",background:"#faf7f2",surface:"#f0ece4",text:"#3d2c1e"},tag:"cafe"},
    {name:"Tech Startup",     colors:{primary:"#6366f1",secondary:"#818cf8",accent:"#a5b4fc",background:"#0f172a",surface:"#1e293b",text:"#e2e8f0"},tag:"saas"},
    {name:"Crypto Dark",      colors:{primary:"#10b981",secondary:"#34d399",accent:"#6ee7b7",background:"#030712",surface:"#111827",text:"#f9fafb"},tag:"crypto"},
    {name:"Agency Bold",      colors:{primary:"#ec4899",secondary:"#f472b6",accent:"#f9a8d4",background:"#0c0a09",surface:"#1c1917",text:"#fafaf9"},tag:"agency"},
    {name:"Photography",      colors:{primary:"#f5f5f4",secondary:"#a8a29e",accent:"#ef4444",background:"#0a0a0a",surface:"#171717",text:"#fafafa"},tag:"photography"},
    {name:"Fitness Power",    colors:{primary:"#ef4444",secondary:"#f97316",accent:"#eab308",background:"#0a0a0a",surface:"#171717",text:"#fafafa"},tag:"fitness"},
    {name:"Yoga Calm",        colors:{primary:"#8b5cf6",secondary:"#a78bfa",accent:"#c4b5fd",background:"#faf5ff",surface:"#f3e8ff",text:"#3b0764"},tag:"yoga"},
    {name:"Real Estate",      colors:{primary:"#0369a1",secondary:"#0284c7",accent:"#38bdf8",background:"#ffffff",surface:"#f0f9ff",text:"#0c4a6e"},tag:"realestate"},
    {name:"Education Bright", colors:{primary:"#2563eb",secondary:"#3b82f6",accent:"#f59e0b",background:"#ffffff",surface:"#eff6ff",text:"#1e3a5f"},tag:"education"},
    {name:"Wedding Blush",    colors:{primary:"#be185d",secondary:"#db2777",accent:"#f9a8d4",background:"#fff1f2",surface:"#ffe4e6",text:"#4c0519"},tag:"wedding"},
    {name:"Eco Green",        colors:{primary:"#15803d",secondary:"#22c55e",accent:"#86efac",background:"#f0fdf4",surface:"#dcfce7",text:"#14532d"},tag:"organic"},
    {name:"Luxury Gold",      colors:{primary:"#b8860b",secondary:"#d4a017",accent:"#f0d060",background:"#0a0a0a",surface:"#1a1510",text:"#faf0e6"},tag:"jewelry"},
    {name:"Construction",     colors:{primary:"#ea580c",secondary:"#f97316",accent:"#fdba74",background:"#ffffff",surface:"#fff7ed",text:"#431407"},tag:"construction"},
    {name:"Automotive Dark",  colors:{primary:"#dc2626",secondary:"#ef4444",accent:"#fca5a5",background:"#0f0f0f",surface:"#1a1a1a",text:"#f5f5f5"},tag:"automotive"},
    {name:"Fashion Mono",     colors:{primary:"#000000",secondary:"#404040",accent:"#d4d4d4",background:"#ffffff",surface:"#fafafa",text:"#0a0a0a"},tag:"fashion"},
    {name:"Kids Playful",     colors:{primary:"#8b5cf6",secondary:"#ec4899",accent:"#f59e0b",background:"#fefce8",surface:"#fdf4ff",text:"#1e1b4b"},tag:"childcare"},
    {name:"Church Warm",      colors:{primary:"#7c3aed",secondary:"#8b5cf6",accent:"#c4b5fd",background:"#faf5ff",surface:"#ede9fe",text:"#2e1065"},tag:"church"},
    {name:"Gaming Neon",      colors:{primary:"#a855f7",secondary:"#ec4899",accent:"#06b6d4",background:"#030712",surface:"#0f172a",text:"#e2e8f0"},tag:"gamedev"},
];

// ═══════════════════════════════════════════
// GOOGLE FONTS (popular, grouped)
// ═══════════════════════════════════════════
const GOOGLE_FONTS = {
    serif: [
        "Playfair Display","Lora","Merriweather","Libre Baskerville","Crimson Pro",
        "Source Serif 4","DM Serif Display","Cormorant Garamond","EB Garamond",
        "Bitter","Roboto Slab","Noto Serif","PT Serif","Vollkorn","Cardo",
        "Old Standard TT","Spectral","Literata","Fraunces","Bodoni Moda",
        "Cormorant","Newsreader","Gelasio","Brygada 1918","Petrona",
        "Noto Serif Display","Instrument Serif","Young Serif","Lancelot","Alike"
    ],
    sans: [
        "Inter","Poppins","Montserrat","Open Sans","Lato","Nunito","Raleway",
        "Source Sans 3","Work Sans","DM Sans","Outfit","Manrope","Space Grotesk",
        "Plus Jakarta Sans","Figtree","Sora","Albert Sans","General Sans",
        "IBM Plex Sans","Rubik","Barlow","Mulish","Jost","Urbanist","Quicksand",
        "Nunito Sans","Archivo","Red Hat Display","Overpass","Be Vietnam Pro",
        "Geist","Bricolage Grotesque","Onest","Gabarito","Inclusive Sans",
        "Lexend","Wix Madefor Display","Red Hat Text","Atkinson Hyperlegible","Karla",
        "Schibsted Grotesk","Hanken Grotesk","Public Sans","Readex Pro","Noto Sans"
    ],
    display: [
        "Oswald","Bebas Neue","Abril Fatface","Righteous","Fredoka One",
        "Staatliches","Bungee","Black Ops One","Anton","Permanent Marker",
        "Comfortaa","Satisfy","Pacifico","Lobster","Caveat",
        "Unbounded","Climate Crisis","Silkscreen","Press Start 2P","Monoton",
        "Bagel Fat One","Bungee Shade","Rampart One","Rubik Glitch","Rubik Wet Paint"
    ],
    mono: [
        "JetBrains Mono","Fira Code","Source Code Pro","IBM Plex Mono","Space Mono",
        "Roboto Mono","Inconsolata","Ubuntu Mono",
        "Red Hat Mono","Martian Mono","Geist Mono","DM Mono"
    ]
};

// ═══════════════════════════════════════════
// AVAILABLE HOMEPAGE SECTIONS
// ═══════════════════════════════════════════
const ALL_SECTIONS = [
    {id:"hero",       label:"Hero",            icon:"⭐", required:true},
    {id:"about",      label:"About",           icon:"📖"},
    {id:"services",   label:"Services",        icon:"🔧"},
    {id:"features",   label:"Features",        icon:"✨"},
    {id:"portfolio",  label:"Portfolio",        icon:"🎨"},
    {id:"gallery",    label:"Gallery",         icon:"🖼️"},
    {id:"team",       label:"Team",            icon:"👥"},
    {id:"testimonials",label:"Testimonials",   icon:"💬"},
    {id:"pricing",    label:"Pricing",         icon:"💰"},
    {id:"faq",        label:"FAQ",             icon:"❓"},
    {id:"blog",       label:"Blog / Articles", icon:"📰"},
    {id:"articles",   label:"Latest Articles", icon:"📰"},
    {id:"pages",      label:"Pages Grid",      icon:"📋"},
    {id:"cta",        label:"Call to Action",  icon:"🎯"},
    {id:"contact",    label:"Contact",         icon:"📧"},
    {id:"stats",      label:"Stats / Numbers", icon:"📊"},
    {id:"clients",    label:"Clients / Logos", icon:"🏢"},
    {id:"process",    label:"Process / Steps", icon:"📝"},
    {id:"menu",       label:"Menu (Food)",     icon:"🍽️"},
    {id:"events",     label:"Events",          icon:"📅"},
    {id:"newsletter", label:"Newsletter",      icon:"✉️"},
    {id:"map",        label:"Map / Location",  icon:"📍"},
    {id:"video",      label:"Video Section",   icon:"🎬"},
    {id:"comparison", label:"Comparison",      icon:"⚖️"},
    {id:"timeline",   label:"Timeline",        icon:"📆"},
];

function buildFontSelect(selectEl, currentValue) {
    selectEl.innerHTML = "";
    const groups = {
        "Sans-Serif": GOOGLE_FONTS.sans,
        "Serif": GOOGLE_FONTS.serif,
        "Display": GOOGLE_FONTS.display,
        "Monospace": GOOGLE_FONTS.mono
    };
    for (const [groupName, fonts] of Object.entries(groups)) {
        const optgroup = document.createElement("optgroup");
        optgroup.label = groupName;
        fonts.forEach(f => {
            const opt = document.createElement("option");
            opt.value = f;
            opt.textContent = f;
            if (f === currentValue) opt.selected = true;
            optgroup.appendChild(opt);
        });
        selectEl.appendChild(optgroup);
    }
    // If current value not in list, add it at top
    const found = Array.from(selectEl.options).some(o => o.value === currentValue);
    if (currentValue && !found) {
        const opt = document.createElement("option");
        opt.value = currentValue;
        opt.textContent = currentValue + " (AI picked)";
        opt.selected = true;
        selectEl.prepend(opt);
    }
}

function buildPalettePresets() {
    const container = document.getElementById("palettePresets");
    container.innerHTML = "";
    COLOR_PALETTES.forEach((p, i) => {
        const div = document.createElement("div");
        div.className = "palette-preset";
        div.title = p.name;
        div.dataset.index = i;
        const keys = ["primary","secondary","accent","background","surface","text"];
        keys.forEach(k => {
            const c = document.createElement("div");
            c.className = "palette-preset-color";
            c.style.background = p.colors[k] || "#888";
            div.appendChild(c);
        });
        const nameSpan = document.createElement("div");
        nameSpan.className = "palette-preset-name";
        nameSpan.textContent = p.name;
        div.appendChild(nameSpan);
        div.onclick = () => {
            state.brief.colors = {...p.colors};
            showBriefReview(state.brief);
            // Highlight selected
            container.querySelectorAll(".palette-preset").forEach(el => el.classList.remove("selected"));
            div.classList.add("selected");
            toast("Palette: " + p.name, "success");
        };
        container.appendChild(div);
    });
}

function updateAddSectionDropdown() {
    const sel = document.getElementById("addSectionSelect");
    sel.innerHTML = '<option value="">+ Add section...</option>';
    const existing = (state.brief?.homepage_sections || []).map(s => s.id);
    ALL_SECTIONS.filter(s => !existing.includes(s.id)).forEach(s => {
        const opt = document.createElement("option");
        opt.value = s.id;
        opt.textContent = s.icon + " " + s.label;
        sel.appendChild(opt);
    });
}

function showBriefReview(brief) {
    // Build the brief review card HTML dynamically into Step 3's panel
    const panel = document.getElementById("briefReviewPanel3");
    panel.innerHTML = `
        <div class="brief-card">
            <div class="brief-row" style="border:0;margin-bottom:8px">
                <span class="brief-key" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em">Theme Name</span>
                <input type="text" class="brief-val-editable" id="briefName" value="" style="font-size:15px;font-weight:600">
            </div>
            <div style="margin-bottom:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <span class="label" style="margin:0">Color Palette</span>
                    <button class="brief-regen-btn" id="btnRegenColorsPreview" title="Regenerate colors only"><i class="fas fa-sync-alt"></i> AI Colors</button>
                </div>
                <div class="palette" id="briefPalette"></div>
                <div style="margin-top:8px">
                    <span style="font-size:10px;color:var(--ctp-overlay0);text-transform:uppercase;letter-spacing:.05em">Or pick a preset palette:</span>
                    <div class="palette-presets" id="palettePresets"></div>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <span class="label" style="margin:0">Typography</span>
                <button class="brief-regen-btn" id="btnRegenFonts" title="Regenerate fonts only"><i class="fas fa-sync-alt"></i> AI Fonts</button>
            </div>
            <div class="brief-row">
                <span class="brief-key">Heading Font</span>
                <select class="brief-font-select" id="briefFontH"></select>
            </div>
            <div class="brief-row">
                <span class="brief-key">Body Font</span>
                <select class="brief-font-select" id="briefFontB"></select>
            </div>
            <div class="brief-row">
                <span class="brief-key">Style · Mood</span>
                <span class="brief-val" id="briefStyle">—</span>
            </div>
            <div style="margin-top:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <span class="label" style="margin:0">Homepage Sections</span>
                    <button class="brief-regen-btn" id="btnRegenSections" title="Regenerate sections only"><i class="fas fa-sync-alt"></i> AI Sections</button>
                </div>
                <div class="brief-section-group" id="briefSections"></div>
                <div class="brief-add-section">
                    <select class="brief-add-select" id="addSectionSelect"><option value="">+ Add section...</option></select>
                    <button class="brief-add-btn" id="btnAddSection">Add</button>
                </div>
            </div>
        </div>
    `;

    // Populate fields
    document.getElementById("briefName").value = brief.name || "Untitled Theme";
    document.getElementById("briefName").onchange = e => { state.brief.name = e.target.value; };

    // Font dropdowns
    const headFont = brief.typography?.headingFont || brief.headingFont || "Inter";
    const bodyFont = brief.typography?.fontFamily || brief.fontFamily || "Inter";
    buildFontSelect(document.getElementById("briefFontH"), headFont);
    buildFontSelect(document.getElementById("briefFontB"), bodyFont);
    document.getElementById("briefFontH").onchange = e => {
        if (!state.brief.typography) state.brief.typography = {};
        state.brief.typography.headingFont = e.target.value;
        updateGoogleFontsUrl();
    };
    document.getElementById("briefFontB").onchange = e => {
        if (!state.brief.typography) state.brief.typography = {};
        state.brief.typography.fontFamily = e.target.value;
        updateGoogleFontsUrl();
    };

    // Style & mood
    document.getElementById("briefStyle").textContent = (brief.designStyle || brief.style || "") + " · " + (brief.mood || "");

    // Color palette with editable swatches
    const palette = document.getElementById("briefPalette");
    palette.innerHTML = "";
    const colors = brief.colors || {};
    ["primary","secondary","accent","background","surface","text"].forEach(key => {
        if (!colors[key]) return;
        const wrap = document.createElement("div");
        wrap.style.textAlign = "center";
        wrap.innerHTML = `
            <div class="palette-swatch"><input type="color" value="${colors[key]}" data-key="${key}"></div>
            <div class="palette-label">${key}</div>
            <div class="palette-hex">${colors[key]}</div>
        `;
        wrap.querySelector("input").onchange = e => {
            state.brief.colors[key] = e.target.value;
            wrap.querySelector(".palette-hex").textContent = e.target.value;
            document.querySelectorAll(".palette-preset").forEach(el => el.classList.remove("selected"));
        };
        palette.appendChild(wrap);
    });

    buildPalettePresets();

    // Homepage sections
    const sectionsDiv = document.getElementById("briefSections");
    sectionsDiv.innerHTML = "";
    (brief.homepage_sections || []).forEach((sec, i) => {
        const item = document.createElement("div");
        item.className = "brief-section-item";
        item.draggable = true;
        item.dataset.sectionIdx = i;
        item.dataset.sectionId = sec.id;
        item.innerHTML = `
            <span class="brief-section-grip" style="cursor:grab;opacity:0.35;margin-right:4px;font-size:11px;user-select:none" title="Drag to reorder">☰</span>
            <span class="brief-section-icon">${sec.icon || "📄"}</span>
            <span class="brief-section-name">${sec.label || sec.id}</span>
            ${sec.required ? '<span class="brief-section-badge">required</span>' : ''}
            ${!sec.required ? `<button class="brief-regen-btn" onclick="removeBriefSection(${i})" title="Remove section"><i class="fas fa-times" style="color:var(--ctp-red)"></i></button>` : ''}
        `;
        sectionsDiv.appendChild(item);
    });

    // Wire DnD reordering for brief sections
    wireBriefSectionDnD(sectionsDiv);

    updateAddSectionDropdown();

    if (brief.name && !document.getElementById("bizName").value) {
        document.getElementById("bizName").value = brief.name;
    }

    // Bind regen button handlers (elements now exist in DOM)
    bindBriefRegenHandlers();
}

function updateGoogleFontsUrl() {
    if (!state.brief) return;
    const h = state.brief.typography?.headingFont || "Inter";
    const b = state.brief.typography?.fontFamily || "Inter";
    const families = [...new Set([h, b])].map(f => "family=" + encodeURIComponent(f) + ":wght@300;400;500;600;700").join("&");
    state.brief.google_fonts_url = "https://fonts.googleapis.com/css2?" + families + "&display=swap";
}

function removeBriefSection(index) {
    if (!state.brief?.homepage_sections) return;
    const sec = state.brief.homepage_sections[index];
    if (sec?.required) return;
    state.brief.homepage_sections.splice(index, 1);
    showBriefReview(state.brief);
    toast("Section removed", "success");
}

// Drag-and-drop reordering for brief homepage sections (Step 2)
function wireBriefSectionDnD(container) {
    if (!container) return;
    let dragEl = null;
    container.querySelectorAll('.brief-section-item[data-section-id]').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            // Only allow drag from grip handle
            if (!e.target.closest('.brief-section-grip') && e.target !== item) {
                // Still allow — dragstart fires on the item
            }
            dragEl = item;
            item.style.opacity = '0.4';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.dataset.sectionId);
        });
        item.addEventListener('dragend', function() {
            item.style.opacity = '1';
            dragEl = null;
            container.querySelectorAll('.brief-section-item').forEach(i => {
                i.style.borderTop = '';
                i.style.paddingTop = '';
            });
        });
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (dragEl && dragEl !== item) {
                item.style.borderTop = '2px solid var(--ctp-blue)';
            }
        });
        item.addEventListener('dragleave', function() {
            item.style.borderTop = '';
        });
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            item.style.borderTop = '';
            if (dragEl && dragEl !== item && state.brief?.homepage_sections) {
                // Calculate new order from DOM positions
                const allItems = Array.from(container.querySelectorAll('.brief-section-item[data-section-id]'));
                const fromIdx = allItems.indexOf(dragEl);
                const toIdx = allItems.indexOf(item);
                if (fromIdx !== -1 && toIdx !== -1) {
                    // Reorder the array
                    const sections = state.brief.homepage_sections;
                    const [moved] = sections.splice(fromIdx, 1);
                    sections.splice(toIdx, 0, moved);
                    // Re-render to update indices (for remove buttons)
                    showBriefReview(state.brief);
                    toast("Section reordered", "success");
                }
            }
        });
    });
}

// Bind regen handlers after showBriefReview() builds the DOM elements
function bindBriefRegenHandlers() {
    const addBtn = document.getElementById("btnAddSection");
    if (addBtn) addBtn.onclick = function() {
        const sel = document.getElementById("addSectionSelect");
        const id = sel.value;
        if (!id || !state.brief) return;
        const tpl = ALL_SECTIONS.find(s => s.id === id);
        if (!tpl) return;
        if (!state.brief.homepage_sections) state.brief.homepage_sections = [];
        state.brief.homepage_sections.push({id: tpl.id, label: tpl.label, icon: tpl.icon});
        showBriefReview(state.brief);
        toast("Added: " + tpl.label, "success");
    };

    const regenColors = document.getElementById("btnRegenColorsPreview");
    if (regenColors) regenColors.onclick = async function() {
        if (!state.brief) return;
        const btn = this;
        btn.classList.add("loading");
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
        try {
            const {provider, model} = getModelParts();
            const result = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value + " — REGENERATE ONLY THE COLOR PALETTE. Keep everything else the same. Choose completely different colors.",
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"), mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (result.ok && result.brief?.colors) {
                state.brief.colors = result.brief.colors;
                showBriefReview(state.brief);
                toast("Colors regenerated!", "success");
            } else { toast("Error regenerating colors", "error"); }
        } catch(e) { toast("Error: " + e.message, "error"); }
        btn.classList.remove("loading");
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> AI Colors';
    };

    const regenFonts = document.getElementById("btnRegenFonts");
    if (regenFonts) regenFonts.onclick = async function() {
        if (!state.brief) return;
        const btn = this;
        btn.classList.add("loading");
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
        try {
            const {provider, model} = getModelParts();
            const result = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value + " — REGENERATE ONLY THE TYPOGRAPHY/FONTS. Keep everything else the same. Choose completely different Google Fonts.",
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"), mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (result.ok && result.brief?.typography) {
                state.brief.typography = result.brief.typography;
                if (result.brief.google_fonts_url) state.brief.google_fonts_url = result.brief.google_fonts_url;
                showBriefReview(state.brief);
                toast("Fonts regenerated!", "success");
            } else { toast("Error regenerating fonts", "error"); }
        } catch(e) { toast("Error: " + e.message, "error"); }
        btn.classList.remove("loading");
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> AI Fonts';
    };

    const regenSections = document.getElementById("btnRegenSections");
    if (regenSections) regenSections.onclick = async function() {
        if (!state.brief) return;
        const btn = this;
        btn.classList.add("loading");
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
        try {
            const {provider, model} = getModelParts();
            const result = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value + " — REGENERATE ONLY THE HOMEPAGE SECTIONS. Keep colors and fonts the same. Choose different, creative sections for this industry.",
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"), mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (result.ok && result.brief?.homepage_sections) {
                state.brief.homepage_sections = result.brief.homepage_sections;
                showBriefReview(state.brief);
                toast("Sections regenerated!", "success");
            } else { toast("Error regenerating sections", "error"); }
        } catch(e) { toast("Error: " + e.message, "error"); }
        btn.classList.remove("loading");
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> AI Sections';
    };
}

document.getElementById("btnRegenBrief").onclick = () => {
    document.getElementById("btnGenerateBrief").click();
};

// Step 1 → Step 2 (Content)
document.getElementById("btnStep1Next").onclick = () => {
    if (!state.brief) { toast("Generate a brief first", "error"); return; }
    goToStep(2); // goToStep auto-calls initStep2()
};

// ═══════════════════════════════════════════
// PAGE TYPES (used by content plan + design step)
// ═══════════════════════════════════════════
const PAGE_TYPES = [
    {id:"home", name:"Home", icon:"fas fa-home", required:true, defaultOn:true},
    {id:"about", name:"About", icon:"fas fa-info-circle", required:false, defaultOn:true},
    {id:"services", name:"Services", icon:"fas fa-concierge-bell", required:false, defaultOn:true},
    {id:"gallery", name:"Gallery", icon:"fas fa-images", required:false, defaultOn:false},
    {id:"blog", name:"Blog", icon:"fas fa-blog", required:false, defaultOn:true},
    {id:"contact", name:"Contact", icon:"fas fa-envelope", required:false, defaultOn:true},
    {id:"pricing", name:"Pricing", icon:"fas fa-tags", required:false, defaultOn:false},
    {id:"portfolio", name:"Portfolio", icon:"fas fa-th-large", required:false, defaultOn:false},
    {id:"team", name:"Team", icon:"fas fa-users", required:false, defaultOn:false},
    {id:"faq", name:"FAQ", icon:"fas fa-question-circle", required:false, defaultOn:false},
    {id:"testimonials", name:"Testimonials", icon:"fas fa-quote-right", required:false, defaultOn:false},
    {id:"features", name:"Features", icon:"fas fa-star", required:false, defaultOn:false},
];

// ═══════════════════════════════════════════
// Content Plan Cards (shown in Step 1 after brief)
// ═══════════════════════════════════════════
function renderContentPlanCards() {
    const container = document.getElementById("contentPlanCards");
    if (!container) return;
    container.innerHTML = "";

    // Include all pages from content plan + PAGE_TYPES defaults
    const allPageIds = [...new Set([...state.pages, ...Object.keys(state.contentPlan)])];

    allPageIds.forEach(pageId => {
        const plan = state.contentPlan[pageId] || {};
        const isIncluded = state.pages.includes(pageId);
        const isHome = pageId === "home";
        const kw = plan.keywords || {};
        const outlineArr = plan.outline || [];

        const card = document.createElement("div");
        card.className = "cp-card";
        card.style.cssText = "background:var(--ctp-surface0);border-radius:10px;padding:14px;margin-bottom:8px;border:1px solid var(--ctp-surface1);" + (!isIncluded ? "opacity:.5;" : "");
        card.dataset.page = pageId;

        let outlineHtml = "";
        outlineArr.forEach((o, oi) => {
            outlineHtml += '<div style="display:flex;align-items:center;gap:4px;margin-bottom:3px">'
                + '<span style="color:var(--ctp-overlay0);font-size:10px;width:14px">H2</span>'
                + '<input class="input cp-outline-input" data-page="' + pageId + '" data-idx="' + oi + '" value="' + (o.heading || "").replace(/"/g, "&quot;") + '" style="font-size:11px;padding:3px 6px;flex:1">'
                + '</div>';
        });

        card.innerHTML = ''
            // Row 1: checkbox + icon + title + word count
            + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">'
            + '<input type="checkbox" class="cp-include-cb" data-page="' + pageId + '" ' + (isIncluded ? 'checked' : '') + (isHome ? ' disabled' : '') + ' style="margin:0">'
            + '<i class="' + getPageIcon(pageId) + '" style="color:var(--ctp-blue);width:16px;text-align:center;font-size:13px"></i>'
            + '<input class="input cp-title-input" data-page="' + pageId + '" value="' + (plan.title || ucfirst(pageId)).replace(/"/g, "&quot;") + '" style="flex:1;font-size:13px;font-weight:600;padding:3px 6px">'
            + '<span style="font-size:10px;color:var(--ctp-overlay0);white-space:nowrap">' + (plan.word_count_target || 500) + 'w</span>'
            + '</div>'
            // Row 2: content brief (editable)
            + '<textarea class="input cp-brief-input" data-page="' + pageId + '" rows="2" style="font-size:11px;padding:4px 6px;margin-bottom:6px;width:100%;resize:vertical;line-height:1.4" placeholder="Content brief...">' + (plan.content_brief || '') + '</textarea>'
            // Row 3: keywords
            + '<div style="display:flex;gap:4px;align-items:center;margin-bottom:6px;flex-wrap:wrap">'
            + '<span style="font-size:10px;color:var(--ctp-overlay0)">KW:</span>'
            + '<input class="input cp-kw-primary" data-page="' + pageId + '" value="' + (kw.primary || "").replace(/"/g, "&quot;") + '" placeholder="primary keyword" style="font-size:10px;padding:2px 5px;flex:1;min-width:80px">'
            + '<input class="input cp-kw-secondary" data-page="' + pageId + '" value="' + (Array.isArray(kw.secondary) ? kw.secondary.join(", ") : "").replace(/"/g, "&quot;") + '" placeholder="secondary keywords" style="font-size:10px;padding:2px 5px;flex:2;min-width:100px">'
            + '</div>'
            // Row 4: outline headings
            + (outlineArr.length > 0 ? '<div style="margin-bottom:6px"><span style="font-size:10px;color:var(--ctp-overlay0)">Outline:</span>' + outlineHtml + '</div>' : '')
            // Row 5: paste your own content (collapsible)
            + '<details class="cp-paste-details" style="margin-bottom:6px">'
            + '<summary style="font-size:10px;color:var(--ctp-blue);cursor:pointer;user-select:none"><i class="fas fa-paste"></i> Paste your own content</summary>'
            + '<textarea class="input cp-paste-input" data-page="' + pageId + '" rows="4" style="font-size:11px;padding:6px;margin-top:6px;width:100%;resize:vertical;line-height:1.5;font-family:inherit" placeholder="Paste your real text here — about your company, services, team, etc. AI will format and structure it into the page instead of generating from scratch.">' + (state.pastedContent?.[pageId] || '') + '</textarea>'
            + '</details>'
            // Row 6: meta description
            + '<input class="input cp-meta-input" data-page="' + pageId + '" value="' + (plan.meta_description || "").replace(/"/g, "&quot;") + '" placeholder="Meta description (150-160 chars)" style="font-size:10px;padding:3px 6px;color:var(--ctp-subtext0);width:100%;margin-bottom:6px">'
            // Row 7: regen button
            + '<div style="text-align:right"><button class="btn-rewrite cp-regen-btn" data-page="' + pageId + '" style="font-size:10px;padding:3px 8px"><i class="fas fa-redo"></i> Regen Plan</button></div>';

        container.appendChild(card);
    });

    // Bind editable fields → update state
    container.querySelectorAll(".cp-include-cb").forEach(cb => {
        cb.onchange = () => {
            const pid = cb.dataset.page;
            if (cb.checked && !state.pages.includes(pid)) state.pages.push(pid);
            if (!cb.checked) state.pages = state.pages.filter(p => p !== pid);
            cb.closest(".cp-card").style.opacity = cb.checked ? "1" : ".5";
            saveState();
        };
    });
    container.querySelectorAll(".cp-title-input").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            state.contentPlan[pid].title = inp.value;
            saveState();
        };
    });
    container.querySelectorAll(".cp-brief-input").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            state.contentPlan[pid].content_brief = inp.value;
            saveState();
        };
    });
    container.querySelectorAll(".cp-kw-primary").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            if (!state.contentPlan[pid].keywords) state.contentPlan[pid].keywords = {};
            state.contentPlan[pid].keywords.primary = inp.value;
            saveState();
        };
    });
    container.querySelectorAll(".cp-kw-secondary").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            if (!state.contentPlan[pid].keywords) state.contentPlan[pid].keywords = {};
            state.contentPlan[pid].keywords.secondary = inp.value.split(",").map(s => s.trim()).filter(Boolean);
            saveState();
        };
    });
    container.querySelectorAll(".cp-outline-input").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            const idx = parseInt(inp.dataset.idx);
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            if (!state.contentPlan[pid].outline) state.contentPlan[pid].outline = [];
            if (state.contentPlan[pid].outline[idx]) state.contentPlan[pid].outline[idx].heading = inp.value;
            saveState();
        };
    });
    container.querySelectorAll(".cp-meta-input").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            state.contentPlan[pid].meta_description = inp.value;
            saveState();
        };
    });
    container.querySelectorAll(".cp-paste-input").forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            state.pastedContent[pid] = inp.value;
            saveState();
        };
    });
    // Regen plan per page
    container.querySelectorAll(".cp-regen-btn").forEach(btn => {
        btn.onclick = async () => {
            const pid = btn.dataset.page;
            btn.style.opacity = ".5";
            btn.style.pointerEvents = "none";
            const {provider, model} = getModelParts();
            try {
                const result = await api("/api/ai-theme-builder/wizard/content-plan", {
                    prompt: document.getElementById("wPrompt").value,
                    industry: getChipValue("wIndustry"),
                    language: document.getElementById("wLang").value,
                    pages: [pid],
                    tone: getChipValue("wTone"),
                    provider, model
                });
                if (result.ok && result.content_plan && result.content_plan[pid]) {
                    state.contentPlan[pid] = result.content_plan[pid];
                    renderContentPlanCards();
                    toast(ucfirst(pid) + " plan regenerated!", "success");
                } else {
                    toast("Regen failed: " + (result.error || "Unknown"), "error");
                }
            } catch(e) { toast("Error: " + e.message, "error"); }
            btn.style.opacity = "1";
            btn.style.pointerEvents = "";
        };
    });
}

// ═══════════════════════════════════════════
// STEP 3: CONTENT STUDIO
// ═══════════════════════════════════════════
function initStep2() {
    renderContentPagesList();
    if (state.pages.length > 0) selectContentPage(state.pages[0]);
    bindContentStudioHandlers();
    initImagePicker();
}

function renderContentPagesList() {
    const list = document.getElementById("contentPagesList");
    if (!list) return;
    list.innerHTML = "";
    state.pages.forEach(pageId => {
        const plan = state.contentPlan[pageId] || {};
        const content = state.pageContent[pageId];
        const hasContent = !!(content && content.html);
        const status = hasContent ? "ready" : "not-generated";
        const statusLabel = content ? "✅ Ready" : "📋 Planned";
        const kw = plan.keywords || {};
        const outlineArr = plan.outline || [];
        const outlineText = outlineArr.map(o => o.heading || '').filter(Boolean).join(' → ');

        const card = document.createElement("div");
        card.className = "content-page-card" + (state.currentPage === pageId ? " active" : "");
        card.dataset.page = pageId;
        card.innerHTML = ''
            + '<div class="cpc-page-icon"><i class="' + getPageIcon(pageId) + '"></i></div>'
            + '<div class="cpc-page-info" style="flex:1;min-width:0">'
            + '<input class="cpc-title-edit" data-page="' + pageId + '" value="' + (plan.title || ucfirst(pageId)).replace(/"/g, '&quot;') + '" style="font-size:13px;font-weight:600;background:transparent;border:1px solid transparent;border-radius:4px;padding:1px 4px;width:100%;color:var(--ctp-text);outline:none" onfocus="this.style.borderColor=\'var(--ctp-surface2)\'" onblur="this.style.borderColor=\'transparent\'">'
            + '<textarea class="cpc-brief-edit" data-page="' + pageId + '" rows="1" style="font-size:11px;color:var(--ctp-subtext0);background:transparent;border:1px solid transparent;border-radius:4px;padding:1px 4px;width:100%;resize:none;line-height:1.3;outline:none;overflow:hidden" onfocus="this.style.borderColor=\'var(--ctp-surface2)\'" onblur="this.style.borderColor=\'transparent\'">' + (plan.content_brief || 'Click generate to create content') + '</textarea>'
            + (outlineText ? '<div style="font-size:10px;color:var(--ctp-overlay0);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:0 4px" title="' + outlineText.replace(/"/g, '&quot;') + '">📝 ' + outlineText + '</div>' : '')
            + (kw.primary ? '<div style="font-size:10px;padding:0 4px"><span style="background:var(--ctp-blue);color:var(--ctp-crust);padding:1px 5px;border-radius:3px;font-weight:500">' + kw.primary + '</span></div>' : '')
            + '</div>'
            + '<div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;min-width:70px">'
            + '<span class="cpc-page-status ' + status + '" style="font-size:10px">' + statusLabel + '</span>'
            + '<span style="font-size:10px;color:var(--ctp-overlay0)">' + (plan.word_count_target || 500) + 'w target</span>'
            + '</div>'
            + '<div class="cpc-page-actions">'
            + '<button class="cpc-page-btn" data-action="generate" data-page="' + pageId + '" title="Generate content"><i class="fas fa-wand-magic-sparkles"></i></button>'
            + (hasContent ? '<button class="cpc-page-btn" data-action="regen" data-page="' + pageId + '" title="Regenerate content"><i class="fas fa-rotate"></i></button>' : '')
            + (hasContent ? '<button class="cpc-page-btn" data-action="seo" data-page="' + pageId + '" title="SEO check"><i class="fas fa-chart-line"></i></button>' : '')
            + '</div>';
        card.onclick = function(e) {
            if (e.target.closest('.cpc-page-btn') || e.target.closest('.cpc-title-edit') || e.target.closest('.cpc-brief-edit')) return;
            selectContentPage(pageId);
        };
        list.appendChild(card);
    });
    // Bind generate/regen/seo buttons
    list.querySelectorAll('.cpc-page-btn[data-action="generate"]').forEach(btn => {
        btn.onclick = () => generatePageContent(btn.dataset.page);
    });
    list.querySelectorAll('.cpc-page-btn[data-action="regen"]').forEach(btn => {
        btn.onclick = () => generatePageContent(btn.dataset.page); // same function, overwrites existing
    });
    list.querySelectorAll('.cpc-page-btn[data-action="seo"]').forEach(btn => {
        btn.onclick = () => runSeoCheck(btn.dataset.page);
    });
    // Bind editable title/brief → update state
    list.querySelectorAll('.cpc-title-edit').forEach(inp => {
        inp.onchange = () => {
            const pid = inp.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            state.contentPlan[pid].title = inp.value;
            saveState();
        };
    });
    list.querySelectorAll('.cpc-brief-edit').forEach(ta => {
        ta.onchange = () => {
            const pid = ta.dataset.page;
            if (!state.contentPlan[pid]) state.contentPlan[pid] = {};
            state.contentPlan[pid].content_brief = ta.value;
            saveState();
        };
    });
}

function selectContentPage(pageId) {
    state.currentPage = pageId;
    document.querySelectorAll('.content-page-card').forEach(c => c.classList.toggle('active', c.dataset.page === pageId));
    displayPageContent(pageId);
}

function displayPageContent(pageId) {
    const content = state.pageContent[pageId];
    const plan = state.contentPlan[pageId] || {};
    const titleEl = document.getElementById("contentPreviewTitle");
    if (titleEl) titleEl.textContent = plan.title || ucfirst(pageId);

    if (content && content.html) {
        const previewEl = document.getElementById("contentPreviewHtml");
        if (previewEl) previewEl.innerHTML = content.html;
        const editorEl = document.getElementById("contentHtmlEditor");
        if (editorEl) editorEl.value = content.html;
        const wcEl = document.getElementById("contentWordCount");
        if (wcEl) wcEl.textContent = (content.wordCount || 0) + " words";
        const seoEl = document.getElementById("contentSeoScore");
        if (seoEl) {
            if (content.seoScore !== null && content.seoScore !== undefined) {
                seoEl.textContent = "SEO: " + content.seoScore + "/100";
                seoEl.style.color = content.seoScore >= 80 ? 'var(--ctp-green)' : content.seoScore >= 60 ? 'var(--ctp-yellow)' : 'var(--ctp-red)';
            } else {
                seoEl.textContent = '';
            }
        }
    } else {
        const previewEl = document.getElementById("contentPreviewHtml");
        if (previewEl) previewEl.innerHTML = '<p style="color:#999;text-align:center;padding:60px 20px">Click the ✨ button to generate content for this page</p>';
        const editorEl = document.getElementById("contentHtmlEditor");
        if (editorEl) editorEl.value = '';
        const wcEl = document.getElementById("contentWordCount");
        if (wcEl) wcEl.textContent = '';
        const seoEl = document.getElementById("contentSeoScore");
        if (seoEl) seoEl.textContent = '';
    }
}

async function generatePageContent(pageId) {
    const plan = state.contentPlan[pageId] || {};
    const card = document.querySelector('.content-page-card[data-page="' + pageId + '"]');
    const statusEl = card ? card.querySelector('.cpc-page-status') : null;
    if (statusEl) { statusEl.className = 'cpc-page-status generating'; statusEl.textContent = '⏳ Generating...'; }

    const {provider, model} = getModelParts();
    try {
        const result = await api("/api/ai-theme-builder/wizard/generate-content", {
            page_type: pageId,
            title: plan.title || ucfirst(pageId),
            content_brief: plan.content_brief || '',
            outline: plan.outline || [],
            keywords: plan.keywords || {},
            tone: getChipValue("wTone") || 'professional',
            industry: getChipValue("wIndustry"),
            language: document.getElementById("wLang").value,
            business_info: state.businessInfo,
            provider, model
        });
        if (result.ok) {
            state.pageContent[pageId] = {
                html: result.content,
                wordCount: result.word_count || 0,
                seoScore: null,
                status: 'ready'
            };
            if (statusEl) { statusEl.className = 'cpc-page-status ready'; statusEl.textContent = '✅ Ready'; }
            if (state.currentPage === pageId) displayPageContent(pageId);
            toast(ucfirst(pageId) + " content generated!", "success");
        } else {
            if (statusEl) { statusEl.className = 'cpc-page-status not-generated'; statusEl.textContent = '❌ Error'; }
            toast("Error: " + (result.error || "Unknown"), "error");
        }
    } catch(e) {
        if (statusEl) { statusEl.className = 'cpc-page-status not-generated'; statusEl.textContent = '❌ Error'; }
        toast("Error: " + e.message, "error");
    }
}

function bindContentStudioHandlers() {
    // Rewrite buttons
    document.querySelectorAll('.btn-rewrite[data-mode]').forEach(function(btn) {
        btn.onclick = async function() {
            var mode = btn.dataset.mode;
            var pageId = state.currentPage;
            var content = state.pageContent[pageId];
            if (!content || !content.html) { toast("Generate content first!", "error"); return; }

            btn.style.opacity = '0.5'; btn.style.pointerEvents = 'none';
            var {provider, model} = getModelParts();
            try {
                var result = await api("/api/ai-theme-builder/wizard/rewrite-content", {
                    content: content.html,
                    mode: mode,
                    tone: document.getElementById("contentToneSelect").value,
                    keywords: (state.contentPlan[pageId] || {}).keywords || {},
                    provider: provider, model: model
                });
                if (result.ok) {
                    state.pageContent[pageId].html = result.content;
                    state.pageContent[pageId].wordCount = result.word_count || 0;
                    displayPageContent(pageId);
                    toast("Content rewritten (" + mode + ")", "success");
                } else {
                    toast("Rewrite error: " + (result.error || "Unknown"), "error");
                }
            } catch(e) {
                toast("Error: " + e.message, "error");
            }
            btn.style.opacity = '1'; btn.style.pointerEvents = '';
        };
    });

    // SEO Check — named function (used by toolbar button + per-card button)
    async function runSeoCheck(pageId) {
        if (!pageId) pageId = state.currentPage;
        var content = state.pageContent[pageId];
        var plan = state.contentPlan[pageId] || {};
        if (!content || !content.html) { toast("Generate content first!", "error"); return; }

        try {
            var result = await api("/api/ai-theme-builder/wizard/seo-check", {
                content: content.html,
                title: plan.title || ucfirst(pageId),
                meta_description: plan.meta_description || '',
                keywords: plan.keywords || {},
                url_slug: pageId
            });
            if (result.ok) {
                state.pageContent[pageId].seoScore = result.score;
                displayPageContent(pageId);
                renderContentPagesList(); // refresh card badges
                toast("SEO Score: " + result.score + "/100", result.score >= 70 ? "success" : "info");
            }
        } catch(e) {
            toast("SEO check error: " + e.message, "error");
        }
    }

    // SEO Check button (toolbar)
    var seoCheckBtn = document.getElementById("btnSeoCheck");
    if (seoCheckBtn) {
        seoCheckBtn.onclick = function() { runSeoCheck(state.currentPage); };
    }

    // Content tab switching (Preview / Edit)
    document.querySelectorAll('.content-tab').forEach(function(tab) {
        tab.onclick = function() {
            document.querySelectorAll('.content-tab').forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            var view = tab.dataset.view;
            document.getElementById("contentPreviewView").style.display = (view === 'preview') ? 'block' : 'none';
            document.getElementById("contentEditView").style.display = (view === 'edit') ? 'block' : 'none';
        };
    });

    // Save edited HTML back
    var htmlEditor = document.getElementById("contentHtmlEditor");
    if (htmlEditor) {
        htmlEditor.onblur = function() {
            if (state.currentPage && state.pageContent[state.currentPage]) {
                state.pageContent[state.currentPage].html = htmlEditor.value;
                document.getElementById("contentPreviewHtml").innerHTML = htmlEditor.value;
            }
        };
    }

    // Generate All Content
    var genAllBtn = document.getElementById("btnGenAllContent");
    if (genAllBtn) {
        genAllBtn.onclick = async function() {
            genAllBtn.classList.add("loading");
            for (var i = 0; i < state.pages.length; i++) {
                var pid = state.pages[i];
                if (state.pageContent[pid]) continue;
                selectContentPage(pid);
                await generatePageContent(pid);
            }
            genAllBtn.classList.remove("loading");
            toast("All content generated!", "success");
        };
    }
}

// ═══════════════════════════════════════════
// IMAGE PICKER (Pexels — Step 2)
// ═══════════════════════════════════════════
const _ip = { page: 1, query: '', loading: false, totalResults: 0, images: [] };

function initImagePicker() {
    const grid = document.getElementById("ipGrid");
    if (!grid) return;

    // Render already-selected images from restored state
    if (state.selectedImages.length > 0) {
        renderImagePickerFromState();
    }

    // Auto-search based on industry when entering Step 2
    const industry = state.brief?.industry || document.querySelector('[data-group="wIndustry"] .chip.active')?.dataset?.value || 'business';
    if (_ip.images.length === 0) {
        _ip.query = industry.replace(/([A-Z])/g, ' $1').replace(/-/g, ' ').trim();
        document.getElementById("ipSearchInput").value = _ip.query;
        searchPexelsImages(_ip.query, 1, false);
    }

    // Search button
    document.getElementById("ipSearchBtn").onclick = () => {
        const q = document.getElementById("ipSearchInput").value.trim();
        if (!q) return;
        _ip.query = q;
        searchPexelsImages(q, 1, false);
    };

    // Enter key on search input
    document.getElementById("ipSearchInput").onkeydown = (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            document.getElementById("ipSearchBtn").click();
        }
    };

    // Load more
    document.getElementById("ipLoadMoreBtn").onclick = () => {
        if (_ip.query) searchPexelsImages(_ip.query, _ip.page + 1, true);
    };
}

async function searchPexelsImages(query, page, append) {
    if (_ip.loading) return;
    _ip.loading = true;
    const grid = document.getElementById("ipGrid");
    const loadMore = document.getElementById("ipLoadMore");
    const searchBtn = document.getElementById("ipSearchBtn");

    if (!append) {
        grid.innerHTML = '<div class="ip-loading"><span class="spinner"></span> Searching Pexels…</div>';
        _ip.images = [];
    } else {
        searchBtn.disabled = true;
    }

    try {
        const result = await api("/api/ai-theme-builder/wizard/search-images", {
            query: query,
            page: page,
            per_page: 15
        });

        if (!result.ok) {
            if (!append) grid.innerHTML = '<div class="ip-empty"><i class="fas fa-exclamation-triangle"></i> ' + (result.error || 'Search failed') + '</div>';
            return;
        }

        _ip.page = page;
        _ip.totalResults = result.total || 0;

        if (result.images.length === 0 && !append) {
            grid.innerHTML = '<div class="ip-empty"><i class="fas fa-image"></i> No images found. Try a different search.</div>';
            loadMore.style.display = "none";
            return;
        }

        // Merge new images, avoiding duplicates
        const existingIds = new Set(_ip.images.map(i => i.id));
        result.images.forEach(img => {
            if (!existingIds.has(img.id)) {
                _ip.images.push(img);
            }
        });

        renderImagePickerGrid();

        // Show/hide load more
        const totalLoaded = _ip.images.length;
        loadMore.style.display = totalLoaded < _ip.totalResults ? "flex" : "none";

    } catch(err) {
        if (!append) grid.innerHTML = '<div class="ip-empty"><i class="fas fa-exclamation-triangle"></i> Search failed</div>';
    } finally {
        _ip.loading = false;
        searchBtn.disabled = false;
    }
}

function renderImagePickerGrid() {
    const grid = document.getElementById("ipGrid");
    grid.innerHTML = '';

    // Build set of selected src URLs for fast lookup
    const selectedSrcs = new Set(state.selectedImages.map(i => i.src));

    _ip.images.forEach(img => {
        const isSelected = selectedSrcs.has(img.src);
        const div = document.createElement("div");
        div.className = "image-picker-item" + (isSelected ? " selected" : "");
        div.dataset.src = img.src;
        div.innerHTML = '<img src="' + (img.thumb || img.src) + '" alt="' + (img.alt || '').replace(/"/g, '&quot;') + '" loading="lazy">'
            + '<div class="ip-check"><i class="fas fa-check"></i></div>'
            + '<div class="ip-photographer">📷 ' + (img.photographer || 'Unknown').replace(/</g, '&lt;') + '</div>';
        div.onclick = () => toggleImageSelection(img, div);
        grid.appendChild(div);
    });

    updateImageCount();
}

function renderImagePickerFromState() {
    // When restoring from saved state, we need to rebuild _ip.images from selected + any previous search
    // For now just update the count display; the grid will be populated when search runs
    updateImageCount();
}

function toggleImageSelection(img, el) {
    const idx = state.selectedImages.findIndex(i => i.src === img.src);
    if (idx >= 0) {
        // Deselect
        state.selectedImages.splice(idx, 1);
        el.classList.remove("selected");
    } else {
        // Select
        state.selectedImages.push({
            src: img.src,
            alt: img.alt || '',
            photographer: img.photographer || ''
        });
        el.classList.add("selected");
    }
    updateImageCount();
    saveState();
}

function updateImageCount() {
    const countEl = document.getElementById("ipCount");
    if (!countEl) return;
    const n = state.selectedImages.length;
    countEl.textContent = n;
    countEl.className = "count " + (n >= 6 ? "ok" : "warn");
}

// Step 2 (Content) navigation
document.getElementById("btnStep2Back").onclick = () => goToStep(1);
document.getElementById("btnStep2Next").onclick = function() {
    goToStep(3); // goToStep auto-calls initDesignStep()
};

// ═══════════════════════════════════════════
// STEP 3: DESIGN (Structure, Colors, Fonts, Business Info)
// ═══════════════════════════════════════════
function initStep3() {
    // Build brief review panel (colors, fonts, sections)
    if (state.brief) showBriefReview(state.brief);

    // Pages checklist
    const list = document.getElementById("pagesList");
    if (!list) return;
    list.innerHTML = "";
    // Smart page defaults: if brief has sections like 'services', 'portfolio', 'pricing', auto-enable matching pages
    const briefSections = (state.brief?.homepage_sections || []).map(s => s.id);
    const sectionToPage = {services:"services", portfolio:"portfolio", pricing:"pricing", team:"team", faq:"faq", testimonials:"testimonials", gallery:"gallery"};
    const autoPages = new Set();
    briefSections.forEach(sid => { if (sectionToPage[sid]) autoPages.add(sectionToPage[sid]); });

    PAGE_TYPES.forEach(p => {
        const checked = p.required || p.defaultOn || autoPages.has(p.id) || state.pages.includes(p.id);
        const div = document.createElement("div");
        div.className = "check-item" + (checked ? " checked" : "") + (p.required ? " locked" : "");
        div.innerHTML = `
            <input type="checkbox" ${checked ? "checked" : ""} ${p.required ? "disabled" : ""} data-page="${p.id}">
            <div class="check-box"><i class="fas fa-check"></i></div>
            <div class="check-icon"><i class="${p.icon}"></i></div>
            <span class="check-label">${p.name}</span>
        `;
        if (!p.required) {
            div.onclick = () => {
                const cb = div.querySelector("input");
                cb.checked = !cb.checked;
                div.classList.toggle("checked", cb.checked);
            };
        }
        list.appendChild(div);
    });

    // Homepage sections moved to Step 3 Design (designSectionsList)
}

// Dropzone
const dropzone = document.getElementById("dropzone");
const fileInput = document.getElementById("fileInput");
dropzone.onclick = () => fileInput.click();
dropzone.ondragover = e => { e.preventDefault(); dropzone.classList.add("dragover"); };
dropzone.ondragleave = () => dropzone.classList.remove("dragover");
dropzone.ondrop = e => { e.preventDefault(); dropzone.classList.remove("dragover"); handleFiles(e.dataTransfer.files); };
fileInput.onchange = e => handleFiles(e.target.files);

async function handleFiles(files) {
    if (!files.length) return;
    const fd = new FormData();
    for (const f of files) fd.append("images[]", f);
    fd.append("_csrf_token", CSRF);
    try {
        const r = await fetch("/api/ai-theme-builder/wizard/upload-images", {method:"POST", body:fd});
        const data = await r.json();
        if (data.ok) {
            state.userImages.push(...(data.images || []));
            renderImageGrid();
            toast(data.images.length + " image(s) uploaded", "success");
        } else {
            toast("Upload failed: " + (data.error || "Unknown"), "error");
        }
    } catch(e) {
        toast("Upload error: " + e.message, "error");
    }
}

function renderImageGrid() {
    const grid = document.getElementById("imgGrid");
    grid.innerHTML = "";
    state.userImages.forEach((img, idx) => {
        const el = document.createElement("img");
        el.className = "img-thumb";
        el.src = img.path || img.url || "";
        el.alt = img.original_name || "Image " + (idx+1);
        el.title = img.original_name || "";
        grid.appendChild(el);
    });
}

// ═══════════════════════════════════════════
// STEP 3→4: BUILD THEME (SSE streaming)
// ═══════════════════════════════════════════

// Design step: populate brief review panel with editable colors/fonts
function initDesignStep() {
    initPatternGrids();

    // Show only pattern grids for selected homepage sections
    const selectedIds = (state.brief?.homepage_sections || []).map(s => s.id);
    document.querySelectorAll('.section-pattern-block').forEach(block => {
        const patterns = (block.dataset.sectionPattern || '').split(',');
        const visible = patterns.some(p => selectedIds.includes(p.trim()));
        block.style.display = visible ? '' : 'none';
    });

    // Render sub-page layout style cards
    renderPageLayoutCards();

    const panel = document.getElementById("designBriefPanel");
    if (!panel || !state.brief) return;

    const b = state.brief;
    const colors = b.colors || {};
    const typo = b.typography || {};

    let html = '<div class="brief-card"><h3>' + (b.name || 'Theme') + '</h3>';

    // Color palette (editable)
    html += '<div style="margin-bottom:16px"><div class="section-head"><span class="section-title">Colors</span><button class="brief-regen-btn" id="btnDesignRegenColors"><i class="fas fa-redo"></i> Randomize</button></div>';
    html += '<div class="palette">';
    const colorKeys = ["primary","secondary","accent","background","surface","text"];
    colorKeys.forEach(k => {
        const val = colors[k] || "#888888";
        html += '<div style="text-align:center"><div class="palette-swatch"><input type="color" value="' + val + '" data-color-key="' + k + '"></div><div class="palette-label">' + k + '</div><div class="palette-hex">' + val + '</div></div>';
    });
    html += '</div>';

    // Preset palettes
    html += '<details style="margin-top:8px"><summary style="font-size:11px;color:var(--ctp-overlay0);cursor:pointer">Preset palettes</summary>';
    html += '<div class="palette-presets" id="designPalettePresets"></div></details>';
    html += '</div>';

    // Fonts (dropdowns + regen)
    html += '<div class="section-head"><span class="section-title">Typography</span><button class="brief-regen-btn" id="btnDesignRegenFonts"><i class="fas fa-redo"></i> Randomize</button></div>';
    html += '<div class="row-2" style="margin-bottom:16px">';
    html += '<div class="field"><label class="label">Heading Font</label><select class="input brief-font-select" id="designHeadingFont"></select><div id="designHeadingPreview" style="margin-top:6px;font-size:20px;font-weight:700;color:var(--ctp-text)">The quick brown fox</div></div>';
    html += '<div class="field"><label class="label">Body Font</label><select class="input brief-font-select" id="designBodyFont"></select><div id="designBodyPreview" style="margin-top:6px;font-size:14px;color:var(--ctp-subtext0)">The quick brown fox jumps over the lazy dog</div></div>';
    html += '</div>';

    // Populate font selects after HTML render
    setTimeout(() => {
        const FONT_LIST = ["Inter","Roboto","Open Sans","Lato","Montserrat","Oswald","Raleway","Poppins","Nunito","Merriweather","Playfair Display","Source Sans Pro","PT Sans","Ubuntu","Noto Sans","Work Sans","DM Sans","Mulish","Quicksand","Barlow","Rubik","Fira Sans","Karla","Libre Baskerville","Bitter","Crimson Text","EB Garamond","Cormorant Garamond","Josefin Sans","Space Grotesk","Plus Jakarta Sans","Outfit","Sora","Manrope","Jost","Lexend","Albert Sans","Bricolage Grotesque","Geist","Figtree","Instrument Sans","Red Hat Display","Urbanist","Onest","General Sans","Satoshi","Cabinet Grotesk","Clash Display","Switzer","Zodiak","Gambetta","Boska","Erode","Synonym","Chillax","Ranade","Archivo","Encode Sans","Be Vietnam Pro","Commissioner","Hanken Grotesk","Public Sans","Overpass","IBM Plex Sans","Noto Serif","Libre Franklin","Lora","Spectral","Source Serif Pro","Cormorant","Fraunces","Newsreader","Petrona","Brygada 1918","Vollkorn","Alegreya","Cardo","Gentium Book Plus","Old Standard TT","Inconsolata","Cabin","Exo 2","Titillium Web","Asap","Hind","Domine","Arimo","Signika","Catamaran","Maven Pro","Oxygen","Nunito Sans","PT Serif","Antic Slab","Zilla Slab","IBM Plex Serif","Tenor Sans","Prata","Cormorant Infant","Bodoni Moda","DM Serif Display","DM Serif Text","Young Serif","Schibsted Grotesk","Darker Grotesque","Wix Madefor Display","Atkinson Hyperlegible"];
        ["designHeadingFont","designBodyFont"].forEach(selId => {
            const sel = document.getElementById(selId);
            if (!sel) return;
            const currentVal = selId === "designHeadingFont" ? (typo.headingFont || "Inter") : (typo.fontFamily || "Inter");
            sel.innerHTML = FONT_LIST.map(f => '<option value="' + f + '"' + (f === currentVal ? ' selected' : '') + '>' + f + '</option>').join("");
            sel.style.fontFamily = currentVal;
            sel.onchange = function() {
                this.style.fontFamily = this.value;
                const previewId = selId === "designHeadingFont" ? "designHeadingPreview" : "designBodyPreview";
                const preview = document.getElementById(previewId);
                if (preview) { preview.style.fontFamily = this.value + ", sans-serif"; }
                // Load font
                const link = document.createElement("link");
                link.rel = "stylesheet";
                link.href = "https://fonts.googleapis.com/css2?family=" + encodeURIComponent(this.value) + ":wght@400;700&display=swap";
                document.head.appendChild(link);
            };
            // Load initial font
            const link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = "https://fonts.googleapis.com/css2?family=" + encodeURIComponent(currentVal) + ":wght@400;700&display=swap";
            document.head.appendChild(link);
            const previewId = selId === "designHeadingFont" ? "designHeadingPreview" : "designBodyPreview";
            const preview = document.getElementById(previewId);
            if (preview) preview.style.fontFamily = currentVal + ", sans-serif";
        });
    }, 50);

    // Sections
    html += '<div class="section-head"><span class="section-title">Homepage Sections</span><button class="brief-regen-btn" id="btnDesignRegenSections"><i class="fas fa-redo"></i> AI Suggest</button></div>';
    html += '<div class="check-list" id="designSectionsList"></div>';

    html += '</div>';
    panel.innerHTML = html;

    // Populate sections
    const secList = document.getElementById("designSectionsList");
    const briefSectionIds = (b.homepage_sections || []).map(s => s.id);
    const allSecs = [...SECTIONS];
    // Add AI-specific sections not in SECTIONS
    (b.homepage_sections || []).forEach(bs => {
        if (!allSecs.find(s => s.id === bs.id)) {
            allSecs.push({id: bs.id, name: bs.label || bs.id, icon: "fas fa-puzzle-piece", required: false});
        }
    });
    allSecs.forEach(s => {
        const checked = s.required || briefSectionIds.includes(s.id);
        const div = document.createElement("div");
        div.className = "check-item" + (checked ? " checked" : "") + (s.required ? " locked" : "");
        div.draggable = true;
        div.dataset.sectionId = s.id;
        div.innerHTML = '<span style="cursor:grab;color:var(--ctp-overlay0);font-size:12px;margin-right:4px" class="drag-handle">☰</span>' +
            '<input type="checkbox" ' + (checked ? "checked" : "") + ' ' + (s.required ? "disabled" : "") + ' data-section="' + s.id + '">' +
            '<div class="check-box"><i class="fas fa-check"></i></div>' +
            '<div class="check-icon"><i class="' + (s.icon || "fas fa-puzzle-piece") + '"></i></div>' +
            '<span class="check-label">' + (s.name || s.id) + '</span>';
        if (!s.required) {
            div.onclick = (e) => { if (e.target.closest('.drag-handle')) return; const cb = div.querySelector("input"); cb.checked = !cb.checked; div.classList.toggle("checked", cb.checked); };
        }
        // Drag & drop
        div.ondragstart = (e) => { e.dataTransfer.setData("text/plain", s.id); div.style.opacity = "0.4"; };
        div.ondragend = () => { div.style.opacity = "1"; };
        div.ondragover = (e) => { e.preventDefault(); div.style.borderTop = "2px solid var(--ctp-blue)"; };
        div.ondragleave = () => { div.style.borderTop = ""; };
        div.ondrop = (e) => {
            e.preventDefault(); div.style.borderTop = "";
            const draggedId = e.dataTransfer.getData("text/plain");
            const draggedEl = secList.querySelector('[data-section-id="' + draggedId + '"]');
            if (draggedEl && draggedEl !== div) { secList.insertBefore(draggedEl, div); }
        };
        secList.appendChild(div);
    });

    // Color change listener + contrast check
    function updateContrastBadge() {
        const bg = state.brief?.colors?.background || "#ffffff";
        const txt = state.brief?.colors?.text || "#000000";
        const ratio = getContrastRatio(bg, txt);
        const badge = document.getElementById("designContrastBadge");
        if (badge) {
            const level = ratio >= 7 ? "AAA" : ratio >= 4.5 ? "AA" : "Fail";
            const color = ratio >= 7 ? "var(--ctp-green)" : ratio >= 4.5 ? "var(--ctp-yellow)" : "var(--ctp-red)";
            badge.innerHTML = '<span style="color:' + color + ';font-weight:600">' + (level === "Fail" ? "⚠️" : "✅") + ' ' + level + '</span> <span style="color:var(--ctp-overlay0)">' + ratio.toFixed(1) + ':1</span>';
        }
    }
    panel.querySelectorAll("input[data-color-key]").forEach(inp => {
        inp.oninput = () => {
            state.brief.colors[inp.dataset.colorKey] = inp.value;
            inp.closest("div").querySelector(".palette-hex").textContent = inp.value;
            updateContrastBadge();
        };
    });
    // Add contrast badge after palette
    const paletteDiv = panel.querySelector(".palette");
    if (paletteDiv) {
        const badgeEl = document.createElement("div");
        badgeEl.id = "designContrastBadge";
        badgeEl.style.cssText = "margin-top:8px;font-size:12px;display:flex;align-items:center;gap:8px";
        badgeEl.innerHTML = "Contrast: checking...";
        paletteDiv.parentElement.appendChild(badgeEl);
        updateContrastBadge();
    }

    // Header/footer layout defaults are already set in Design step HTML

    // Populate preset palettes
    const presetsDiv = document.getElementById("designPalettePresets");
    if (presetsDiv) {
        presetsDiv.innerHTML = "";
        COLOR_PALETTES.forEach(p => {
            const div = document.createElement("div");
            div.className = "palette-preset";
            div.innerHTML = Object.values(p.colors).map(c => '<div class="palette-preset-color" style="background:' + c + '"></div>').join("") +
                '<div class="palette-preset-name">' + p.name + '</div>';
            div.onclick = () => {
                Object.entries(p.colors).forEach(([k, v]) => {
                    state.brief.colors[k] = v;
                    const inp = panel.querySelector('input[data-color-key="' + k + '"]');
                    if (inp) { inp.value = v; inp.closest("div").querySelector(".palette-hex").textContent = v; }
                });
                presetsDiv.querySelectorAll(".palette-preset").forEach(x => x.classList.remove("selected"));
                div.classList.add("selected");
            };
            presetsDiv.appendChild(div);
        });
    }
}

// Step 3 Design — Regen buttons
document.addEventListener("click", async function(e) {
    // Regen Colors in Design step
    if (e.target.closest("#btnDesignRegenColors")) {
        const btn = e.target.closest("#btnDesignRegenColors");
        btn.style.opacity = ".5"; btn.style.pointerEvents = "none";
        const {provider, model} = getModelParts();
        try {
            const r = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value,
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"),
                mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (r.ok && r.brief?.colors) {
                state.brief.colors = r.brief.colors;
                initDesignStep();
                toast("Colors randomized!", "success");
            }
        } catch(err) { toast("Error: " + err.message, "error"); }
        btn.style.opacity = "1"; btn.style.pointerEvents = "";
    }
    // Regen Fonts in Design step
    if (e.target.closest("#btnDesignRegenFonts")) {
        const btn = e.target.closest("#btnDesignRegenFonts");
        btn.style.opacity = ".5"; btn.style.pointerEvents = "none";
        const {provider, model} = getModelParts();
        try {
            const r = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value,
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"),
                mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (r.ok && r.brief?.typography) {
                state.brief.typography = r.brief.typography;
                initDesignStep();
                toast("Fonts randomized!", "success");
            }
        } catch(err) { toast("Error: " + err.message, "error"); }
        btn.style.opacity = "1"; btn.style.pointerEvents = "";
    }
    // Regen Sections in Design step
    if (e.target.closest("#btnDesignRegenSections")) {
        const btn = e.target.closest("#btnDesignRegenSections");
        btn.style.opacity = ".5"; btn.style.pointerEvents = "none";
        const {provider, model} = getModelParts();
        try {
            const r = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value,
                industry: getChipValue("wIndustry"), style: getChipValue("wStyle"),
                mood: getChipValue("wMood"), tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value, provider, model
            });
            if (r.ok && r.brief?.homepage_sections) {
                state.brief.homepage_sections = r.brief.homepage_sections;
                initDesignStep();
                toast("Sections re-suggested!", "success");
            }
        } catch(err) { toast("Error: " + err.message, "error"); }
        btn.style.opacity = "1"; btn.style.pointerEvents = "";
    }
});

// Step 3 (Design) navigation
document.getElementById("btnStep3Back").onclick = () => goToStep(2);
document.getElementById("btnBuildTheme").onclick = async function() {
    const btn = this;
    btn.classList.add("loading");
    btn.disabled = true;

    // Collect from Step 1 (pages, business info) and Step 3 (design settings)
    state.pages = [...document.querySelectorAll("#pagesList input:checked")].map(cb => cb.dataset.page);
    state.homeSections = [...document.querySelectorAll("#designSectionsList input:checked")].map(cb => cb.dataset.section);
    collectBusinessInfo(); // Collects all business profile fields, repeaters, hours
    state.headerSettings = {
        layout: document.getElementById("designHeaderLayout").value,
        behavior: "sticky",
        ctaText: document.getElementById("bizName")?.value ? "Contact Us" : "",
        ctaLink: "#contact",
        showPhone: true,
        showSearch: false,
        showSocial: false,
        topBar: false,
    };
    state.heroSettings = {
        layout: document.getElementById("designHeroLayout").value,
    };
    state.footerSettings = {
        layout: document.getElementById("designFooterLayout").value,
        newsletter: true,
        social: true,
        contact: true,
        map: false,
        hours: false,
    };
    state.sidebarEnabled = document.getElementById("designSidebar")?.checked || false;

    // Update brief fonts from design step
    if (state.brief) {
        const hf = document.getElementById("designHeadingFont")?.value;
        const bf = document.getElementById("designBodyFont")?.value;
        if (hf) state.brief.typography = {...(state.brief.typography || {}), headingFont: hf};
        if (bf) state.brief.typography = {...(state.brief.typography || {}), fontFamily: bf};
    }

    // Inject homepage_sections into brief
    if (state.brief) {
        const sectionLookup = {};
        ALL_SECTIONS.forEach(s => sectionLookup[s.id] = s);
        SECTIONS.forEach(s => sectionLookup[s.id] = s);
        state.brief.homepage_sections = state.homeSections.map(id => {
            const info = sectionLookup[id];
            return info ? {id, label: info.name || info.label || id, icon: info.icon || ''} : {id, label: id};
        });
    }

    goToStep(4);

    // Progress indicators — 3 steps (brief already done in Step 1)
    const prog = document.getElementById("layoutProgress");
    const steps = ["HTML Structure", "CSS Stylesheet", "Assembly"];
    prog.innerHTML = steps.map((s, i) =>
        '<div class="progress-item" id="lp' + (i+1) + '"><i class="fas fa-circle"></i> ' + s + '</div>'
    ).join("");

    const {provider, model} = getDesignModelParts();
    const buildCreativity = getDesignCreativity();
    try {
        const bodyData = {
            prompt: document.getElementById("wPrompt").value,
            industry: getChipValue("wIndustry"),
            style: getChipValue("wStyle"),
            mood: getChipValue("wMood"),
            tone: getChipValue("wTone"),
            language: document.getElementById("wLang").value,
            creativity: buildCreativity,
            brief: state.brief,
            pages: state.pages,
            business_info: state.businessInfo,
            user_images: state.userImages,
            selected_images: state.selectedImages,
            header_settings: state.headerSettings,
            footer_settings: state.footerSettings,
            hero_settings: state.heroSettings,
            sidebar_enabled: state.sidebarEnabled,
            provider, model
        };

        // Use wizard/layout-stream (SSE) — passes brief from Step 1
        const resp = await fetch("/api/ai-theme-builder/wizard/layout-stream", {
            method: "POST",
            headers: {"Content-Type":"application/json", "X-CSRF-TOKEN":CSRF},
            body: JSON.stringify(bodyData)
        });

        if (!resp.ok) {
            // Fallback: try non-stream endpoint
            const fallback = await api("/api/ai-theme-builder/wizard/layout", bodyData);
            if (fallback.ok) {
                state.slug = fallback.slug;
                if (fallback.seeded_pages) state.seededPages = fallback.seeded_pages;
                steps.forEach((_, i) => updateProgress(i+1, "done"));
            } else {
                throw new Error(fallback.error || "Generation failed");
            }
        } else {
            const reader = resp.body.getReader();
            const decoder = new TextDecoder();
            let buffer = "";

            let eventType = "";
            while (true) {
                const {done, value} = await reader.read();
                if (done) break;
                buffer += decoder.decode(value, {stream:true});
                const lines = buffer.split("\n");
                buffer = lines.pop();
                for (const line of lines) {
                    if (line.startsWith("event: ")) { eventType = line.slice(7).trim(); continue; }
                    if (!line.startsWith("data: ")) { if (line === "") eventType = ""; continue; }
                    try {
                        const evt = JSON.parse(line.slice(6));
                        if (eventType === "complete") {
                            if (evt.slug) state.slug = evt.slug;
                            if (evt.seeded_pages) state.seededPages = evt.seeded_pages;
                            steps.forEach((_, i) => updateProgress(i+1, "done"));
                        } else if (eventType === "error") {
                            if (evt.error_info) {
                                showAIError(evt.error_info, evt.error);
                            } else {
                                toast("Error: " + (evt.error || "Generation failed"), "error");
                            }
                        } else {
                            if (evt.step) updateProgress(evt.step, evt.status);
                            if (evt.slug) state.slug = evt.slug;
                            if (evt.error) toast("Error: " + evt.error, "error");
                        }
                    } catch(e) { /* ignore parse errors */ }
                    eventType = "";
                }
            }
        }

        if (state.slug) {
            // Load preview immediately after homepage is built (before sub-pages)
            const previewUrl = "/admin/ai-theme-builder/preview?theme=" + state.slug;
            document.getElementById("previewUrl4").textContent = previewUrl;
            document.getElementById("previewLink4").href = previewUrl;
            document.getElementById("previewIframe4").src = previewUrl;

            toast("Homepage generated! Now generating sub-pages...", "success");

            // ── Generate sub-pages for ALL seeded pages (from server) ──
            // Use seededPages from server (exact pages created in DB) — fallback to state.pages
            const seededPages = state.seededPages || state.pages.filter(p => p !== "home" && p !== "blog" && p !== "gallery");
            const subPages = [...new Set(seededPages)];
            if (subPages.length > 0) {
                // Add sub-page progress items
                const prog2 = document.getElementById("layoutProgress");
                subPages.forEach((p, i) => {
                    const idx = 4 + i; // after the 3 layout steps
                    prog2.innerHTML += '<div class="progress-item" id="lp' + idx + '"><i class="fas fa-circle"></i> ' + ucfirst(p) + ' page</div>';
                });

                // Generate each sub-page sequentially
                for (let i = 0; i < subPages.length; i++) {
                    const pageType = subPages[i];
                    const idx = 4 + i;
                    updateProgress(idx, "running");
                    try {
                        const pageContent = state.pageContent[pageType];
                        const pagePlan = state.contentPlan[pageType];
                        const pageResult = await api("/api/ai-theme-builder/wizard/page", {
                            prompt: document.getElementById("wPrompt").value,
                            industry: getChipValue("wIndustry"),
                            style: getChipValue("wStyle"),
                            mood: getChipValue("wMood"),
                            language: document.getElementById("wLang").value,
                            creativity: buildCreativity,
                            brief: state.brief,
                            slug: state.slug,
                            page_type: pageType,
                            layout_style: state.pageLayouts?.[pageType] || "auto",
                            business_info: state.businessInfo,
                            user_images: state.userImages,
                            page_content: pageContent ? pageContent.html : null,
                            pasted_content: state.pastedContent?.[pageType] || null,
                            content_plan: pagePlan || null,
                            provider, model
                        });
                        if (pageResult.ok) {
                            state.generatedPages[pageType] = pageResult;
                            updateProgress(idx, "done");
                        } else {
                            updateProgress(idx, "error");
                            if (pageResult.error_info) {
                                showAIError(pageResult.error_info, pageResult.error);
                            } else {
                                toast(ucfirst(pageType) + ": " + (pageResult.error || "Failed"), "error");
                            }
                        }
                    } catch(e) {
                        updateProgress(idx, "error");
                        toast(ucfirst(pageType) + " error: " + e.message, "error");
                    }
                }
                const generated = Object.keys(state.generatedPages).length;
                toast(generated + " sub-page(s) generated!", "success");
            }

            // Refresh preview with sub-pages included
            document.getElementById("previewIframe4").contentWindow.location.reload();
            document.getElementById("layoutActions").classList.remove("hidden");
            toast("Theme complete!", "success");
            setTimeout(() => {
                document.getElementById("layoutActions").scrollIntoView({behavior:"smooth", block:"start"});
            }, 200);
        } else {
            toast("Generation failed — no theme was created. Check AI model settings and try again.", "error");
        }
    } catch(e) {
        if (e.errorInfo) {
            showAIError(e.errorInfo, e.message);
        } else {
            toast("Error: " + e.message, "error");
        }
    }
    btn.classList.remove("loading");
    btn.disabled = false;
};

function updateProgress(step, status) {
    const el = document.getElementById("lp" + step);
    if (!el) return;
    el.className = "progress-item " + status;
    const icon = el.querySelector("i");
    if (status === "done") icon.className = "fas fa-check-circle";
    else if (status === "running") icon.className = "fas fa-spinner fa-spin";
    else if (status === "error") icon.className = "fas fa-times-circle";
    else icon.className = "fas fa-circle";
}

// Build step — populate regen model dropdown from Design step model list
(function populateRegenModel() {
    const designSrc = document.getElementById("designModel");
    const fallbackSrc = document.getElementById("wModel");
    const src = (designSrc && designSrc.options.length > 0) ? designSrc : fallbackSrc;
    const dst = document.getElementById("regenModel");
    if (!src || !dst) return;
    dst.innerHTML = src.innerHTML;
    dst.value = src.value; // default to same model as Design step
})();

// Regenerate handler — optionally re-generates brief for fresh design
document.getElementById("btnRegenLayout").onclick = async () => {
    const freshBrief = document.getElementById("regenNewBrief").checked;
    const regenCreativity = document.getElementById("regenCreativity").value;
    const regenModelVal = document.getElementById("regenModel").value;

    // Sync Design step selectors with regen choices (Design step is the source of truth for Build)
    const designCreativityEl = document.getElementById("designCreativity");
    if (designCreativityEl) designCreativityEl.value = regenCreativity;
    const designModelEl = document.getElementById("designModel");
    if (designModelEl && regenModelVal) designModelEl.value = regenModelVal;

    // Also sync Step 1 for consistency
    document.getElementById("wCreativity").value = regenCreativity;
    const wModel = document.getElementById("wModel");
    if (wModel && regenModelVal) wModel.value = regenModelVal;

    if (freshBrief) {
        // Re-generate brief first (new colors, fonts, sections)
        toast("Generating fresh design brief...", "info");
        try {
            const {provider, model} = getDesignModelParts();
            const briefResult = await api("/api/ai-theme-builder/wizard/brief", {
                prompt: document.getElementById("wPrompt").value,
                industry: getChipValue("wIndustry"),
                style: getChipValue("wStyle"),
                mood: getChipValue("wMood"),
                tone: getChipValue("wTone"),
                language: document.getElementById("wLang").value,
                creativity: regenCreativity,
                provider, model
            });
            if (briefResult.brief) {
                state.brief = briefResult.brief;
                // Update brief review panel
                if (typeof showBriefReview === "function") showBriefReview(state.brief);
                if (typeof initStep3 === "function") initStep3();
                toast("New brief: " + (state.brief.name || "Unnamed"), "success");
            }
        } catch(e) {
            toast("Brief error: " + e.message + " — using existing brief", "error");
        }
    }

    // Now trigger build with (possibly new) brief
    document.getElementById("layoutActions").classList.add("hidden");
    document.getElementById("btnBuildTheme").click();
};

// ═══════════════════════════════════════════
// STEP 4: REVIEW & PUBLISH
// ═══════════════════════════════════════════
function initStep5() {
    const cl = document.getElementById("finalChecklist");
    cl.innerHTML = "";

    const hasTheme = !!state.slug;
    const contentCount = Object.keys(state.pageContent).length;
    const items = [
        {ok: hasTheme, label: "Homepage generated"},
        {ok: hasTheme, label: "Header & Footer"},
        {ok: hasTheme, label: "CSS Stylesheet"},
        {ok: contentCount > 0, label: contentCount + " page(s) with content"},
    ];

    // Pages
    state.pages.filter(p => p !== "home").forEach(p => {
        const hasContent = !!state.pageContent[p];
        const isSystem = (p === "gallery" || p === "blog");
        items.push({
            ok: isSystem || hasContent || !!state.generatedPages[p],
            label: p.charAt(0).toUpperCase() + p.slice(1) + " page" + (hasContent ? " ✅" : isSystem ? " (system)" : "")
        });
    });

    items.push(
        {ok: true, label: "Navigation menus"},
        {ok: state.userImages.length > 0, label: state.userImages.length + " user image(s) uploaded"}
    );

    items.forEach(i => {
        const icon = i.ok ? "fa-check-circle" : "fa-circle";
        const cls = i.ok ? "ok" : "pending";
        cl.innerHTML += '<div class="checklist-item ' + cls + '"><i class="fas ' + icon + '"></i> ' + i.label + '</div>';
    });

    if (state.slug) {
        const url = "/admin/ai-theme-builder/preview?theme=" + state.slug;
        document.getElementById("previewUrl4_review").textContent = url;
        document.getElementById("previewIframe4_review").src = url;
    }

    // Page tabs for preview — with Regenerate and Accept per page
    const tabsContainer = document.getElementById("reviewPageTabs");
    if (tabsContainer && state.slug) {
        tabsContainer.innerHTML = "";
        const allPages = ["home", ...state.pages.filter(p => p !== "home")];
        if (!state.reviewAccepted) state.reviewAccepted = {};

        allPages.forEach(p => {
            const isAccepted = !!state.reviewAccepted[p];
            const isGenerated = p === "home" || !!state.generatedPages[p] || (p === "blog" || p === "gallery");
            const canRegen = p !== "home" && p !== "blog" && p !== "gallery";
            const tab = document.createElement("div");
            tab.className = "tab" + (p === "home" ? " active" : "");
            tab.style.cssText = "display:flex;align-items:center;gap:6px;justify-content:space-between;padding:8px 10px";
            tab.innerHTML = ''
                + '<div style="display:flex;align-items:center;gap:6px;flex:1;min-width:0;cursor:pointer" class="review-tab-label">'
                + '<i class="' + getPageIcon(p) + '" style="font-size:12px"></i> '
                + '<span style="flex:1">' + ucfirst(p) + '</span>'
                + (isAccepted ? '<span style="color:var(--ctp-green);font-size:11px">✅</span>' : '')
                + '</div>'
                + '<div style="display:flex;gap:4px;align-items:center">'
                + (canRegen ? '<button class="review-regen-btn" data-page="' + p + '" title="Regenerate this page" style="background:transparent;border:1px solid var(--ctp-surface2);border-radius:5px;padding:3px 6px;cursor:pointer;color:var(--ctp-text);font-size:10px"><i class="fas fa-redo"></i></button>' : '')
                + (canRegen ? '<button class="review-edit-btn" data-page="' + p + '" title="Edit page content" style="background:transparent;border:1px solid var(--ctp-surface2);border-radius:5px;padding:3px 6px;cursor:pointer;color:var(--ctp-text);font-size:10px"><i class="fas fa-pen"></i></button>' : '')
                + '<button class="review-accept-btn" data-page="' + p + '" title="' + (isAccepted ? 'Accepted' : 'Accept this page') + '" style="background:' + (isAccepted ? 'var(--ctp-green)' : 'transparent') + ';border:1px solid ' + (isAccepted ? 'var(--ctp-green)' : 'var(--ctp-surface2)') + ';border-radius:5px;padding:3px 6px;cursor:pointer;color:' + (isAccepted ? 'var(--ctp-crust)' : 'var(--ctp-text)') + ';font-size:10px"><i class="fas fa-check"></i></button>'
                + '</div>';

            // Click label → preview
            tab.querySelector(".review-tab-label").onclick = () => {
                tabsContainer.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
                tab.classList.add("active");
                const pageUrl = p === "home"
                    ? "/admin/ai-theme-builder/preview?theme=" + state.slug
                    : "/admin/ai-theme-builder/preview?theme=" + state.slug + "&page=" + p;
                document.getElementById("previewUrl4_review").textContent = pageUrl;
                document.getElementById("previewIframe4_review").src = pageUrl;
            };
            tabsContainer.appendChild(tab);
        });

        // Bind edit buttons
        tabsContainer.querySelectorAll(".review-edit-btn").forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const pid = btn.dataset.page;
                openPageEditor(pid);
            };
        });

        // Bind regen buttons
        tabsContainer.querySelectorAll(".review-regen-btn").forEach(btn => {
            btn.onclick = async (e) => {
                e.stopPropagation();
                const pid = btn.dataset.page;
                btn.style.opacity = ".5"; btn.style.pointerEvents = "none";
                const {provider, model} = getDesignModelParts();
                try {
                    toast("Regenerating " + ucfirst(pid) + " page...", "info");
                    const pageContent = state.pageContent[pid];
                    const pagePlan = state.contentPlan[pid];
                    const result = await api("/api/ai-theme-builder/wizard/page", {
                        prompt: document.getElementById("wPrompt").value,
                        industry: getChipValue("wIndustry"),
                        style: getChipValue("wStyle"),
                        mood: getChipValue("wMood"),
                        language: document.getElementById("wLang").value,
                        creativity: getDesignCreativity(),
                        brief: state.brief,
                        slug: state.slug,
                        page_type: pid,
                        layout_style: state.pageLayouts?.[pid] || "auto",
                        business_info: state.businessInfo,
                        user_images: state.userImages,
                        page_content: pageContent ? pageContent.html : null,
                        content_plan: pagePlan || null,
                        provider, model
                    });
                    if (result.ok) {
                        state.generatedPages[pid] = result;
                        state.reviewAccepted[pid] = false;
                        toast(ucfirst(pid) + " regenerated!", "success");
                        // Refresh preview
                        const pageUrl = "/admin/ai-theme-builder/preview?theme=" + state.slug + "&page=" + pid;
                        document.getElementById("previewIframe4_review").src = pageUrl + "&_t=" + Date.now();
                        initStep5(); // Rebuild tabs to update status
                    } else {
                        toast("Error: " + (result.error || "Failed"), "error");
                    }
                } catch(err) { toast("Error: " + err.message, "error"); }
                btn.style.opacity = "1"; btn.style.pointerEvents = "";
            };
        });

        // Bind accept buttons
        tabsContainer.querySelectorAll(".review-accept-btn").forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const pid = btn.dataset.page;
                state.reviewAccepted[pid] = !state.reviewAccepted[pid];
                saveState();
                initStep5(); // Rebuild to update visual state
            };
        });
    }
}

document.getElementById("btnApply").onclick = async () => {
    if (!state.slug) {
        toast("No theme to apply!", "error");
        return;
    }

    // Show custom confirm overlay instead of native confirm()
    const overlay = document.createElement("div");
    overlay.style.cssText = "position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:99999";
    overlay.innerHTML = `
        <div style="background:var(--ctp-surface0,#313244);border-radius:16px;padding:32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.5)">
            <div style="font-size:48px;margin-bottom:16px">🎨</div>
            <h3 style="color:var(--ctp-text,#cdd6f4);margin:0 0 8px">Apply Theme</h3>
            <p style="color:var(--ctp-subtext0,#a6adc8);margin:0 0 24px;font-size:14px">Make <strong>${state.slug}</strong> your live theme?</p>
            <div style="display:flex;gap:12px;justify-content:center">
                <button id="_applyCancel" style="padding:10px 24px;border-radius:8px;border:1px solid var(--ctp-surface2,#585b70);background:transparent;color:var(--ctp-text,#cdd6f4);cursor:pointer;font-size:14px">Cancel</button>
                <button id="_applyConfirm" style="padding:10px 24px;border-radius:8px;border:none;background:var(--ctp-green,#a6e3a1);color:var(--ctp-crust,#11111b);cursor:pointer;font-weight:600;font-size:14px">Apply Now</button>
            </div>
        </div>`;
    document.body.appendChild(overlay);

    const choice = await new Promise(resolve => {
        document.getElementById("_applyCancel").onclick = () => { overlay.remove(); resolve(false); };
        document.getElementById("_applyConfirm").onclick = () => resolve(true);
        overlay.onclick = (e) => { if (e.target === overlay) { overlay.remove(); resolve(false); } };
    });
    if (!choice) return;

    // Show loading state
    const confirmBtn = document.getElementById("_applyConfirm");
    confirmBtn.textContent = "Applying...";
    confirmBtn.disabled = true;

    try {
        const r = await api("/api/ai-theme-builder/apply", {slug: state.slug});
        if (r.ok) {
            overlay.querySelector("div > div").innerHTML = `
                <div style="font-size:48px;margin-bottom:16px">✅</div>
                <h3 style="color:var(--ctp-green,#a6e3a1);margin:0 0 8px">Theme Applied!</h3>
                <p style="color:var(--ctp-subtext0,#a6adc8);margin:0 0 8px;font-size:14px">Redirecting to your new site...</p>
                <div style="width:40px;height:40px;border:3px solid var(--ctp-surface2,#585b70);border-top-color:var(--ctp-green,#a6e3a1);border-radius:50%;margin:16px auto;animation:spin 1s linear infinite"></div>`;
            setTimeout(() => { window.location.href = "/"; }, 2500);
        } else {
            overlay.remove();
            toast("Error: " + (r.error || "Unknown"), "error");
        }
    } catch(e) {
        overlay.remove();
        toast("Error: " + e.message, "error");
    }
};

document.getElementById("btnExport").onclick = () => {
    if (state.slug) {
        window.location.href = "/api/ai-theme-builder/export?theme=" + state.slug;
    }
};

// Step 4 (Build) navigation
document.getElementById("btnStep4Back").onclick = () => goToStep(3);
document.getElementById("btnStep4Next").onclick = () => {
    goToStep(5); // goToStep auto-calls initStep5()
};
// Step 5 (Review) navigation
document.getElementById("btnStep5Back").onclick = () => goToStep(4);
document.getElementById("btnStartFresh").onclick = () => {
    if (confirm("Start over? This will clear all progress.")) {
        clearSavedState();
        window.location.reload();
    }
};

// ═══════════════════════════════════════════
// HEADER/FOOTER VISUAL PREVIEWS (SVG schematics)
// ═══════════════════════════════════════════
// HEADER & FOOTER PATTERN PICKER (visual grid)
// ═══════════════════════════════════════════
const HEADER_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Random</text></svg>'},
    // Standard
    {id:"classic", name:"Classic", group:"standard", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="12" width="30" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="48" y="16" width="16" height="4" rx="1" fill="#6c7086"/><rect x="68" y="16" width="16" height="4" rx="1" fill="#6c7086"/><rect x="88" y="16" width="16" height="4" rx="1" fill="#6c7086"/><rect x="112" y="12" width="22" height="12" rx="6" fill="#89b4fa" opacity=".4"/></svg>'},
    {id:"nav-center", name:"Nav Center", group:"standard", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="12" width="28" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="46" y="16" width="12" height="4" rx="1" fill="#6c7086"/><rect x="62" y="16" width="12" height="4" rx="1" fill="#6c7086"/><rect x="78" y="16" width="12" height="4" rx="1" fill="#6c7086"/><rect x="94" y="16" width="12" height="4" rx="1" fill="#6c7086"/><rect x="116" y="14" width="18" height="8" rx="4" fill="#89b4fa" opacity=".4"/></svg>'},
    {id:"brand-center", name:"Brand Center", group:"standard", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="46" y="4" width="48" height="14" rx="3" fill="#89b4fa" opacity=".3"/><rect x="22" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="42" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="84" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="104" y="28" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    {id:"stacked", name:"Stacked", group:"standard", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="42" y="4" width="56" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="16" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="36" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="56" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="76" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="96" y="28" width="14" height="4" rx="1" fill="#6c7086"/><rect x="116" y="28" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    {id:"inline-tight", name:"Inline Tight", group:"standard", svg:'<svg width="140" height="32" viewBox="0 0 140 32"><rect width="140" height="32" rx="4" fill="#313244"/><rect x="6" y="10" width="24" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="36" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="52" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="68" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="84" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="100" y="14" width="12" height="4" rx="1" fill="#6c7086"/></svg>'},
    // Topbar
    {id:"topbar-info", name:"Info Topbar", group:"topbar", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="14" fill="#1e1e2e"/><rect x="6" y="4" width="40" height="5" rx="1" fill="#585b70"/><rect x="94" y="4" width="40" height="5" rx="1" fill="#585b70"/><rect y="14" width="140" height="30" fill="#313244"/><rect x="6" y="20" width="28" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="50" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="68" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="86" y="23" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    {id:"topbar-social", name:"Social Topbar", group:"topbar", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="14" fill="#1e1e2e"/><circle cx="12" cy="7" r="4" fill="#585b70"/><circle cx="24" cy="7" r="4" fill="#585b70"/><circle cx="36" cy="7" r="4" fill="#585b70"/><rect x="90" y="4" width="44" height="6" rx="2" fill="#585b70"/><rect y="14" width="140" height="30" fill="#313244"/><rect x="6" y="20" width="28" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="52" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="70" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="88" y="23" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    {id:"topbar-full", name:"Full Topbar", group:"topbar", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="14" fill="#1e1e2e"/><rect x="6" y="3" width="30" height="4" rx="1" fill="#585b70"/><rect x="42" y="3" width="30" height="4" rx="1" fill="#585b70"/><circle cx="112" cy="7" r="4" fill="#585b70"/><circle cx="124" cy="7" r="4" fill="#585b70"/><rect y="14" width="140" height="30" fill="#313244"/><rect x="6" y="20" width="28" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="50" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="68" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="86" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="108" y="21" width="24" height="8" rx="4" fill="#89b4fa" opacity=".4"/></svg>'},
    {id:"topbar-announce", name:"Announce Bar", group:"topbar", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="14" fill="#89b4fa" opacity=".15"/><rect x="30" y="4" width="80" height="5" rx="1" fill="#89b4fa" opacity=".4"/><rect y="14" width="140" height="30" fill="#313244"/><rect x="6" y="20" width="28" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="50" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="68" y="23" width="14" height="4" rx="1" fill="#6c7086"/><rect x="86" y="23" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    // Creative
    {id:"split-nav", name:"Split Nav", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="12" y="14" width="14" height="4" rx="1" fill="#6c7086"/><rect x="30" y="14" width="14" height="4" rx="1" fill="#6c7086"/><rect x="52" y="8" width="36" height="16" rx="3" fill="#89b4fa" opacity=".3"/><rect x="96" y="14" width="14" height="4" rx="1" fill="#6c7086"/><rect x="114" y="14" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    {id:"actions-bar", name:"Actions Bar", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="10" width="28" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="44" y="14" width="14" height="4" rx="1" fill="#6c7086"/><rect x="62" y="14" width="14" height="4" rx="1" fill="#6c7086"/><circle cx="98" cy="18" r="6" fill="#585b70"/><rect x="112" y="12" width="22" height="12" rx="6" fill="#89b4fa" opacity=".4"/></svg>'},
    {id:"brand-tagline", name:"Brand + Tagline", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="32" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="6" y="22" width="50" height="4" rx="1" fill="#585b70"/><rect x="72" y="16" width="14" height="4" rx="1" fill="#6c7086"/><rect x="90" y="16" width="14" height="4" rx="1" fill="#6c7086"/><rect x="108" y="16" width="14" height="4" rx="1" fill="#6c7086"/></svg>'},
    // Transparent
    {id:"transparent", name:"Transparent", group:"transparent", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><defs><linearGradient id="hg1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#585b70" stop-opacity=".3"/><stop offset="1" stop-color="#313244" stop-opacity="0"/></linearGradient></defs><rect width="140" height="36" rx="4" fill="url(#hg1)"/><rect x="6" y="12" width="28" height="10" rx="2" fill="#cdd6f4" opacity=".3"/><rect x="50" y="15" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="68" y="15" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="86" y="15" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".5"/></svg>'},
    {id:"transparent-center", name:"Transp. Center", group:"transparent", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><defs><linearGradient id="hg2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#585b70" stop-opacity=".3"/><stop offset="1" stop-color="#313244" stop-opacity="0"/></linearGradient></defs><rect width="140" height="40" rx="4" fill="url(#hg2)"/><rect x="44" y="4" width="52" height="12" rx="2" fill="#cdd6f4" opacity=".3"/><rect x="20" y="28" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="40" y="28" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="86" y="28" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="106" y="28" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".4"/></svg>'},
    {id:"transparent-bold", name:"Transp. Bold", group:"transparent", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><defs><linearGradient id="hg3" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#585b70" stop-opacity=".4"/><stop offset="1" stop-color="#313244" stop-opacity="0"/></linearGradient></defs><rect width="140" height="36" rx="4" fill="url(#hg3)"/><rect x="6" y="8" width="36" height="16" rx="3" fill="#cdd6f4" opacity=".25"/><rect x="56" y="14" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="74" y="14" width="14" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="100" y="10" width="32" height="14" rx="7" fill="#89b4fa" opacity=".4"/></svg>'},
    // Minimal
    {id:"minimal-clean", name:"Minimal Clean", group:"minimal", svg:'<svg width="140" height="28" viewBox="0 0 140 28"><rect width="140" height="28" rx="4" fill="#313244"/><rect x="6" y="8" width="24" height="10" rx="2" fill="#89b4fa" opacity=".25"/><rect x="106" y="11" width="12" height="4" rx="1" fill="#585b70"/><rect x="122" y="11" width="12" height="4" rx="1" fill="#585b70"/></svg>'},
    {id:"minimal-line", name:"Minimal Line", group:"minimal", svg:'<svg width="140" height="30" viewBox="0 0 140 30"><rect width="140" height="29" rx="4" fill="#313244"/><rect x="6" y="10" width="24" height="8" rx="2" fill="#89b4fa" opacity=".25"/><rect x="100" y="12" width="10" height="4" rx="1" fill="#585b70"/><rect x="114" y="12" width="10" height="4" rx="1" fill="#585b70"/><rect x="0" y="28" width="140" height="1" fill="#585b70"/></svg>'},
    {id:"minimal-dots", name:"Minimal Dots", group:"minimal", svg:'<svg width="140" height="28" viewBox="0 0 140 28"><rect width="140" height="28" rx="4" fill="#313244"/><rect x="6" y="8" width="24" height="10" rx="2" fill="#89b4fa" opacity=".25"/><circle cx="100" cy="14" r="2" fill="#585b70"/><rect x="108" y="12" width="10" height="4" rx="1" fill="#585b70"/><circle cx="124" cy="14" r="2" fill="#585b70"/><rect x="128" y="12" width="10" height="4" rx="1" fill="#585b70"/></svg>'},
    // Bold
    {id:"bold-bar", name:"Bold Bar", group:"bold", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#89b4fa" opacity=".15"/><rect x="6" y="10" width="36" height="16" rx="3" fill="#89b4fa" opacity=".3"/><rect x="54" y="16" width="14" height="5" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="72" y="16" width="14" height="5" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="90" y="16" width="14" height="5" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="112" y="13" width="22" height="12" rx="6" fill="#cdd6f4" opacity=".3"/></svg>'},
    {id:"bold-offset", name:"Bold Offset", group:"bold", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="0" y="0" width="50" height="40" rx="4" fill="#89b4fa" opacity=".15"/><rect x="8" y="12" width="34" height="14" rx="3" fill="#89b4fa" opacity=".3"/><rect x="60" y="16" width="14" height="5" rx="1" fill="#6c7086"/><rect x="78" y="16" width="14" height="5" rx="1" fill="#6c7086"/><rect x="96" y="16" width="14" height="5" rx="1" fill="#6c7086"/></svg>'},
    {id:"burger-only", name:"Burger Only", group:"bold", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="10" width="36" height="14" rx="2" fill="#89b4fa" opacity=".3"/><rect x="118" y="12" width="16" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="118" y="18" width="16" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="118" y="24" width="12" height="3" rx="1" fill="#cdd6f4" opacity=".5"/></svg>'},
    // Industry
    {id:"professional", name:"Professional", group:"industry", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="14" fill="#1e1e2e"/><rect x="6" y="4" width="50" height="5" rx="1" fill="#585b70"/><rect x="100" y="4" width="34" height="5" rx="1" fill="#585b70"/><rect y="14" width="140" height="30" fill="#313244"/><rect x="6" y="20" width="30" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="48" y="24" width="12" height="4" rx="1" fill="#6c7086"/><rect x="64" y="24" width="12" height="4" rx="1" fill="#6c7086"/><rect x="80" y="24" width="12" height="4" rx="1" fill="#6c7086"/><rect x="102" y="22" width="30" height="8" rx="4" fill="#89b4fa" opacity=".4"/></svg>'},
    {id:"service-header", name:"Service", group:"industry", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="8" width="28" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="42" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="58" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="74" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="90" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="110" y="10" width="24" height="14" rx="7" fill="#a6e3a1" opacity=".3"/></svg>'},
    {id:"commerce", name:"Commerce", group:"industry", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="6" y="10" width="28" height="12" rx="2" fill="#89b4fa" opacity=".3"/><rect x="44" y="14" width="12" height="4" rx="1" fill="#6c7086"/><rect x="60" y="14" width="12" height="4" rx="1" fill="#6c7086"/><circle cx="100" cy="18" r="7" fill="#585b70"/><text x="100" y="21" text-anchor="middle" fill="#cdd6f4" font-size="8">🔍</text><circle cx="120" cy="18" r="7" fill="#585b70"/><text x="120" y="21" text-anchor="middle" fill="#cdd6f4" font-size="8">🛒</text></svg>'},
    {id:"editorial", name:"Editorial", group:"industry", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="20" y="4" width="100" height="14" rx="2" fill="#89b4fa" opacity=".2"/><text x="70" y="15" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".6">MASTHEAD</text><rect x="10" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="28" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="46" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="64" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="82" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="100" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="118" y="28" width="12" height="4" rx="1" fill="#6c7086"/><rect x="0" y="24" width="140" height="1" fill="#585b70"/></svg>'},
];

const FOOTER_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Random</text></svg>'},
    // Minimal
    {id:"minimal-single", name:"Single Row", group:"minimal", svg:'<svg width="140" height="28" viewBox="0 0 140 28"><rect width="140" height="28" rx="4" fill="#313244"/><rect x="6" y="10" width="24" height="8" rx="2" fill="#89b4fa" opacity=".3"/><rect x="42" y="12" width="12" height="4" rx="1" fill="#585b70"/><rect x="58" y="12" width="12" height="4" rx="1" fill="#585b70"/><rect x="74" y="12" width="12" height="4" rx="1" fill="#585b70"/><rect x="102" y="12" width="32" height="4" rx="1" fill="#45475a"/></svg>'},
    {id:"minimal-centered", name:"Centered", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="50" y="4" width="40" height="8" rx="2" fill="#89b4fa" opacity=".3"/><rect x="32" y="16" width="12" height="4" rx="1" fill="#585b70"/><rect x="48" y="16" width="12" height="4" rx="1" fill="#585b70"/><rect x="80" y="16" width="12" height="4" rx="1" fill="#585b70"/><rect x="96" y="16" width="12" height="4" rx="1" fill="#585b70"/><rect x="36" y="26" width="68" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"minimal-stacked", name:"Stacked", group:"minimal", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="46" y="4" width="48" height="10" rx="2" fill="#89b4fa" opacity=".3"/><rect x="20" y="20" width="12" height="4" rx="1" fill="#585b70"/><rect x="36" y="20" width="12" height="4" rx="1" fill="#585b70"/><rect x="52" y="20" width="12" height="4" rx="1" fill="#585b70"/><rect x="76" y="20" width="12" height="4" rx="1" fill="#585b70"/><rect x="92" y="20" width="12" height="4" rx="1" fill="#585b70"/><rect x="108" y="20" width="12" height="4" rx="1" fill="#585b70"/><circle cx="56" cy="36" r="4" fill="#585b70"/><circle cx="70" cy="36" r="4" fill="#585b70"/><circle cx="84" cy="36" r="4" fill="#585b70"/></svg>'},
    // Classic
    {id:"classic-3col", name:"3 Columns", group:"classic", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><rect x="6" y="6" width="36" height="5" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="14" width="30" height="3" rx="1" fill="#585b70"/><rect x="6" y="20" width="26" height="3" rx="1" fill="#585b70"/><rect x="52" y="6" width="20" height="4" rx="1" fill="#6c7086"/><rect x="52" y="13" width="28" height="3" rx="1" fill="#585b70"/><rect x="52" y="19" width="28" height="3" rx="1" fill="#585b70"/><rect x="52" y="25" width="28" height="3" rx="1" fill="#585b70"/><rect x="98" y="6" width="20" height="4" rx="1" fill="#6c7086"/><rect x="98" y="13" width="36" height="3" rx="1" fill="#585b70"/><rect x="98" y="19" width="30" height="3" rx="1" fill="#585b70"/><rect x="6" y="44" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"classic-4col", name:"4 Columns", group:"classic", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><rect x="4" y="6" width="28" height="5" rx="1" fill="#89b4fa" opacity=".3"/><rect x="4" y="14" width="24" height="3" rx="1" fill="#585b70"/><rect x="36" y="6" width="16" height="4" rx="1" fill="#6c7086"/><rect x="36" y="13" width="24" height="3" rx="1" fill="#585b70"/><rect x="36" y="19" width="24" height="3" rx="1" fill="#585b70"/><rect x="68" y="6" width="16" height="4" rx="1" fill="#6c7086"/><rect x="68" y="13" width="24" height="3" rx="1" fill="#585b70"/><rect x="68" y="19" width="24" height="3" rx="1" fill="#585b70"/><rect x="100" y="6" width="16" height="4" rx="1" fill="#6c7086"/><rect x="100" y="13" width="34" height="3" rx="1" fill="#585b70"/><rect x="100" y="19" width="28" height="3" rx="1" fill="#585b70"/><rect x="4" y="44" width="132" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"classic-asymmetric", name:"Asymmetric", group:"classic", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><rect x="4" y="6" width="52" height="5" rx="1" fill="#89b4fa" opacity=".3"/><rect x="4" y="14" width="48" height="3" rx="1" fill="#585b70"/><rect x="4" y="20" width="40" height="3" rx="1" fill="#585b70"/><rect x="4" y="26" width="44" height="3" rx="1" fill="#585b70"/><rect x="66" y="6" width="16" height="4" rx="1" fill="#6c7086"/><rect x="66" y="13" width="28" height="3" rx="1" fill="#585b70"/><rect x="66" y="19" width="28" height="3" rx="1" fill="#585b70"/><rect x="102" y="6" width="16" height="4" rx="1" fill="#6c7086"/><rect x="102" y="13" width="32" height="3" rx="1" fill="#585b70"/><rect x="102" y="19" width="28" height="3" rx="1" fill="#585b70"/><rect x="4" y="44" width="132" height="3" rx="1" fill="#45475a"/></svg>'},
    // Modern
    {id:"modern-split", name:"Split", group:"modern", svg:'<svg width="140" height="48" viewBox="0 0 140 48"><rect width="70" height="48" rx="4" fill="#313244"/><rect x="70" width="70" height="48" fill="#1e1e2e"/><rect x="6" y="8" width="30" height="6" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="40" height="3" rx="1" fill="#585b70"/><rect x="78" y="8" width="16" height="4" rx="1" fill="#6c7086"/><rect x="78" y="15" width="24" height="3" rx="1" fill="#585b70"/><rect x="78" y="21" width="24" height="3" rx="1" fill="#585b70"/><rect x="78" y="27" width="24" height="3" rx="1" fill="#585b70"/><rect x="6" y="40" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"modern-magazine", name:"Magazine", group:"modern", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><rect x="4" y="4" width="60" height="16" rx="2" fill="#89b4fa" opacity=".15"/><rect x="8" y="8" width="36" height="6" rx="1" fill="#89b4fa" opacity=".3"/><circle cx="74" cy="12" r="5" fill="#585b70"/><circle cx="88" cy="12" r="5" fill="#585b70"/><circle cx="102" cy="12" r="5" fill="#585b70"/><rect x="4" y="26" width="16" height="4" rx="1" fill="#6c7086"/><rect x="26" y="26" width="16" height="4" rx="1" fill="#6c7086"/><rect x="48" y="26" width="16" height="4" rx="1" fill="#6c7086"/><rect x="100" y="26" width="36" height="4" rx="1" fill="#585b70"/><rect x="4" y="44" width="132" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"modern-bands", name:"Bands", group:"modern", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="26" rx="4" fill="#313244"/><rect x="6" y="6" width="28" height="6" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="78" y="8" width="16" height="4" rx="1" fill="#6c7086"/><rect x="98" y="8" width="16" height="4" rx="1" fill="#6c7086"/><rect x="118" y="8" width="16" height="4" rx="1" fill="#6c7086"/><rect y="26" width="140" height="26" fill="#1e1e2e"/><rect x="6" y="32" width="20" height="4" rx="1" fill="#585b70"/><rect x="32" y="32" width="20" height="4" rx="1" fill="#585b70"/><rect x="58" y="32" width="20" height="4" rx="1" fill="#585b70"/><rect x="92" y="32" width="42" height="3" rx="1" fill="#45475a"/><rect x="6" y="44" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    // Creative
    {id:"creative-wave", name:"Wave", group:"creative", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><path d="M0 8 Q35 0 70 8 Q105 16 140 8 L140 0 L0 0Z" fill="#89b4fa" opacity=".1"/><rect x="6" y="18" width="32" height="6" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="28" width="50" height="3" rx="1" fill="#585b70"/><rect x="68" y="18" width="16" height="4" rx="1" fill="#6c7086"/><rect x="68" y="25" width="24" height="3" rx="1" fill="#585b70"/><rect x="100" y="18" width="16" height="4" rx="1" fill="#6c7086"/><rect x="100" y="25" width="28" height="3" rx="1" fill="#585b70"/><rect x="6" y="44" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"creative-diagonal", name:"Diagonal", group:"creative", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><polygon points="0,0 140,0 140,14 0,8" fill="#89b4fa" opacity=".1"/><rect x="6" y="18" width="32" height="6" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="28" width="44" height="3" rx="1" fill="#585b70"/><rect x="68" y="18" width="16" height="4" rx="1" fill="#6c7086"/><rect x="68" y="25" width="24" height="3" rx="1" fill="#585b70"/><rect x="100" y="18" width="16" height="4" rx="1" fill="#6c7086"/><rect x="100" y="25" width="28" height="3" rx="1" fill="#585b70"/><rect x="6" y="44" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"creative-bigbrand", name:"Big Brand", group:"creative", svg:'<svg width="140" height="52" viewBox="0 0 140 52"><rect width="140" height="52" rx="4" fill="#313244"/><rect x="6" y="6" width="70" height="14" rx="3" fill="#89b4fa" opacity=".2"/><rect x="6" y="26" width="80" height="3" rx="1" fill="#585b70"/><rect x="6" y="32" width="60" height="3" rx="1" fill="#585b70"/><circle cx="102" cy="14" r="5" fill="#585b70"/><circle cx="116" cy="14" r="5" fill="#585b70"/><circle cx="130" cy="14" r="5" fill="#585b70"/><rect x="6" y="44" width="128" height="3" rx="1" fill="#45475a"/></svg>'},
    // Detailed
    {id:"detailed-mega", name:"Mega Footer", group:"detailed", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="4" y="4" width="24" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="4" y="11" width="20" height="3" rx="1" fill="#585b70"/><rect x="4" y="17" width="20" height="3" rx="1" fill="#585b70"/><rect x="34" y="4" width="16" height="3" rx="1" fill="#6c7086"/><rect x="34" y="10" width="22" height="3" rx="1" fill="#585b70"/><rect x="34" y="16" width="22" height="3" rx="1" fill="#585b70"/><rect x="34" y="22" width="22" height="3" rx="1" fill="#585b70"/><rect x="62" y="4" width="16" height="3" rx="1" fill="#6c7086"/><rect x="62" y="10" width="22" height="3" rx="1" fill="#585b70"/><rect x="62" y="16" width="22" height="3" rx="1" fill="#585b70"/><rect x="90" y="4" width="16" height="3" rx="1" fill="#6c7086"/><rect x="90" y="10" width="22" height="3" rx="1" fill="#585b70"/><rect x="90" y="16" width="22" height="3" rx="1" fill="#585b70"/><rect x="118" y="4" width="16" height="3" rx="1" fill="#6c7086"/><rect x="118" y="10" width="18" height="3" rx="1" fill="#585b70"/><rect x="4" y="30" width="132" height="1" fill="#45475a"/><rect x="4" y="36" width="60" height="3" rx="1" fill="#585b70"/><circle cx="108" cy="38" r="4" fill="#585b70"/><circle cx="120" cy="38" r="4" fill="#585b70"/><circle cx="132" cy="38" r="4" fill="#585b70"/><rect x="4" y="48" width="132" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"detailed-corporate", name:"Corporate", group:"detailed", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="4" y="4" width="40" height="6" rx="1" fill="#89b4fa" opacity=".3"/><rect x="4" y="14" width="50" height="3" rx="1" fill="#585b70"/><rect x="4" y="20" width="40" height="3" rx="1" fill="#585b70"/><rect x="62" y="4" width="16" height="3" rx="1" fill="#6c7086"/><rect x="62" y="10" width="22" height="3" rx="1" fill="#585b70"/><rect x="62" y="16" width="22" height="3" rx="1" fill="#585b70"/><rect x="62" y="22" width="22" height="3" rx="1" fill="#585b70"/><rect x="92" y="4" width="20" height="3" rx="1" fill="#6c7086"/><rect x="92" y="10" width="44" height="12" rx="2" fill="#585b70" opacity=".3"/><rect x="96" y="14" width="36" height="5" rx="2" fill="#89b4fa" opacity=".3"/><rect x="4" y="36" width="132" height="1" fill="#45475a"/><rect x="4" y="44" width="80" height="3" rx="1" fill="#45475a"/><rect x="100" y="44" width="36" height="3" rx="1" fill="#45475a"/></svg>'},
    {id:"detailed-inforich", name:"Info Rich", group:"detailed", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="4" y="4" width="28" height="5" rx="1" fill="#89b4fa" opacity=".3"/><rect x="4" y="12" width="38" height="3" rx="1" fill="#585b70"/><rect x="4" y="18" width="32" height="3" rx="1" fill="#585b70"/><rect x="4" y="24" width="36" height="3" rx="1" fill="#585b70"/><rect x="50" y="4" width="14" height="3" rx="1" fill="#6c7086"/><rect x="50" y="10" width="20" height="3" rx="1" fill="#585b70"/><rect x="50" y="16" width="20" height="3" rx="1" fill="#585b70"/><rect x="78" y="4" width="14" height="3" rx="1" fill="#6c7086"/><rect x="78" y="10" width="20" height="3" rx="1" fill="#585b70"/><rect x="78" y="16" width="20" height="3" rx="1" fill="#585b70"/><rect x="106" y="4" width="14" height="3" rx="1" fill="#6c7086"/><rect x="106" y="10" width="28" height="3" rx="1" fill="#585b70"/><rect x="106" y="16" width="24" height="3" rx="1" fill="#585b70"/><rect x="106" y="22" width="20" height="3" rx="1" fill="#585b70"/><rect x="4" y="34" width="132" height="1" fill="#45475a"/><circle cx="56" cy="44" r="5" fill="#585b70"/><circle cx="70" cy="44" r="5" fill="#585b70"/><circle cx="84" cy="44" r="5" fill="#585b70"/><rect x="4" y="50" width="132" height="3" rx="1" fill="#45475a"/></svg>'},
];

const HERO_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    // Centered
    {id:"centered", name:"Centered", group:"centered", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect width="140" height="56" rx="4" fill="#89b4fa" opacity=".08"/><rect x="35" y="8" width="70" height="4" rx="2" fill="#89b4fa" opacity=".3"/><rect x="20" y="16" width="100" height="8" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="30" y="28" width="80" height="4" rx="1" fill="#585b70"/><rect x="42" y="38" width="24" height="8" rx="3" fill="#89b4fa" opacity=".6"/><rect x="72" y="38" width="24" height="8" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"centered-video", name:"Video BG", group:"centered", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect width="140" height="56" rx="4" fill="#89b4fa" opacity=".05"/><polygon points="62,18 82,28 62,38" fill="#89b4fa" opacity=".3"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="40" y="42" width="24" height="7" rx="3" fill="#89b4fa" opacity=".5"/><rect x="70" y="42" width="24" height="7" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"centered-minimal", name:"Minimal", group:"centered", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#1e1e2e"/><rect x="20" y="14" width="100" height="10" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="32" y="30" width="76" height="4" rx="1" fill="#585b70"/><rect x="50" y="40" width="40" height="8" rx="3" fill="#89b4fa" opacity=".5"/></svg>'},
    // Split
    {id:"split-image", name:"Split Image", group:"split", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="6" y="6" width="56" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="14" width="60" height="8" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="6" y="26" width="52" height="3" rx="1" fill="#585b70"/><rect x="6" y="32" width="48" height="3" rx="1" fill="#585b70"/><rect x="6" y="42" width="22" height="7" rx="3" fill="#89b4fa" opacity=".5"/><rect x="32" y="42" width="22" height="7" rx="3" fill="#585b70" opacity=".4"/><rect x="76" y="4" width="58" height="48" rx="6" fill="#89b4fa" opacity=".12"/><rect x="86" y="14" width="38" height="28" rx="4" fill="#585b70" opacity=".3"/></svg>'},
    {id:"split-cards", name:"Split + Cards", group:"split", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="16" width="58" height="7" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="6" y="28" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="42" width="22" height="7" rx="3" fill="#89b4fa" opacity=".5"/><rect x="76" y="6" width="56" height="44" rx="6" fill="#585b70" opacity=".2"/><rect x="80" y="10" width="48" height="12" rx="3" fill="#45475a"/><rect x="80" y="26" width="48" height="12" rx="3" fill="#45475a"/><rect x="68" y="14" width="16" height="8" rx="2" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"split-reverse", name:"Reverse Split", group:"split", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="48" rx="6" fill="#89b4fa" opacity=".12"/><rect x="16" y="14" width="38" height="28" rx="4" fill="#585b70" opacity=".3"/><rect x="76" y="6" width="56" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="76" y="14" width="58" height="8" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="76" y="26" width="52" height="3" rx="1" fill="#585b70"/><rect x="76" y="32" width="48" height="3" rx="1" fill="#585b70"/><rect x="76" y="42" width="22" height="7" rx="3" fill="#89b4fa" opacity=".5"/></svg>'},
    // Fullwidth
    {id:"fullscreen", name:"Fullscreen", group:"fullwidth", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect width="140" height="56" rx="4" fill="#89b4fa" opacity=".06"/><rect x="6" y="12" width="70" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="20" width="80" height="8" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="6" y="32" width="60" height="3" rx="1" fill="#585b70"/><rect x="6" y="42" width="24" height="7" rx="3" fill="#89b4fa" opacity=".5"/><rect x="34" y="42" width="24" height="7" rx="3" fill="#585b70" opacity=".4"/><circle cx="128" cy="48" r="6" fill="#585b70" opacity=".3"/></svg>'},
    {id:"fullscreen-stats", name:"Stats Bar", group:"fullwidth", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect width="140" height="56" rx="4" fill="#89b4fa" opacity=".06"/><rect x="6" y="6" width="70" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="6" y="14" width="80" height="7" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="6" y="25" width="60" height="3" rx="1" fill="#585b70"/><rect x="6" y="34" width="22" height="6" rx="3" fill="#89b4fa" opacity=".5"/><rect x="2" y="44" width="136" height="1" fill="#585b70" opacity=".3"/><rect x="8" y="47" width="14" height="4" rx="1" fill="#89b4fa" opacity=".4"/><text x="15" y="54" text-anchor="middle" fill="#585b70" font-size="4">500+</text><rect x="42" y="47" width="14" height="4" rx="1" fill="#89b4fa" opacity=".4"/><rect x="76" y="47" width="14" height="4" rx="1" fill="#89b4fa" opacity=".4"/><rect x="110" y="47" width="14" height="4" rx="1" fill="#89b4fa" opacity=".4"/></svg>'},
    // Creative
    {id:"editorial", name:"Editorial", group:"creative", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><rect width="140" height="56" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="2" rx="1" fill="#89b4fa" opacity=".5"/><rect x="6" y="12" width="58" height="7" rx="2" fill="#cdd6f4" opacity=".8"/><rect x="6" y="24" width="54" height="3" rx="1" fill="#585b70"/><rect x="6" y="30" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="42" width="16" height="5" rx="1" fill="#585b70" opacity=".5"/><rect x="26" y="42" width="28" height="6" rx="3" fill="#89b4fa" opacity=".5"/><rect x="76" y="4" width="58" height="48" rx="6" fill="#585b70" opacity=".15"/><rect x="82" y="10" width="46" height="36" rx="3" fill="#89b4fa" opacity=".1"/></svg>'},
    {id:"gradient-wave", name:"Gradient Wave", group:"creative", svg:'<svg width="140" height="56" viewBox="0 0 140 56"><defs><linearGradient id="hgw" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#89b4fa" stop-opacity=".3"/><stop offset="100%" stop-color="#cba6f7" stop-opacity=".3"/></linearGradient></defs><rect width="140" height="56" rx="4" fill="#313244"/><rect width="140" height="46" rx="4" fill="url(#hgw)"/><rect x="30" y="6" width="80" height="4" rx="2" fill="#fff" opacity=".4"/><rect x="20" y="14" width="100" height="8" rx="2" fill="#fff" opacity=".7"/><rect x="32" y="26" width="76" height="3" rx="1" fill="#fff" opacity=".4"/><rect x="42" y="34" width="24" height="7" rx="3" fill="#fff" opacity=".5"/><rect x="70" y="34" width="24" height="7" rx="3" fill="#fff" opacity=".3"/><path d="M0 44 Q35 38 70 44 Q105 50 140 44 L140 56 L0 56Z" fill="#313244"/></svg>'},
];

const FEATURES_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"grid-3col", name:"Grid 3-Col", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"grid-4col", name:"Grid 4-Col", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="44" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="78" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="112" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"grid-2col", name:"Grid 2-Col", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="60" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="74" y="10" width="60" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"cards-elevated", name:"Cards", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-bordered", name:"Bordered", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-glass", name:"Glass", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"alternating-rows", name:"Alternating", group:"alternating", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"alternating-zigzag", name:"Zigzag", group:"alternating", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"alternating-timeline", name:"Timeline", group:"alternating", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"icon-list-horizontal", name:"Icons Row", group:"icon", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="44" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="78" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="112" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"icon-list-numbered", name:"Numbered", group:"icon", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"icon-list-vertical", name:"Vertical", group:"icon", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"creative-bento", name:"Bento", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"creative-tabs", name:"Tabs", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".1"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"creative-carousel", name:"Carousel", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'}
];

const ABOUT_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"split-image-right", name:"Image Right", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"split-image-left", name:"Image Left", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"split-with-stats", name:"With Stats", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"split-video", name:"Video", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"creative-timeline", name:"Timeline", group:"creative", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"creative-team-mission", name:"Mission", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"creative-values-grid", name:"Values", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"minimal-centered", name:"Centered", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".08"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"minimal-two-column", name:"Two Column", group:"minimal", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"minimal-with-signature", name:"Signature", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".05"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'}
];

const TESTIMONIALS_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"cards-grid", name:"Cards Grid", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-masonry", name:"Masonry", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"cards-single-featured", name:"Featured", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="56" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="48" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="44" height="3" rx="1" fill="#585b70"/><rect x="70" y="6" width="56" height="28" rx="4" fill="#45475a"/><rect x="74" y="10" width="48" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="74" y="18" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-minimal", name:"Minimal Cards", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"slider-centered", name:"Centered", group:"slider", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><text x="14" y="18" fill="#89b4fa" font-size="20" opacity=".3">&ldquo;</text><rect x="30" y="10" width="80" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="36" y="18" width="68" height="3" rx="1" fill="#585b70"/><circle cx="70" cy="30" r="5" fill="#585b70" opacity=".4"/><rect x="80" y="28" width="30" height="3" rx="1" fill="#585b70" opacity=".3"/></svg>'},
    {id:"slider-split", name:"Split", group:"slider", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"slider-cards-row", name:"Card Row", group:"slider", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-quote-wall", name:"Quote Wall", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"creative-video-testimonial", name:"Video", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"creative-social-proof", name:"Social", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"minimal-list", name:"List", group:"minimal", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"minimal-centered", name:"Centered Min", group:"minimal", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><text x="14" y="18" fill="#89b4fa" font-size="20" opacity=".3">&ldquo;</text><rect x="30" y="10" width="80" height="4" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="36" y="18" width="68" height="3" rx="1" fill="#585b70"/><circle cx="70" cy="30" r="5" fill="#585b70" opacity=".4"/><rect x="80" y="28" width="30" height="3" rx="1" fill="#585b70" opacity=".3"/></svg>'}
];

const PRICING_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"columns-3", name:"3 Columns", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="7" width="36" height="26" rx="3" fill="#45475a"/><text x="26" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="12" y="23" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="12" y="27" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="50" y="4" width="36" height="30" rx="3" fill="#45475a"/><text x="68" y="16" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="54" y="20" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="54" y="24" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="92" y="7" width="36" height="26" rx="3" fill="#45475a"/><text x="110" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="96" y="23" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="96" y="27" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/></svg>'},
    {id:"columns-2", name:"2 Columns", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="7" width="56" height="26" rx="3" fill="#45475a"/><text x="36" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="12" y="23" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="12" y="27" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="70" y="4" width="56" height="30" rx="3" fill="#45475a"/><text x="98" y="16" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="74" y="20" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="74" y="24" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/></svg>'},
    {id:"columns-4", name:"4 Columns", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="7" width="26" height="26" rx="3" fill="#45475a"/><text x="21" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="12" y="23" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="12" y="27" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="40" y="7" width="26" height="26" rx="3" fill="#45475a"/><text x="53" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="44" y="23" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="44" y="27" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="72" y="4" width="26" height="30" rx="3" fill="#45475a"/><text x="85" y="16" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="76" y="20" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="76" y="24" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="104" y="7" width="26" height="26" rx="3" fill="#45475a"/><text x="117" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="108" y="23" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="108" y="27" width="18" height="2" rx="1" fill="#585b70" opacity=".3"/></svg>'},
    {id:"cards-elevated", name:"Cards", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-gradient", name:"Gradient", group:"cards", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"cards-horizontal", name:"Horizontal", group:"cards", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"comparison-table", name:"Table", group:"comparison", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="44" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="78" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="112" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"comparison-toggle", name:"Toggle", group:"comparison", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="7" width="36" height="26" rx="3" fill="#45475a"/><text x="26" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="12" y="23" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="12" y="27" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="50" y="4" width="36" height="30" rx="3" fill="#45475a"/><text x="68" y="16" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="54" y="20" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="54" y="24" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="92" y="7" width="36" height="26" rx="3" fill="#45475a"/><text x="110" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="96" y="23" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="96" y="27" width="28" height="2" rx="1" fill="#585b70" opacity=".3"/></svg>'},
    {id:"creative-slider", name:"Slider", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"creative-minimal", name:"Minimal", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="7" width="56" height="26" rx="3" fill="#45475a"/><text x="36" y="19" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="12" y="23" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="12" y="27" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="70" y="4" width="56" height="30" rx="3" fill="#45475a"/><text x="98" y="16" text-anchor="middle" fill="#89b4fa" font-size="8" opacity=".5">$29</text><rect x="74" y="20" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/><rect x="74" y="24" width="48" height="2" rx="1" fill="#585b70" opacity=".3"/></svg>'}
];

const CTA_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"banner-centered", name:"Centered", group:"banner", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".15"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"banner-gradient", name:"Gradient", group:"banner", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".25"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"banner-image", name:"Image BG", group:"banner", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".1"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"split-text-button", name:"Split", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"split-image-text", name:"Image+Text", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"split-card", name:"Card", group:"split", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="116" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="108" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="104" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-diagonal", name:"Diagonal", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".15"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"creative-wave", name:"Wave", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".2"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"creative-glassmorphism", name:"Glass", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".12"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"minimal-inline", name:"Inline", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".05"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"minimal-bordered", name:"Bordered", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".08"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"minimal-dark", name:"Dark", group:"minimal", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".3"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'}
];

const FAQ_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"accordion-simple", name:"Simple", group:"accordion", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"accordion-boxed", name:"Boxed", group:"accordion", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"accordion-numbered", name:"Numbered", group:"accordion", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"columns-2col", name:"2 Columns", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="60" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="74" y="10" width="60" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"columns-categories", name:"Categories", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"columns-split", name:"Split", group:"columns", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"creative-search", name:"Search", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".08"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"creative-tabs", name:"Tabs", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".1"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'}
];

const STATS_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"counters-row", name:"Counters", group:"counters", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><text x="20" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">500+</text><rect x="8" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="55" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">25+</text><rect x="43" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="90" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">98%</text><rect x="78" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="125" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">50+</text><rect x="113" y="26" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"counters-icons", name:"With Icons", group:"counters", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><text x="20" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">500+</text><rect x="8" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="55" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">25+</text><rect x="43" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="90" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">98%</text><rect x="78" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="125" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">50+</text><rect x="113" y="26" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"counters-boxed", name:"Boxed", group:"counters", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="40" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="44" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="44" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="72" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="76" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="76" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="104" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="108" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="108" y="18" width="14" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"visual-progress-bars", name:"Progress", group:"visual", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"visual-circular", name:"Circular", group:"visual", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="44" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="78" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="112" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"visual-large-numbers", name:"Large Nums", group:"visual", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><text x="20" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">500+</text><rect x="8" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="55" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">25+</text><rect x="43" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="90" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">98%</text><rect x="78" y="26" width="24" height="3" rx="1" fill="#585b70"/><text x="125" y="20" text-anchor="middle" fill="#89b4fa" font-size="11" opacity=".6">50+</text><rect x="113" y="26" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-split-bg", name:"Split BG", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"creative-with-image", name:"With Image", group:"creative", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".1"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'}
];

const CLIENTS_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"strip-simple", name:"Simple Strip", group:"strip", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="8" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="30" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="52" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="74" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="96" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="118" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/></svg>'},
    {id:"strip-scroll", name:"Scrolling", group:"strip", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="8" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="30" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="52" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="74" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="96" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="118" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/></svg>'},
    {id:"strip-two-rows", name:"Two Rows", group:"strip", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="8" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="30" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="52" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="74" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="96" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="118" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/></svg>'},
    {id:"grid-bordered", name:"Grid", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="44" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="78" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="112" y="10" width="30" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"grid-cards", name:"Cards", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="40" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="44" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="44" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="72" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="76" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="76" y="18" width="14" height="3" rx="1" fill="#585b70"/><rect x="104" y="6" width="26" height="28" rx="4" fill="#45475a"/><rect x="108" y="10" width="18" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="108" y="18" width="14" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"grid-with-names", name:"With Names", group:"grid", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#313244"/><rect x="8" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="30" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="52" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="74" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="96" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/><rect x="118" y="12" width="16" height="12" rx="2" fill="#585b70" opacity=".4"/></svg>'},
    {id:"featured-with-testimonial", name:"Testimonial", group:"featured", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"featured-case-study", name:"Case Study", group:"featured", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'}
];

const GALLERY_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"grid-3col", name:"3 Column", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"grid-masonry", name:"Masonry", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"grid-filterable", name:"Filterable", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"showcase-featured", name:"Featured", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"showcase-carousel", name:"Carousel", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="10" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="54" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/><rect x="98" y="10" width="40" height="20" rx="3" fill="#585b70" opacity=".4"/></svg>'},
    {id:"showcase-lightbox", name:"Lightbox", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"creative-mosaic", name:"Mosaic", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="39" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="72" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="105" y="6" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="6" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/><rect x="39" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".22"/><rect x="72" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".12"/><rect x="105" y="22" width="30" height="13" rx="2" fill="#89b4fa" opacity=".17"/></svg>'},
    {id:"creative-before-after", name:"Before/After", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'}
];

const TEAM_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"grid-cards", name:"Cards", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"grid-circular", name:"Circular", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"grid-minimal", name:"Minimal", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"showcase-featured", name:"Featured", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"showcase-carousel", name:"Carousel", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"showcase-split", name:"Split", group:"showcase", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"creative-hover-reveal", name:"Hover Reveal", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-org-chart", name:"Org Chart", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><circle cx="22" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="12" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="14" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="54" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="44" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="46" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="86" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="76" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="78" y="30" width="16" height="2" rx="1" fill="#585b70"/><circle cx="118" cy="14" r="7" fill="#585b70" opacity=".5"/><rect x="108" y="24" width="20" height="3" rx="1" fill="#cdd6f4" opacity=".4"/><rect x="110" y="30" width="16" height="2" rx="1" fill="#585b70"/></svg>'}
];

const BLOG_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"grid-3col", name:"3 Column", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="8" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="8" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="50" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="52" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="52" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="94" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="96" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="96" y="30" width="28" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"grid-2col", name:"2 Column", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="8" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="8" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="50" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="52" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="52" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="94" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="96" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="96" y="30" width="28" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"grid-masonry", name:"Masonry", group:"grid", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="8" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="8" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="50" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="52" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="52" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="94" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="96" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="96" y="30" width="28" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"list-horizontal", name:"List", group:"list", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"list-minimal", name:"Minimal List", group:"list", svg:'<svg width="140" height="44" viewBox="0 0 140 44"><rect width="140" height="44" rx="4" fill="#313244"/><rect x="10" y="8" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="9" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="16" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="17" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="24" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="25" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/><rect x="10" y="32" width="100" height="5" rx="2" fill="#45475a"/><rect x="118" y="33" width="8" height="3" rx="1" fill="#89b4fa" opacity=".3"/></svg>'},
    {id:"list-featured", name:"Featured", group:"list", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-magazine", name:"Magazine", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="8" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="8" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="50" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="52" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="52" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="94" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="96" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="96" y="30" width="28" height="2" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-carousel", name:"Carousel", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="8" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="8" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="50" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="52" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="52" y="30" width="28" height="2" rx="1" fill="#585b70"/><rect x="94" y="6" width="40" height="14" rx="2" fill="#89b4fa" opacity=".1"/><rect x="96" y="24" width="36" height="3" rx="1" fill="#cdd6f4" opacity=".5"/><rect x="96" y="30" width="28" height="2" rx="1" fill="#585b70"/></svg>'}
];

const CONTACT_PATTERNS = [
    {id:"auto", name:"AI Picks Best", group:"auto", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="6" fill="#1e1e2e" stroke="#585b70" stroke-dasharray="4 2"/><text x="70" y="16" text-anchor="middle" fill="#89b4fa" font-size="16">🎲</text><text x="70" y="30" text-anchor="middle" fill="#6c7086" font-size="8">Auto</text></svg>'},
    {id:"form-centered", name:"Centered", group:"form", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="20" y="6" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="14" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="22" width="100" height="5" rx="2" fill="#45475a"/><rect x="50" y="31" width="40" height="6" rx="3" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"form-split", name:"Split", group:"form", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'},
    {id:"form-card", name:"Card Form", group:"form", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="20" y="6" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="14" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="22" width="100" height="5" rx="2" fill="#45475a"/><rect x="50" y="31" width="40" height="6" rx="3" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"info-cards", name:"Info Cards", group:"info", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="8" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="12" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="12" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="50" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="54" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="54" y="18" width="24" height="3" rx="1" fill="#585b70"/><rect x="92" y="6" width="36" height="28" rx="4" fill="#45475a"/><rect x="96" y="10" width="28" height="4" rx="1" fill="#89b4fa" opacity=".3"/><rect x="96" y="18" width="24" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"info-map", name:"Map", group:"info", svg:'<svg width="140" height="36" viewBox="0 0 140 36"><rect width="140" height="36" rx="4" fill="#89b4fa" opacity=".08"/><rect x="30" y="6" width="80" height="6" rx="2" fill="#cdd6f4" opacity=".7"/><rect x="40" y="16" width="60" height="3" rx="1" fill="#585b70"/><rect x="48" y="24" width="44" height="8" rx="4" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"info-minimal", name:"Minimal", group:"info", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="20" y="6" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="14" width="100" height="5" rx="2" fill="#45475a"/><rect x="20" y="22" width="100" height="5" rx="2" fill="#45475a"/><rect x="50" y="31" width="40" height="6" rx="3" fill="#89b4fa" opacity=".5"/></svg>'},
    {id:"creative-split-bg", name:"Split BG", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/><rect x="76" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="76" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="76" y="24" width="44" height="3" rx="1" fill="#585b70"/></svg>'},
    {id:"creative-faq-combo", name:"FAQ Combo", group:"creative", svg:'<svg width="140" height="40" viewBox="0 0 140 40"><rect width="140" height="40" rx="4" fill="#313244"/><rect x="6" y="8" width="56" height="6" rx="2" fill="#cdd6f4" opacity=".6"/><rect x="6" y="18" width="50" height="3" rx="1" fill="#585b70"/><rect x="6" y="24" width="44" height="3" rx="1" fill="#585b70"/><rect x="76" y="4" width="58" height="32" rx="4" fill="#89b4fa" opacity=".12"/></svg>'}
];

// ═══════════════════════════════════════════
// PAGE LAYOUT STYLES — hints for sub-page generation
// ═══════════════════════════════════════════
const PAGE_LAYOUT_STYLES = {
    about: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"story",    icon:"📖", name:"Story",        desc:"Narrative + timeline + team"},
        {id:"split",    icon:"📐", name:"Split",        desc:"Image left, text right"},
        {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Clean text, lots of space"},
        {id:"magazine", icon:"📰", name:"Magazine",     desc:"Editorial multi-column"},
    ],
    services: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"cards",    icon:"🃏", name:"Cards Grid",   desc:"Icon cards in grid layout"},
        {id:"detailed", icon:"📋", name:"Detailed",     desc:"Alternating image+text rows"},
        {id:"compact",  icon:"📊", name:"Compact",      desc:"Dense list with icons"},
        {id:"showcase", icon:"🖼️", name:"Showcase",     desc:"Large images per service"},
    ],
    contact: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"form",     icon:"📝", name:"Form Focus",   desc:"Large centered form"},
        {id:"split",    icon:"📐", name:"Split",        desc:"Info left, form right"},
        {id:"map",      icon:"🗺️", name:"Map + Form",   desc:"Map section with form below"},
        {id:"cards",    icon:"🃏", name:"Info Cards",   desc:"Phone/email/address cards"},
    ],
    pricing: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"columns",  icon:"📊", name:"Columns",      desc:"2-3 tier comparison"},
        {id:"cards",    icon:"🃏", name:"Cards",        desc:"Elevated feature cards"},
        {id:"table",    icon:"📋", name:"Table",        desc:"Detailed comparison table"},
        {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Simple clean pricing"},
    ],
    gallery: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"grid",     icon:"⊞",  name:"Grid",         desc:"Clean uniform grid"},
        {id:"masonry",  icon:"🧱", name:"Masonry",      desc:"Pinterest-style layout"},
        {id:"carousel", icon:"🎠", name:"Carousel",     desc:"Sliding gallery"},
        {id:"lightbox", icon:"🔍", name:"Lightbox",     desc:"Click to zoom overlay"},
    ],
    portfolio: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"grid",     icon:"⊞",  name:"Grid",         desc:"Project cards grid"},
        {id:"case",     icon:"📁", name:"Case Studies",  desc:"Detailed project pages"},
        {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Clean image-focused"},
        {id:"creative", icon:"🎨", name:"Creative",     desc:"Asymmetric art layout"},
    ],
    blog: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"grid",     icon:"⊞",  name:"Card Grid",    desc:"3-column article cards"},
        {id:"list",     icon:"📋", name:"List",         desc:"Horizontal article rows"},
        {id:"magazine", icon:"📰", name:"Magazine",     desc:"Featured + grid mix"},
        {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Simple clean list"},
    ],
    team: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"grid",     icon:"⊞",  name:"Card Grid",    desc:"Photo + name + role cards"},
        {id:"circular", icon:"⭕", name:"Circular",     desc:"Round photos, centered"},
        {id:"detailed", icon:"📋", name:"Detailed",     desc:"Large cards with bios"},
        {id:"creative", icon:"🎨", name:"Creative",     desc:"Hover reveal + social"},
    ],
    faq: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"accordion",icon:"📂", name:"Accordion",    desc:"Expandable Q&A list"},
        {id:"columns",  icon:"📊", name:"2 Columns",    desc:"Split left/right layout"},
        {id:"search",   icon:"🔍", name:"Searchable",   desc:"With search bar on top"},
        {id:"tabs",     icon:"📑", name:"Tabbed",       desc:"Categories in tabs"},
    ],
    testimonials: [
        {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
        {id:"cards",    icon:"🃏", name:"Cards",        desc:"Quote cards in grid"},
        {id:"slider",   icon:"🎠", name:"Slider",       desc:"One at a time carousel"},
        {id:"wall",     icon:"🧱", name:"Quote Wall",   desc:"Masonry quote blocks"},
        {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Large centered quotes"},
    ],
    features: [
        {id:"auto",        icon:"🎲", name:"AI Picks",      desc:"Best for your industry"},
        {id:"grid",        icon:"🔲", name:"Feature Grid",   desc:"Icon + title + desc cards"},
        {id:"alternating", icon:"↔️", name:"Alternating",    desc:"Image-text rows flip sides"},
        {id:"showcase",    icon:"⭐", name:"Showcase",       desc:"Hero feature + grid below"},
        {id:"tabs",        icon:"📑", name:"Tabbed",         desc:"Features in tab panels"},
    ],
};
// Fallback for page types not listed above
const DEFAULT_PAGE_LAYOUTS = [
    {id:"auto",     icon:"🎲", name:"AI Picks",     desc:"Best for your industry"},
    {id:"modern",   icon:"🏗️", name:"Modern",       desc:"Bold sections + cards"},
    {id:"classic",  icon:"📜", name:"Classic",      desc:"Traditional layout"},
    {id:"minimal",  icon:"✨", name:"Minimal",      desc:"Clean and spacious"},
    {id:"creative", icon:"🎨", name:"Creative",     desc:"Unique asymmetric"},
];

function renderPageLayoutCards() {
    const container = document.getElementById("pageLayoutsContainer");
    if (!container) return;
    container.innerHTML = "";

    const selectedPages = (state.pages || []).filter(p => p !== "home");
    if (selectedPages.length === 0) {
        container.innerHTML = '<div style="font-size:12px;color:var(--ctp-overlay0);padding:8px 0">No sub-pages selected. Choose pages in Step 1.</div>';
        return;
    }

    if (!state.pageLayouts) state.pageLayouts = {};

    selectedPages.forEach(pageId => {
        const pageInfo = PAGE_TYPES.find(p => p.id === pageId);
        const pageName = pageInfo ? pageInfo.name : pageId;
        const pageIcon = pageInfo ? pageInfo.icon : "fas fa-file";
        const layouts = PAGE_LAYOUT_STYLES[pageId] || DEFAULT_PAGE_LAYOUTS;
        const current = state.pageLayouts[pageId] || "auto";

        const group = document.createElement("div");
        group.className = "page-layout-group";
        group.innerHTML = '<div class="plg-header"><span class="plg-icon"><i class="' + pageIcon + '"></i></span><span class="plg-name">' + pageName + '</span></div>';

        const row = document.createElement("div");
        row.className = "page-layout-row";

        layouts.forEach(layout => {
            const card = document.createElement("div");
            card.className = "page-layout-card" + (layout.id === current ? " selected" : "");
            card.innerHTML = '<div class="plc-icon">' + layout.icon + '</div><div class="plc-name">' + layout.name + '</div><div class="plc-desc">' + layout.desc + '</div>';
            card.onclick = () => {
                row.querySelectorAll(".page-layout-card").forEach(c => c.classList.remove("selected"));
                card.classList.add("selected");
                state.pageLayouts[pageId] = layout.id;
            };
            row.appendChild(card);
        });

        group.appendChild(row);
        container.appendChild(group);
    });
}

// Build pattern grid
function buildPatternGrid(containerId, patterns, hiddenInputId) {
    const grid = document.getElementById(containerId);
    if (!grid) return;
    grid.innerHTML = "";
    patterns.forEach(p => {
        const card = document.createElement("div");
        card.className = "pattern-card" + (p.id === "auto" ? " auto-card selected" : "");
        card.dataset.patternId = p.id;
        card.innerHTML = p.svg
            + '<div class="pc-name">' + p.name + '</div>'
            + (p.group !== "auto" ? '<div class="pc-group">' + p.group + '</div>' : '');
        card.onclick = () => {
            grid.querySelectorAll(".pattern-card").forEach(c => c.classList.remove("selected"));
            card.classList.add("selected");
            document.getElementById(hiddenInputId).value = p.id;
        };
        grid.appendChild(card);
    });
}

// Page Editor modal for Step 5
function openPageEditor(pageId) {
    let modal = document.getElementById("pageEditorModal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "pageEditorModal";
        modal.style.cssText = "position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center";
        modal.innerHTML = '<div style="background:var(--ctp-base);border-radius:16px;width:90vw;max-width:900px;height:80vh;display:flex;flex-direction:column;overflow:hidden">'
            + '<div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--ctp-surface1)">'
            + '<h3 id="pageEditorTitle" style="margin:0;font-size:16px;color:var(--ctp-text)">Edit Page</h3>'
            + '<div style="display:flex;gap:8px">'
            + '<button id="pageEditorSave" style="background:var(--ctp-blue);color:var(--ctp-crust);border:none;padding:6px 16px;border-radius:8px;cursor:pointer;font-size:13px">Save & Close</button>'
            + '<button id="pageEditorCancel" style="background:var(--ctp-surface1);color:var(--ctp-text);border:none;padding:6px 12px;border-radius:8px;cursor:pointer;font-size:13px">Cancel</button>'
            + '</div></div>'
            + '<textarea id="pageEditorContent" style="flex:1;padding:20px;font-family:monospace;font-size:13px;border:none;background:var(--ctp-mantle);color:var(--ctp-text);resize:none;outline:none"></textarea>'
            + '</div>';
        document.body.appendChild(modal);
    }
    modal.style.display = "flex";
    document.getElementById("pageEditorTitle").textContent = "Edit: " + ucfirst(pageId);
    const content = state.pageContent[pageId];
    document.getElementById("pageEditorContent").value = content ? content.html : "";
    
    document.getElementById("pageEditorSave").onclick = async () => {
        const newHtml = document.getElementById("pageEditorContent").value;
        if (!state.pageContent[pageId]) state.pageContent[pageId] = {};
        state.pageContent[pageId].html = newHtml;
        // Save to DB via wizard/page endpoint with raw content
        try {
            const result = await api("/api/ai-theme-builder/wizard/page", {
                slug: state.slug,
                page_type: pageId,
                raw_content: newHtml,
                brief: state.brief
            });
            if (result.ok) toast("Page saved!", "success");
        } catch(e) { /* silent — content saved in state */ }
        state.reviewAccepted[pageId] = false;
        saveState();
        modal.style.display = "none";
        initStep5();
    };
    document.getElementById("pageEditorCancel").onclick = () => { modal.style.display = "none"; };
    modal.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };
}

// Init grids when Design step is shown
function initPatternGrids() {
    buildPatternGrid("headerPatternGrid", HEADER_PATTERNS, "designHeaderLayout");
    buildPatternGrid("heroPatternGrid", HERO_PATTERNS, "designHeroLayout");
    buildPatternGrid("footerPatternGrid", FOOTER_PATTERNS, "designFooterLayout");
    buildPatternGrid("featuresPatternGrid", FEATURES_PATTERNS, "designFeaturesLayout");
    buildPatternGrid("aboutPatternGrid", ABOUT_PATTERNS, "designAboutLayout");
    buildPatternGrid("testimonialsPatternGrid", TESTIMONIALS_PATTERNS, "designTestimonialsLayout");
    buildPatternGrid("pricingPatternGrid", PRICING_PATTERNS, "designPricingLayout");
    buildPatternGrid("ctaPatternGrid", CTA_PATTERNS, "designCTALayout");
    buildPatternGrid("faqPatternGrid", FAQ_PATTERNS, "designFAQLayout");
    buildPatternGrid("statsPatternGrid", STATS_PATTERNS, "designStatsLayout");
    buildPatternGrid("clientsPatternGrid", CLIENTS_PATTERNS, "designClientsLayout");
    buildPatternGrid("galleryPatternGrid", GALLERY_PATTERNS, "designGalleryLayout");
    buildPatternGrid("teamPatternGrid", TEAM_PATTERNS, "designTeamLayout");
    buildPatternGrid("blogPatternGrid", BLOG_PATTERNS, "designBlogLayout");
    buildPatternGrid("contactPatternGrid", CONTACT_PATTERNS, "designContactLayout");
}
// Called from goToStep(3) or initDesignStep

// DEVICE PREVIEW TOGGLE
// ═══════════════════════════════════════════
document.querySelectorAll(".device-btns").forEach(group => {
    group.querySelectorAll("button").forEach(btn => {
        btn.onclick = () => {
            group.querySelectorAll("button").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            const frame = btn.closest(".preview-panel").querySelector(".preview-frame iframe");
            if (!frame) return;
            const device = btn.dataset.device;
            if (device === "tablet") frame.style.width = "768px";
            else if (device === "mobile") frame.style.width = "375px";
            else frame.style.width = "100%";
        };
    });
});
</script>
<?php
if (file_exists(CMS_ROOT . '/admin/includes/ai-assistant-widget.php')) {
    require_once CMS_ROOT . '/admin/includes/ai-assistant-widget.php';
}
?>
</body>
</html>
