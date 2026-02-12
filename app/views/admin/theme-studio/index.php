<?php
/**
 * Theme Studio — Full-Screen Visual Theme Customizer
 *
 * Available variables:
 *   $themeSlug, $themeName, $schema, $values,
 *   $history, $aiAvailable, $pexelsAvailable, $csrfToken
 */
$schemaJson  = json_encode($schema ?? [], JSON_HEX_TAG | JSON_HEX_APOS);
$valuesJson  = json_encode($values ?? new stdClass, JSON_HEX_TAG | JSON_HEX_APOS);
$historyJson = json_encode($history ?? [], JSON_HEX_TAG | JSON_HEX_APOS);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Theme Studio — <?= htmlspecialchars($themeName ?? 'Theme') ?></title>
<style>
/* ═══════════════════════════════════════════════════════════
   Theme Studio — Catppuccin Mocha Dark Theme
   All styles scoped to #ts-app to prevent iframe leaks
   ═══════════════════════════════════════════════════════════ */

/* ── Reset ──────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --ts-bg:       #1e1e2e;
  --ts-surface:  #313244;
  --ts-overlay:  #45475a;
  --ts-text:     #cdd6f4;
  --ts-subtext:  #a6adc8;
  --ts-blue:     #89b4fa;
  --ts-green:    #a6e3a1;
  --ts-red:      #f38ba8;
  --ts-yellow:   #f9e2af;
  --ts-mauve:    #cba6f7;
  --ts-peach:    #fab387;
  --ts-teal:     #94e2d5;
  --ts-border:   #585b70;
  --ts-panel-w:  340px;
  --ts-topbar-h: 52px;
  --ts-radius:   8px;
  --ts-radius-sm:6px;
  --ts-transition:0.2s ease;
}

html,body{
  height:100%;overflow:hidden;
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,sans-serif;
  font-size:14px;color:var(--ts-text);background:var(--ts-bg);
  -webkit-font-smoothing:antialiased;
}

/* ── Scrollbar ─────────────────────────────────────────── */
.ts-scroll::-webkit-scrollbar{width:6px}
.ts-scroll::-webkit-scrollbar-track{background:transparent}
.ts-scroll::-webkit-scrollbar-thumb{background:var(--ts-overlay);border-radius:3px}
.ts-scroll::-webkit-scrollbar-thumb:hover{background:var(--ts-border)}

/* ── Layout ────────────────────────────────────────────── */
#ts-app{display:flex;flex-direction:column;height:100vh;overflow:hidden}

/* ── Top Bar ───────────────────────────────────────────── */
#ts-topbar{
  display:flex;align-items:center;gap:12px;
  height:var(--ts-topbar-h);min-height:var(--ts-topbar-h);
  padding:0 16px;background:var(--ts-surface);
  border-bottom:1px solid var(--ts-border);
  z-index:100;user-select:none;
}
#ts-topbar a,#ts-topbar button{color:var(--ts-text);text-decoration:none}

.ts-back{
  display:inline-flex;align-items:center;gap:6px;
  font-size:13px;color:var(--ts-subtext)!important;
  transition:color var(--ts-transition);
}
.ts-back:hover{color:var(--ts-blue)!important}
.ts-back svg{width:16px;height:16px}

.ts-topbar-title{
  font-size:15px;font-weight:600;white-space:nowrap;
  display:flex;align-items:center;gap:8px;
}
.ts-topbar-title .ts-theme-name{color:var(--ts-mauve)}

.ts-topbar-sep{width:1px;height:24px;background:var(--ts-border);flex-shrink:0}
.ts-topbar-spacer{flex:1}

/* ── Save status indicator ─────────────────────────────── */
.ts-save-indicator{
  font-size:12px;color:var(--ts-subtext);
  display:flex;align-items:center;gap:6px;
  transition:opacity var(--ts-transition);
}
.ts-save-indicator.saving{color:var(--ts-yellow)}
.ts-save-indicator.saved{color:var(--ts-green)}
.ts-save-indicator.error{color:var(--ts-red)}
.ts-save-indicator .ts-dot{
  width:8px;height:8px;border-radius:50%;
  background:currentColor;
}

/* ── Icon button (topbar) ──────────────────────────────── */
.ts-icon-btn{
  display:inline-flex;align-items:center;justify-content:center;
  width:34px;height:34px;border:1px solid var(--ts-border);border-radius:var(--ts-radius-sm);
  background:transparent;cursor:pointer;transition:all var(--ts-transition);
  position:relative;color:var(--ts-subtext);font-size:0;
}
.ts-icon-btn:hover{background:var(--ts-overlay);color:var(--ts-text)}
.ts-icon-btn.active{background:var(--ts-blue);color:var(--ts-bg);border-color:var(--ts-blue)}
.ts-icon-btn:disabled{opacity:0.35;cursor:default;pointer-events:none}
.ts-icon-btn svg{width:16px;height:16px}

.ts-btn-group{display:flex;gap:4px}

/* Device toggles */
.ts-device-btns{display:flex;gap:2px}
.ts-device-btns .ts-icon-btn{border:none;width:32px;height:32px}

/* ── Primary / Danger / Ghost buttons ──────────────────── */
.ts-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:7px 16px;border-radius:var(--ts-radius-sm);
  font-size:13px;font-weight:600;cursor:pointer;
  border:1px solid transparent;transition:all var(--ts-transition);
  white-space:nowrap;font-family:inherit;
}
.ts-btn-primary{background:var(--ts-blue);color:var(--ts-bg)}
.ts-btn-primary:hover{background:#a8c7fc;box-shadow:0 0 12px rgba(137,180,250,.3)}
.ts-btn-danger{background:transparent;color:var(--ts-red);border-color:var(--ts-red)}
.ts-btn-danger:hover{background:var(--ts-red);color:var(--ts-bg)}
.ts-btn-ghost{background:transparent;color:var(--ts-subtext);border-color:var(--ts-border)}
.ts-btn-ghost:hover{background:var(--ts-overlay);color:var(--ts-text)}
.ts-btn-sm{padding:5px 12px;font-size:12px}

/* ── Main Body ─────────────────────────────────────────── */
#ts-body{
  display:flex;flex:1;overflow:hidden;
  height:calc(100vh - var(--ts-topbar-h));
}

/* ── Control Panel (left sidebar) ──────────────────────── */
#ts-panel{
  width:var(--ts-panel-w);min-width:var(--ts-panel-w);
  display:flex;flex-direction:column;
  background:var(--ts-surface);border-right:1px solid var(--ts-border);
  overflow:hidden;
}
#ts-panel-sections{flex:1;overflow-y:auto;padding:8px 0}

/* ── Accordion Sections ────────────────────────────────── */
.ts-section{border-bottom:1px solid var(--ts-border)}
.ts-section:last-child{border-bottom:none}

.ts-section-header{
  display:flex;align-items:center;gap:10px;
  padding:14px 16px;cursor:pointer;
  transition:background var(--ts-transition);
  user-select:none;
}
.ts-section-header:hover{background:rgba(69,71,90,.5)}
.ts-section-header .ts-section-icon{font-size:18px;flex-shrink:0;width:24px;text-align:center}
.ts-section-header .ts-section-label{font-size:13px;font-weight:600;flex:1}
.ts-section-header .ts-section-chevron{
  width:18px;height:18px;color:var(--ts-subtext);
  transition:transform 0.25s cubic-bezier(0.4,0,0.2,1);flex-shrink:0;
}
.ts-section.open .ts-section-header .ts-section-chevron{transform:rotate(90deg)}
.ts-section.open .ts-section-header{background:rgba(69,71,90,.3)}

.ts-section-body{
  max-height:0;overflow:hidden;
  transition:max-height 0.35s cubic-bezier(0.4,0,0.2,1);
}
.ts-section.open .ts-section-body{max-height:2000px}

.ts-section-fields{padding:4px 16px 16px}

/* ── Field common ──────────────────────────────────────── */
.ts-field{margin-bottom:14px}
.ts-field:last-child{margin-bottom:0}
.ts-field-label{
  display:block;font-size:11px;font-weight:700;
  color:var(--ts-subtext);margin-bottom:6px;
  text-transform:uppercase;letter-spacing:0.6px;
}

/* ── Text input ────────────────────────────────────────── */
.ts-input{
  width:100%;padding:9px 12px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;font-family:inherit;
  transition:border-color var(--ts-transition),box-shadow var(--ts-transition);
  outline:none;
}
.ts-input:focus{
  border-color:var(--ts-blue);
  box-shadow:0 0 0 3px rgba(137,180,250,.15);
}
.ts-input::placeholder{color:var(--ts-overlay)}

/* ── Textarea ──────────────────────────────────────────── */
.ts-textarea{
  width:100%;padding:9px 12px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;font-family:inherit;line-height:1.5;
  transition:border-color var(--ts-transition),box-shadow var(--ts-transition);
  outline:none;resize:vertical;min-height:72px;
}
.ts-textarea:focus{
  border-color:var(--ts-blue);
  box-shadow:0 0 0 3px rgba(137,180,250,.15);
}

/* ── Select Dropdown ───────────────────────────────────── */
.ts-select-wrap{position:relative}
.ts-select{
  width:100%;padding:9px 32px 9px 12px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;font-family:inherit;cursor:pointer;
  appearance:none;outline:none;
  transition:border-color var(--ts-transition);
}
.ts-select:focus{border-color:var(--ts-blue)}
.ts-select-wrap::after{
  content:'';position:absolute;right:12px;top:50%;
  transform:translateY(-50%);
  border:5px solid transparent;border-top-color:var(--ts-subtext);
  pointer-events:none;
}

/* ── Toggle Switch ─────────────────────────────────────── */
.ts-toggle-wrap{display:flex;align-items:center;gap:10px}
.ts-toggle{
  position:relative;width:44px;height:24px;flex-shrink:0;
  cursor:pointer;display:block;
}
.ts-toggle input{position:absolute;opacity:0;width:0;height:0}
.ts-toggle-track{
  position:absolute;inset:0;border-radius:12px;
  background:var(--ts-overlay);
  transition:background 0.25s ease;
}
.ts-toggle input:checked + .ts-toggle-track{background:var(--ts-blue)}
.ts-toggle-thumb{
  position:absolute;top:3px;left:3px;width:18px;height:18px;
  border-radius:50%;background:#fff;
  box-shadow:0 1px 3px rgba(0,0,0,.3);
  transition:transform 0.25s cubic-bezier(0.4,0,0.2,1);
  pointer-events:none;
}
.ts-toggle input:checked ~ .ts-toggle-thumb{transform:translateX(20px)}
.ts-toggle-label-text{font-size:13px;color:var(--ts-text)}

/* ── Color Picker Field ───────────────────────────────── */
.ts-color-field{display:flex;align-items:center;gap:10px}
.ts-color-swatch{
  width:40px;height:40px;border-radius:var(--ts-radius-sm);
  border:2px solid var(--ts-border);cursor:pointer;
  transition:border-color var(--ts-transition),box-shadow var(--ts-transition);
  flex-shrink:0;
}
.ts-color-swatch:hover{border-color:var(--ts-blue);box-shadow:0 0 8px rgba(137,180,250,.25)}
.ts-color-hex{
  width:100px;padding:8px 10px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;font-family:'SF Mono',Monaco,Consolas,monospace;
  outline:none;transition:border-color var(--ts-transition);
}
.ts-color-hex:focus{border-color:var(--ts-blue)}

/* ── Color Picker Popover ──────────────────────────────── */
.ts-cpicker{
  position:fixed;z-index:1000;
  width:260px;padding:16px;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius);
  box-shadow:0 12px 40px rgba(0,0,0,.5);
  display:none;
}
.ts-cpicker.open{display:block}

.ts-cpicker-sat{
  position:relative;width:100%;height:150px;
  border-radius:var(--ts-radius-sm);cursor:crosshair;
  overflow:hidden;margin-bottom:12px;
}
.ts-cpicker-sat-white{
  position:absolute;inset:0;
  background:linear-gradient(to right,#fff,transparent);
}
.ts-cpicker-sat-black{
  position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent,#000);
}
.ts-cpicker-sat-cursor{
  position:absolute;width:14px;height:14px;
  border:2px solid #fff;border-radius:50%;
  box-shadow:0 0 4px rgba(0,0,0,.5),inset 0 0 2px rgba(0,0,0,.3);
  transform:translate(-50%,-50%);pointer-events:none;
}

.ts-cpicker-hue{
  position:relative;width:100%;height:14px;
  border-radius:7px;cursor:pointer;margin-bottom:12px;
  background:linear-gradient(to right,
    #f00 0%,#ff0 17%,#0f0 33%,#0ff 50%,#00f 67%,#f0f 83%,#f00 100%);
}
.ts-cpicker-hue-cursor{
  position:absolute;top:-2px;width:18px;height:18px;
  border:2px solid #fff;border-radius:50%;
  box-shadow:0 0 4px rgba(0,0,0,.5);
  transform:translateX(-50%);pointer-events:none;
  background:currentColor;
}

.ts-cpicker-input-row{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.ts-cpicker-input-row .ts-color-hex{flex:1}
.ts-cpicker-preview{
  width:32px;height:32px;border-radius:var(--ts-radius-sm);
  border:1px solid var(--ts-border);flex-shrink:0;
}

.ts-cpicker-swatches{display:flex;flex-wrap:wrap;gap:6px}
.ts-cpicker-sw{
  width:22px;height:22px;border-radius:4px;cursor:pointer;
  border:2px solid transparent;transition:all var(--ts-transition);
}
.ts-cpicker-sw:hover{border-color:var(--ts-text);transform:scale(1.15)}

/* ── Image Upload Field ────────────────────────────────── */
.ts-image-field{}
.ts-image-preview{
  position:relative;margin-bottom:8px;
  border-radius:var(--ts-radius-sm);overflow:hidden;
  border:1px solid var(--ts-border);
  display:none;
}
.ts-image-preview.has-image{display:block}
.ts-image-preview img{
  display:block;width:100%;max-height:140px;object-fit:cover;
  background:var(--ts-bg);
}
.ts-image-remove{
  position:absolute;top:6px;right:6px;
  width:26px;height:26px;border-radius:50%;
  background:rgba(0,0,0,.7);color:#fff;border:none;
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  font-size:14px;transition:background var(--ts-transition);
  line-height:1;
}
.ts-image-remove:hover{background:var(--ts-red)}

.ts-image-drop{
  padding:24px 16px;border:2px dashed var(--ts-border);
  border-radius:var(--ts-radius-sm);text-align:center;
  cursor:pointer;transition:all var(--ts-transition);
}
.ts-image-drop:hover,.ts-image-drop.dragover{
  border-color:var(--ts-blue);background:rgba(137,180,250,.05);
}
.ts-image-drop-icon{font-size:24px;margin-bottom:6px;color:var(--ts-subtext)}
.ts-image-drop-text{font-size:12px;color:var(--ts-subtext)}
.ts-image-drop-text span{color:var(--ts-blue);text-decoration:underline;cursor:pointer}
.ts-image-drop input[type="file"]{display:none}

/* Uploading state */
.ts-image-drop.uploading{
  pointer-events:none;opacity:0.6;
}
.ts-image-drop.uploading::after{
  content:'Uploading…';display:block;margin-top:6px;
  color:var(--ts-yellow);font-size:12px;
  animation:ts-pulse 1.5s ease-in-out infinite;
}
@keyframes ts-pulse{0%,100%{opacity:1}50%{opacity:.5}}



/* ── Preview Area (right side) ─────────────────────────── */
#ts-preview{
  flex:1;display:flex;align-items:center;justify-content:center;
  background:var(--ts-bg);padding:16px;overflow:hidden;
  position:relative;
}

.ts-preview-container{
  position:relative;transition:all 0.4s cubic-bezier(0.4,0,0.2,1);
  height:100%;background:#fff;border-radius:var(--ts-radius);
  overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.3);
}
.ts-preview-container.desktop{width:100%;max-width:100%;border-radius:var(--ts-radius)}
.ts-preview-container.tablet{width:768px;max-width:768px}
.ts-preview-container.mobile{width:375px;max-width:375px}

/* Device frame styling for tablet/mobile */
.ts-preview-container.tablet{
  border:3px solid var(--ts-overlay);border-radius:20px;
}
.ts-preview-container.mobile{
  border:3px solid var(--ts-overlay);border-radius:32px;
  padding-top:24px;padding-bottom:20px;
}

.ts-preview-notch{
  display:none;position:absolute;top:6px;left:50%;transform:translateX(-50%);
  width:80px;height:6px;background:var(--ts-overlay);
  border-radius:3px;z-index:5;
}
.ts-preview-container.mobile .ts-preview-notch{display:block}

.ts-preview-home{
  display:none;position:absolute;bottom:4px;left:50%;transform:translateX(-50%);
  width:40px;height:5px;background:var(--ts-overlay);
  border-radius:3px;z-index:5;
}
.ts-preview-container.mobile .ts-preview-home{display:block}

#ts-iframe{
  width:100%;height:100%;border:none;
  background:#fff;display:block;
}

/* Preview loading overlay */
.ts-preview-loading{
  position:absolute;inset:0;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:12px;background:var(--ts-bg);z-index:10;
  transition:opacity 0.4s ease;
}
.ts-preview-loading.hidden{opacity:0;pointer-events:none}
.ts-preview-spinner{
  width:36px;height:36px;border:3px solid var(--ts-overlay);
  border-top-color:var(--ts-blue);border-radius:50%;
  animation:ts-spin 0.8s linear infinite;
}
.ts-preview-loading-text{font-size:12px;color:var(--ts-subtext)}
@keyframes ts-spin{to{transform:rotate(360deg)}}

/* ── AI Chat Panel (bottom of sidebar) ─────────────────── */
#ts-ai-panel{
  border-top:1px solid var(--ts-border);
  background:var(--ts-bg);
  display:flex;flex-direction:column;
  max-height:50%;
  transition:max-height 0.3s ease;
}
#ts-ai-panel.collapsed{max-height:42px;overflow:hidden}

.ts-ai-header{
  display:flex;align-items:center;gap:8px;
  padding:10px 16px;cursor:pointer;
  font-size:13px;font-weight:600;
  user-select:none;flex-shrink:0;
  transition:background var(--ts-transition);
}
.ts-ai-header:hover{background:var(--ts-overlay)}
.ts-ai-header .ts-ai-icon{font-size:16px}
.ts-ai-header .ts-ai-label{flex:1}
.ts-ai-header .ts-ai-chevron{
  width:14px;height:14px;
  color:var(--ts-subtext);transition:transform 0.25s ease;
}
#ts-ai-panel:not(.collapsed) .ts-ai-chevron{transform:rotate(180deg)}

.ts-ai-messages{
  flex:1;overflow-y:auto;padding:8px 12px;
  display:flex;flex-direction:column;gap:8px;
  min-height:60px;
}

.ts-ai-welcome{
  text-align:center;padding:16px 8px;
  color:var(--ts-subtext);font-size:12px;line-height:1.6;
}
.ts-ai-welcome-icon{font-size:28px;margin-bottom:8px;display:block}

.ts-ai-msg{
  padding:10px 12px;border-radius:var(--ts-radius-sm);
  font-size:13px;line-height:1.5;max-width:95%;
  word-wrap:break-word;
}
.ts-ai-msg.user{
  align-self:flex-end;
  background:var(--ts-blue);color:var(--ts-bg);
  border-bottom-right-radius:2px;
}
.ts-ai-msg.assistant{
  align-self:flex-start;
  background:var(--ts-surface);color:var(--ts-text);
  border-bottom-left-radius:2px;
}

/* AI change list */
.ts-ai-changes{
  margin-top:8px;padding-top:8px;
  border-top:1px solid var(--ts-border);
}
.ts-ai-change-item{
  display:flex;align-items:center;gap:5px;
  font-size:12px;color:var(--ts-subtext);
  padding:3px 0;flex-wrap:wrap;
}
.ts-ai-change-section{color:var(--ts-mauve);font-weight:700;font-size:11px}
.ts-ai-change-field{color:var(--ts-text)}
.ts-ai-change-arrow{color:var(--ts-subtext);font-size:10px}
.ts-ai-change-value{
  color:var(--ts-green);
  font-family:'SF Mono',Monaco,Consolas,monospace;
  font-size:11px;
  background:rgba(166,227,161,.1);
  padding:1px 4px;border-radius:3px;
}

.ts-ai-actions{display:flex;gap:6px;margin-top:10px}

/* AI typing indicator */
.ts-ai-typing{
  display:flex;align-items:center;gap:4px;
  padding:10px 12px;align-self:flex-start;
}
.ts-ai-typing-dot{
  width:6px;height:6px;border-radius:50%;
  background:var(--ts-subtext);
  animation:ts-typing 1.4s ease-in-out infinite;
}
.ts-ai-typing-dot:nth-child(2){animation-delay:0.2s}
.ts-ai-typing-dot:nth-child(3){animation-delay:0.4s}
@keyframes ts-typing{
  0%,60%,100%{transform:translateY(0);opacity:0.4}
  30%{transform:translateY(-6px);opacity:1}
}

/* AI suggestion chips */
.ts-ai-chips{
  display:flex;flex-wrap:wrap;gap:6px;
  padding:4px 12px 8px;flex-shrink:0;
}
.ts-ai-chip{
  padding:5px 12px;border-radius:20px;
  background:var(--ts-overlay);color:var(--ts-subtext);
  font-size:11px;cursor:pointer;border:1px solid transparent;
  transition:all var(--ts-transition);font-family:inherit;
}
.ts-ai-chip:hover{background:var(--ts-blue);color:var(--ts-bg);border-color:var(--ts-blue)}

/* AI Model Selector */
.ts-ai-model-bar{
  display:flex;align-items:center;gap:6px;
  padding:8px 12px;flex-shrink:0;
  border-bottom:1px solid var(--ts-border);
  background:rgba(49,50,68,.5);
}
.ts-ai-model-bar label{
  font-size:11px;color:var(--ts-subtext);font-weight:500;
  white-space:nowrap;
}
.ts-ai-model-select{
  flex:1;padding:5px 28px 5px 8px;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:12px;font-family:inherit;outline:none;
  cursor:pointer;appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23a6adc8' d='M0 0l5 6 5-6z'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 8px center;
  transition:border-color var(--ts-transition);
  min-width:0;
}
.ts-ai-model-select:focus{border-color:var(--ts-blue)}
.ts-ai-model-select optgroup{
  background:var(--ts-surface);color:var(--ts-subtext);
  font-weight:700;font-size:11px;
}
.ts-ai-model-select option{
  background:var(--ts-bg);color:var(--ts-text);
  padding:4px 8px;font-size:12px;
}
.ts-ai-model-info{
  font-size:10px;color:var(--ts-subtext);
  display:flex;align-items:center;gap:4px;
  white-space:nowrap;
}
.ts-ai-model-tier{
  display:inline-block;padding:1px 5px;border-radius:3px;
  font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;
}
.ts-ai-model-tier.fast{background:rgba(166,227,161,.15);color:var(--ts-green)}
.ts-ai-model-tier.pro{background:rgba(137,180,250,.15);color:var(--ts-blue)}
.ts-ai-model-tier.premium{background:rgba(203,166,247,.15);color:var(--ts-mauve)}
.ts-ai-model-tier.reasoning{background:rgba(249,226,175,.15);color:var(--ts-yellow)}
.ts-ai-model-tier.recommended{background:rgba(250,179,135,.15);color:var(--ts-peach)}
.ts-ai-model-tier.legacy{background:rgba(88,91,112,.2);color:var(--ts-overlay)}
.ts-ai-model-tier.standard{background:rgba(137,180,250,.1);color:var(--ts-subtext)}

/* AI usage indicator */
.ts-ai-usage{
  font-size:10px;color:var(--ts-subtext);
  text-align:right;padding:2px 12px 0;
  display:none;
}
.ts-ai-usage.visible{display:block}

/* AI input */
.ts-ai-input-row{
  display:flex;gap:8px;padding:10px 12px;flex-shrink:0;
  border-top:1px solid var(--ts-border);
}
.ts-ai-input{
  flex:1;padding:9px 12px;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;font-family:inherit;outline:none;
  transition:border-color var(--ts-transition);
}
.ts-ai-input:focus{border-color:var(--ts-blue)}
.ts-ai-input::placeholder{color:var(--ts-overlay)}
.ts-ai-send{
  width:36px;height:36px;border-radius:var(--ts-radius-sm);
  background:var(--ts-blue);color:var(--ts-bg);border:none;
  cursor:pointer;display:flex;align-items:center;justify-content:center;
  transition:all var(--ts-transition);flex-shrink:0;
  font-size:0;
}
.ts-ai-send:hover{background:#a8c7fc}
.ts-ai-send:disabled{opacity:0.4;cursor:default}
.ts-ai-send svg{width:16px;height:16px}

/* ── History Dropdown ──────────────────────────────────── */
.ts-history-dropdown{
  position:absolute;top:calc(var(--ts-topbar-h) + 4px);
  right:200px;width:320px;max-height:380px;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius);overflow:hidden;
  box-shadow:0 12px 40px rgba(0,0,0,.5);
  z-index:200;display:none;
}
.ts-history-dropdown.open{display:flex;flex-direction:column}

.ts-history-dropdown-title{
  padding:12px 16px;font-size:13px;font-weight:700;
  border-bottom:1px solid var(--ts-border);
  display:flex;align-items:center;justify-content:space-between;
  flex-shrink:0;
}
.ts-history-list{overflow-y:auto;flex:1}
.ts-history-item{
  display:flex;align-items:center;gap:10px;
  padding:10px 16px;cursor:pointer;
  transition:background var(--ts-transition);
  border-bottom:1px solid rgba(88,91,112,.3);
}
.ts-history-item:hover{background:var(--ts-overlay)}
.ts-history-item:last-child{border-bottom:none}
.ts-history-dot{
  width:8px;height:8px;border-radius:50%;
  background:var(--ts-blue);flex-shrink:0;
}
.ts-history-info{flex:1;min-width:0}
.ts-history-label{font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ts-history-date{font-size:11px;color:var(--ts-subtext);margin-top:2px}
.ts-history-empty{
  padding:24px 16px;text-align:center;
  color:var(--ts-subtext);font-size:13px;
}

/* ── Toast Notifications ───────────────────────────────── */
#ts-toasts{
  position:fixed;bottom:20px;right:20px;
  display:flex;flex-direction:column-reverse;gap:8px;
  z-index:9999;pointer-events:none;
}
.ts-toast{
  display:flex;align-items:center;gap:10px;
  padding:12px 18px;border-radius:var(--ts-radius);
  font-size:13px;font-weight:500;
  pointer-events:auto;cursor:pointer;
  animation:ts-toast-in 0.3s ease;
  box-shadow:0 8px 24px rgba(0,0,0,.4);
  max-width:360px;
}
.ts-toast.out{animation:ts-toast-out 0.3s ease forwards}
.ts-toast-success{background:var(--ts-green);color:var(--ts-bg)}
.ts-toast-error{background:var(--ts-red);color:var(--ts-bg)}
.ts-toast-info{background:var(--ts-blue);color:var(--ts-bg)}
.ts-toast-icon{font-size:16px;flex-shrink:0}
@keyframes ts-toast-in{from{transform:translateX(80px);opacity:0}to{transform:none;opacity:1}}
@keyframes ts-toast-out{to{transform:translateX(80px);opacity:0}}

/* ── Confirm Modal ─────────────────────────────────────── */
.ts-modal-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.6);
  display:flex;align-items:center;justify-content:center;
  z-index:5000;opacity:0;pointer-events:none;
  transition:opacity 0.2s ease;
  backdrop-filter:blur(4px);
}
.ts-modal-overlay.open{opacity:1;pointer-events:auto}
.ts-modal{
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius);padding:24px;
  width:420px;max-width:90vw;
  box-shadow:0 16px 48px rgba(0,0,0,.5);
  transform:scale(0.95);transition:transform 0.2s ease;
}
.ts-modal-overlay.open .ts-modal{transform:scale(1)}
.ts-modal-title{font-size:16px;font-weight:700;margin-bottom:8px}
.ts-modal-text{font-size:13px;color:var(--ts-subtext);margin-bottom:20px;line-height:1.5}
.ts-modal-actions{display:flex;gap:8px;justify-content:flex-end}

/* ── Tab Navigation ────────────────────────────────────── */
.ts-tab-nav{
  display:flex;border-bottom:1px solid var(--ts-border);
  background:var(--ts-bg);flex-shrink:0;
}
.ts-tab-btn{
  flex:1;padding:10px 12px;
  background:transparent;border:none;
  color:var(--ts-subtext);font-size:12px;font-weight:600;
  cursor:pointer;transition:all var(--ts-transition);
  border-bottom:2px solid transparent;font-family:inherit;
  display:flex;align-items:center;justify-content:center;gap:6px;
}
.ts-tab-btn:hover{color:var(--ts-text);background:rgba(69,71,90,.3)}
.ts-tab-btn.active{
  color:var(--ts-blue);border-bottom-color:var(--ts-blue);
  background:rgba(137,180,250,.05);
}
.ts-tab-content{display:none;flex:1;overflow-y:auto}
.ts-tab-content.active{display:block}

/* ── Section Manager ───────────────────────────────────── */
.ts-sections-panel{padding:0}
.ts-sections-header{
  padding:16px;border-bottom:1px solid var(--ts-border);
}
.ts-sections-header h3{
  font-size:14px;font-weight:700;color:var(--ts-text);margin-bottom:4px;
}
.ts-sections-header p{
  font-size:12px;color:var(--ts-subtext);
}
.ts-sections-list{
  padding:8px;min-height:100px;
}
.ts-section-item{
  display:flex;align-items:center;gap:10px;
  padding:10px 12px;margin-bottom:4px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);
  cursor:grab;transition:all 0.15s ease;
  user-select:none;
}
.ts-section-item:active{cursor:grabbing}
.ts-section-item:hover{border-color:var(--ts-blue);background:rgba(137,180,250,.03)}
.ts-section-item.dragging{
  opacity:0.5;border-color:var(--ts-blue);
  box-shadow:0 4px 12px rgba(0,0,0,.3);
}
.ts-section-item.drag-over{
  border-color:var(--ts-green);
  box-shadow:0 0 0 2px rgba(166,227,161,.2);
}
.ts-section-item.disabled{opacity:0.5}
.ts-section-item.disabled .ts-section-item-label{color:var(--ts-subtext);text-decoration:line-through}

.ts-section-item-handle{
  color:var(--ts-overlay);font-size:16px;flex-shrink:0;
  cursor:grab;width:20px;text-align:center;
  line-height:1;letter-spacing:-1px;
}
.ts-section-item-icon{font-size:18px;flex-shrink:0;width:24px;text-align:center}
.ts-section-item-label{
  flex:1;font-size:13px;font-weight:500;color:var(--ts-text);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.ts-section-item-badge{
  font-size:9px;font-weight:700;text-transform:uppercase;
  padding:2px 6px;border-radius:3px;
  background:rgba(203,166,247,.15);color:var(--ts-mauve);
  letter-spacing:0.5px;flex-shrink:0;
}

/* Section toggle switch (compact) */
.ts-section-toggle{
  position:relative;width:36px;height:20px;flex-shrink:0;
  cursor:pointer;display:block;
}
.ts-section-toggle input{position:absolute;opacity:0;width:0;height:0}
.ts-section-toggle-track{
  position:absolute;inset:0;border-radius:10px;
  background:var(--ts-overlay);
  transition:background 0.25s ease;
}
.ts-section-toggle input:checked + .ts-section-toggle-track{background:var(--ts-green)}
.ts-section-toggle-thumb{
  position:absolute;top:2px;left:2px;width:16px;height:16px;
  border-radius:50%;background:#fff;
  box-shadow:0 1px 2px rgba(0,0,0,.3);
  transition:transform 0.25s cubic-bezier(0.4,0,0.2,1);
  pointer-events:none;
}
.ts-section-toggle input:checked ~ .ts-section-toggle-thumb{transform:translateX(16px)}
.ts-section-toggle input:disabled{cursor:not-allowed}
.ts-section-toggle input:disabled + .ts-section-toggle-track{background:var(--ts-green);opacity:0.6}
.ts-section-toggle input:disabled ~ .ts-section-toggle-thumb{transform:translateX(16px)}

.ts-sections-save{
  display:flex;align-items:center;justify-content:center;gap:8px;
  width:calc(100% - 16px);margin:8px;padding:10px 16px;
  background:var(--ts-blue);color:var(--ts-bg);border:none;
  border-radius:var(--ts-radius-sm);font-size:13px;font-weight:600;
  cursor:pointer;transition:all var(--ts-transition);
  font-family:inherit;
}
.ts-sections-save:hover{background:#a8c7fc;box-shadow:0 0 12px rgba(137,180,250,.3)}
.ts-sections-save:disabled{opacity:0.5;cursor:default}
.ts-sections-save.saved{background:var(--ts-green)}

/* Section inline editor */
.ts-section-item-wrap{margin-bottom:4px}
.ts-section-item-wrap .ts-section-item{margin-bottom:0;border-radius:var(--ts-radius-sm) var(--ts-radius-sm) 0 0}
.ts-section-item-wrap.collapsed .ts-section-item{border-radius:var(--ts-radius-sm)}
.ts-section-edit-btn{
  background:none;border:none;cursor:pointer;padding:4px;
  color:var(--ts-subtext);font-size:14px;flex-shrink:0;
  transition:color 0.15s;border-radius:4px;width:28px;height:28px;
  display:flex;align-items:center;justify-content:center;
}
.ts-section-edit-btn:hover{color:var(--ts-blue);background:rgba(137,180,250,.1)}
.ts-section-item-wrap:not(.collapsed) .ts-section-edit-btn{color:var(--ts-blue)}
.ts-section-editor{
  display:none;padding:12px 14px 14px;
  background:rgba(30,30,46,.6);border:1px solid var(--ts-border);
  border-top:none;border-radius:0 0 var(--ts-radius-sm) var(--ts-radius-sm);
}
.ts-section-item-wrap:not(.collapsed) .ts-section-editor{display:block}
.ts-section-editor .ts-field{margin-bottom:10px}
.ts-section-editor .ts-field:last-child{margin-bottom:0}
.ts-section-editor .ts-field label{
  display:block;font-size:11px;font-weight:600;color:var(--ts-subtext);
  margin-bottom:4px;text-transform:uppercase;letter-spacing:0.3px;
}
.ts-section-editor .ts-field input[type="text"],
.ts-section-editor .ts-field textarea{
  width:100%;padding:7px 10px;font-size:13px;font-family:inherit;
  background:var(--ts-mantle);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  transition:border-color 0.15s;
}
.ts-section-editor .ts-field input:focus,
.ts-section-editor .ts-field textarea:focus{
  border-color:var(--ts-blue);outline:none;
  box-shadow:0 0 0 2px rgba(137,180,250,.15);
}
.ts-section-editor .ts-field textarea{min-height:60px;resize:vertical}
.ts-section-editor .ts-field .ts-img-field{
  display:flex;align-items:center;gap:8px;
}
.ts-section-editor .ts-field .ts-img-preview{
  width:48px;height:48px;border-radius:6px;border:1px solid var(--ts-border);
  background:var(--ts-mantle);overflow:hidden;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:18px;color:var(--ts-overlay);
}
.ts-section-editor .ts-field .ts-img-preview img{width:100%;height:100%;object-fit:cover}
.ts-section-editor .ts-field .ts-img-btn{
  padding:5px 10px;font-size:11px;font-weight:600;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  cursor:pointer;transition:all 0.15s;font-family:inherit;
}
.ts-section-editor .ts-field .ts-img-btn:hover{border-color:var(--ts-blue);color:var(--ts-blue)}
.ts-section-editor-actions{
  display:flex;gap:8px;margin-top:12px;padding-top:10px;
  border-top:1px solid var(--ts-border);
}
.ts-section-editor-save{
  flex:1;padding:7px 12px;font-size:12px;font-weight:600;
  background:var(--ts-green);color:var(--ts-bg);border:none;
  border-radius:var(--ts-radius-sm);cursor:pointer;font-family:inherit;
  transition:all 0.15s;
}
.ts-section-editor-save:hover{opacity:0.9}
.ts-section-editor-save:disabled{opacity:0.5;cursor:default}
.ts-section-no-fields{
  padding:16px;text-align:center;font-size:12px;color:var(--ts-subtext);
  font-style:italic;
}

/* ── Responsive ────────────────────────────────────────── */
/* ── Color Presets ──────────────────────────────────────── */
.ts-presets{padding:12px 16px;border-bottom:1px solid var(--ts-border)}
.ts-presets-title{
  font-size:11px;font-weight:700;color:var(--ts-subtext);
  text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;
}
.ts-presets-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.ts-preset-btn{
  display:flex;flex-direction:column;align-items:center;gap:6px;
  padding:10px 6px;background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);cursor:pointer;
  transition:all var(--ts-transition);font-family:inherit;
  color:var(--ts-text);
}
.ts-preset-btn:hover{border-color:var(--ts-blue);background:var(--ts-overlay)}
.ts-preset-dots{display:flex;gap:3px}
.ts-preset-dot{width:12px;height:12px;border-radius:50%;border:1px solid rgba(255,255,255,.15)}
.ts-preset-label{font-size:10px;color:var(--ts-subtext);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}

@media(max-width:900px){
  :root{--ts-panel-w:280px}
}
</style>
<link rel="stylesheet" href="/plugins/jessie-theme-builder/assets/css/media-gallery.css">
</head>
<body>

<!-- ═════════════════════════════════════════════════════════
     APP SHELL
     ═════════════════════════════════════════════════════════ -->
<div id="ts-app">

  <!-- ── Top Bar ───────────────────────────────────────── -->
  <header id="ts-topbar">
    <a href="/admin" class="ts-back" title="Back to Admin">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
      <span>Admin</span>
    </a>

    <div class="ts-topbar-sep"></div>

    <div class="ts-topbar-title">
      <span>Theme Studio:</span>
      <span class="ts-theme-name"><?= htmlspecialchars($themeName ?? 'Theme') ?></span>
    </div>

    <div class="ts-save-indicator" id="ts-save-status">
      <span class="ts-dot"></span>
      <span class="ts-save-text">Ready</span>
    </div>

    <div class="ts-topbar-spacer"></div>

    <!-- Undo / Redo / History -->
    <div class="ts-btn-group">
      <button class="ts-icon-btn" id="ts-undo" title="Undo (Ctrl+Z)" disabled>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 00-9-9 9 9 0 00-6.69 3L3 13"/></svg>
      </button>
      <button class="ts-icon-btn" id="ts-redo" title="Redo (Ctrl+Shift+Z)" disabled>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 019-9 9 9 0 016.69 3L21 13"/></svg>
      </button>
      <button class="ts-icon-btn" id="ts-history-btn" title="History">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </button>
    </div>

    <div class="ts-topbar-sep"></div>

    <!-- Device preview toggles -->
    <div class="ts-device-btns">
      <button class="ts-icon-btn active" data-device="desktop" title="Desktop preview">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      </button>
      <button class="ts-icon-btn" data-device="tablet" title="Tablet preview (768px)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
      </button>
      <button class="ts-icon-btn" data-device="mobile" title="Mobile preview (375px)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
      </button>
    </div>

    <div class="ts-topbar-sep"></div>

    <button class="ts-btn ts-btn-ghost" id="ts-reset-btn">Reset</button>
    <button class="ts-btn ts-btn-primary" id="ts-publish-btn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
      Publish
    </button>
  </header>

  <!-- ── History Dropdown ──────────────────────────────── -->
  <div class="ts-history-dropdown" id="ts-history-dropdown">
    <div class="ts-history-dropdown-title">
      <span>📜 History</span>
      <button class="ts-icon-btn" id="ts-history-close" style="width:26px;height:26px;border:none">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="ts-history-list ts-scroll" id="ts-history-list"></div>
  </div>

  <!-- ── Main Body ─────────────────────────────────────── -->
  <div id="ts-body">

    <!-- ── Control Panel (left sidebar) ────────────────── -->
    <aside id="ts-panel">
      <!-- Tab Navigation -->
      <div class="ts-tab-nav" id="ts-tab-nav">
        <button class="ts-tab-btn active" data-tab="design" title="Design">🎨 Design</button>
        <button class="ts-tab-btn" data-tab="sections" title="Sections">📐 Sections</button>
      </div>

      <!-- Design Tab (existing accordion) -->
      <div id="ts-panel-sections" class="ts-scroll ts-tab-content active" data-tab="design"></div>

      <!-- Sections Tab (Section Manager) -->
      <div id="ts-sections-tab" class="ts-scroll ts-tab-content" data-tab="sections" style="display:none">
        <div class="ts-sections-panel" id="sections-panel">
          <div class="ts-sections-header">
            <h3>Homepage Sections</h3>
            <p>Drag to reorder, toggle to show/hide</p>
          </div>
          <div class="ts-sections-list" id="sections-list">
            <!-- Populated by JS -->
          </div>
          <button class="ts-sections-save" id="sections-save-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Save Section Order
          </button>
        </div>
      </div>

      <!-- AI Chat (shows only if AI is configured) -->
      <?php if (!empty($aiAvailable)): ?>
      <div id="ts-ai-panel" class="collapsed">
        <div class="ts-ai-header" id="ts-ai-toggle">
          <span class="ts-ai-icon">🤖</span>
          <span class="ts-ai-label">AI Assistant</span>
          <svg class="ts-ai-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        </div>
        <!-- Model Selector Bar -->
        <div class="ts-ai-model-bar" id="ts-ai-model-bar">
          <label for="ts-ai-model">Model:</label>
          <select class="ts-ai-model-select" id="ts-ai-model">
            <option value="">Loading models…</option>
          </select>
          <span class="ts-ai-model-info" id="ts-ai-model-info"></span>
        </div>
        <div class="ts-ai-messages ts-scroll" id="ts-ai-messages">
          <div class="ts-ai-welcome">
            <span class="ts-ai-welcome-icon">✨</span>
            Describe what you'd like and I'll suggest customizations.<br>
            <small style="color:var(--ts-overlay)">Choose a model above, then type your prompt below.</small>
          </div>
        </div>
        <div class="ts-ai-chips" id="ts-ai-chips">
          <button class="ts-ai-chip" data-prompt="Rebrand for a tech startup">🚀 Tech startup</button>
          <button class="ts-ai-chip" data-prompt="Change colors to warm earth tones">🌿 Earth tones</button>
          <button class="ts-ai-chip" data-prompt="Make it more modern and minimal">✨ Minimalist</button>
          <button class="ts-ai-chip" data-prompt="Suggest a professional corporate look">💼 Corporate</button>
          <button class="ts-ai-chip" data-prompt="Make the design more playful and colorful">🎨 Playful</button>
          <button class="ts-ai-chip" data-prompt="I'm a dentist in Warsaw, customize for my clinic">🦷 Dentist</button>
          <button class="ts-ai-chip" data-prompt="Restaurant in Paris, elegant French cuisine">🍷 Restaurant</button>
          <button class="ts-ai-chip" data-prompt="Dark mode with neon accents">🌙 Dark neon</button>
        </div>
        <div class="ts-ai-usage" id="ts-ai-usage"></div>
        <div class="ts-ai-input-row">
          <input class="ts-ai-input" id="ts-ai-input" placeholder="Describe your vision…" autocomplete="off">
          <button class="ts-ai-send" id="ts-ai-send" title="Send to AI">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
      </div>
      <?php endif; ?>
    </aside>

    <!-- ── Preview Area (right side) ───────────────────── -->
    <main id="ts-preview">
      <div class="ts-preview-container desktop" id="ts-preview-container">
        <div class="ts-preview-notch"></div>
        <div class="ts-preview-home"></div>
        <div class="ts-preview-loading" id="ts-preview-loading">
          <div class="ts-preview-spinner"></div>
          <div class="ts-preview-loading-text">Loading preview…</div>
        </div>
        <iframe id="ts-iframe" src="/admin/theme-studio/preview" title="Theme Preview"></iframe>
      </div>
    </main>
  </div>
</div>

<!-- ── Toasts Container ────────────────────────────────── -->
<div id="ts-toasts"></div>

<!-- ── Confirm Modal ───────────────────────────────────── -->
<div class="ts-modal-overlay" id="ts-modal">
  <div class="ts-modal">
    <div class="ts-modal-title" id="ts-modal-title"></div>
    <div class="ts-modal-text" id="ts-modal-text"></div>
    <div class="ts-modal-actions">
      <button class="ts-btn ts-btn-ghost" id="ts-modal-cancel">Cancel</button>
      <button class="ts-btn ts-btn-primary" id="ts-modal-confirm">Confirm</button>
    </div>
  </div>
</div>

<!-- ── Color Picker Popover (shared, positioned dynamically) ── -->
<div class="ts-cpicker" id="ts-cpicker">
  <div class="ts-cpicker-sat" id="ts-cpicker-sat">
    <div class="ts-cpicker-sat-white"></div>
    <div class="ts-cpicker-sat-black"></div>
    <div class="ts-cpicker-sat-cursor" id="ts-cpicker-sat-cursor"></div>
  </div>
  <div class="ts-cpicker-hue" id="ts-cpicker-hue">
    <div class="ts-cpicker-hue-cursor" id="ts-cpicker-hue-cursor"></div>
  </div>
  <div class="ts-cpicker-input-row">
    <input type="text" class="ts-color-hex" id="ts-cpicker-hex" maxlength="7" spellcheck="false" placeholder="#000000">
    <div class="ts-cpicker-preview" id="ts-cpicker-preview"></div>
  </div>
  <div class="ts-cpicker-swatches" id="ts-cpicker-swatches"></div>
</div>


<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT — Vanilla, no frameworks
     ═══════════════════════════════════════════════════════════ -->
<script>
(function() {
'use strict';

/* ═══════════════════════════════════════════════════════════
   CONFIGURATION & INITIAL STATE
   ═══════════════════════════════════════════════════════════ */

const CSRF       = <?= json_encode($csrfToken ?? '') ?>;
const SCHEMA     = <?= $schemaJson ?>;
const AI_ON      = <?= json_encode(!empty($aiAvailable)) ?>;
const PEXELS_ON  = <?= json_encode(!empty($pexelsAvailable)) ?>;
const THEME_SLUG = <?= json_encode($themeSlug ?? '') ?>;

let values    = <?= $valuesJson ?>;
let history   = <?= $historyJson ?>;
let undoStack = [];
let redoStack = [];
let dirty     = false;
let saveTimer = null;
let activeDevice = 'desktop';

/* AI model state */
let aiProviders   = {};
let aiSelectedProvider = null;
let aiSelectedModel    = null;
let aiTotalTokens = 0;

/* Color picker state */
let cpickerTarget = null;
let cpickerHue    = 0;
let cpickerSat    = 1;
let cpickerVal    = 1;

/* ═══════════════════════════════════════════════════════════
   DOM REFERENCES
   ═══════════════════════════════════════════════════════════ */

const $  = (s, p) => (p || document).querySelector(s);
const $$ = (s, p) => (p || document).querySelectorAll(s);

const dom = {
  panelSections   : $('#ts-panel-sections'),
  previewContainer: $('#ts-preview-container'),
  iframe          : $('#ts-iframe'),
  previewLoading  : $('#ts-preview-loading'),
  undoBtn         : $('#ts-undo'),
  redoBtn         : $('#ts-redo'),
  historyBtn      : $('#ts-history-btn'),
  historyDropdown : $('#ts-history-dropdown'),
  historyList     : $('#ts-history-list'),
  historyClose    : $('#ts-history-close'),
  publishBtn      : $('#ts-publish-btn'),
  resetBtn        : $('#ts-reset-btn'),
  saveStatus      : $('#ts-save-status'),
  saveText        : $('.ts-save-text', $('#ts-save-status')),
  saveDot         : $('.ts-dot', $('#ts-save-status')),
  toasts          : $('#ts-toasts'),
  modal           : $('#ts-modal'),
  modalTitle      : $('#ts-modal-title'),
  modalText       : $('#ts-modal-text'),
  modalConfirm    : $('#ts-modal-confirm'),
  modalCancel     : $('#ts-modal-cancel'),
  cpicker         : $('#ts-cpicker'),
  cpickerSat      : $('#ts-cpicker-sat'),
  cpickerSatCursor: $('#ts-cpicker-sat-cursor'),
  cpickerHue      : $('#ts-cpicker-hue'),
  cpickerHueCursor: $('#ts-cpicker-hue-cursor'),
  cpickerHex      : $('#ts-cpicker-hex'),
  cpickerPreview  : $('#ts-cpicker-preview'),
  cpickerSwatches : $('#ts-cpicker-swatches'),
};

/* AI elements (may be null if AI not available) */
const aiDom = AI_ON ? {
  panel     : $('#ts-ai-panel'),
  toggle    : $('#ts-ai-toggle'),
  messages  : $('#ts-ai-messages'),
  input     : $('#ts-ai-input'),
  send      : $('#ts-ai-send'),
  chips     : $$('.ts-ai-chip'),
  modelBar  : $('#ts-ai-model-bar'),
  modelSelect: $('#ts-ai-model'),
  modelInfo : $('#ts-ai-model-info'),
  usage     : $('#ts-ai-usage'),
} : null;


/* ═══════════════════════════════════════════════════════════
   UTILITY FUNCTIONS
   ═══════════════════════════════════════════════════════════ */

function apiHeaders(isJson) {
  const h = { 'X-CSRF-TOKEN': CSRF };
  if (isJson) h['Content-Type'] = 'application/json';
  return h;
}

async function api(method, path, body) {
  const opts = { method, headers: apiHeaders(body && !(body instanceof FormData)) };
  if (body) opts.body = (body instanceof FormData) ? body : JSON.stringify(body);
  const resp = await fetch('/api/theme-studio/' + path, opts);
  if (!resp.ok) throw new Error('API error: ' + resp.status);
  return resp.json();
}

function deepClone(o) { return JSON.parse(JSON.stringify(o)); }

function getVal(section, field) {
  if (values && values[section] && values[section][field] !== undefined) {
    return values[section][field];
  }
  return SCHEMA[section]?.fields?.[field]?.default ?? '';
}

function setVal(section, field, value) {
  if (!values) values = {};
  if (!values[section]) values[section] = {};
  values[section][field] = value;
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

function formatDate(d) {
  if (!d) return '';
  const dt = new Date(d);
  if (isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString('en-GB', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}


/* ═══════════════════════════════════════════════════════════
   TOAST NOTIFICATIONS
   ═══════════════════════════════════════════════════════════ */

const TOAST_ICONS = { success: '✓', error: '✕', info: 'ℹ' };

function toast(msg, type) {
  type = type || 'info';
  const el = document.createElement('div');
  el.className = 'ts-toast ts-toast-' + type;
  el.innerHTML = '<span class="ts-toast-icon">' + (TOAST_ICONS[type] || '') + '</span>' + esc(msg);
  dom.toasts.appendChild(el);
  el.addEventListener('click', () => dismissToast(el));
  setTimeout(() => dismissToast(el), 3000);
}

function dismissToast(el) {
  if (el._gone) return;
  el._gone = true;
  el.classList.add('out');
  setTimeout(() => el.remove(), 300);
}


/* ═══════════════════════════════════════════════════════════
   CONFIRM MODAL
   ═══════════════════════════════════════════════════════════ */

let modalResolve = null;

function confirmDialog(title, text, confirmLabel) {
  return new Promise(resolve => {
    modalResolve = resolve;
    dom.modalTitle.textContent = title;
    dom.modalText.textContent  = text;
    dom.modalConfirm.textContent = confirmLabel || 'Confirm';
    dom.modal.classList.add('open');
    dom.modalConfirm.focus();
  });
}

function closeModal(result) {
  dom.modal.classList.remove('open');
  if (modalResolve) { modalResolve(result); modalResolve = null; }
}

dom.modalConfirm.addEventListener('click', () => closeModal(true));
dom.modalCancel.addEventListener('click',  () => closeModal(false));
dom.modal.addEventListener('click', e => { if (e.target === dom.modal) closeModal(false); });


/* ═══════════════════════════════════════════════════════════
   COLOR CONVERSION UTILITIES
   ═══════════════════════════════════════════════════════════ */

function hsvToRgb(h, s, v) {
  let r, g, b;
  const i = Math.floor(h * 6);
  const f = h * 6 - i;
  const p = v * (1 - s);
  const q = v * (1 - f * s);
  const t = v * (1 - (1 - f) * s);
  switch (i % 6) {
    case 0: r=v; g=t; b=p; break;
    case 1: r=q; g=v; b=p; break;
    case 2: r=p; g=v; b=t; break;
    case 3: r=p; g=q; b=v; break;
    case 4: r=t; g=p; b=v; break;
    case 5: r=v; g=p; b=q; break;
  }
  return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function rgbToHsv(r, g, b) {
  r /= 255; g /= 255; b /= 255;
  const mx = Math.max(r, g, b), mn = Math.min(r, g, b);
  const d = mx - mn;
  let h = 0, s = mx === 0 ? 0 : d / mx, v = mx;
  if (d !== 0) {
    switch (mx) {
      case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
      case g: h = ((b - r) / d + 2) / 6; break;
      case b: h = ((r - g) / d + 4) / 6; break;
    }
  }
  return [h, s, v];
}

function hexToRgb(hex) {
  hex = hex.replace('#', '');
  if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
  const n = parseInt(hex, 16);
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
}

function rgbToHex(r, g, b) {
  return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}

function hsvToHex(h, s, v) { return rgbToHex(...hsvToRgb(h, s, v)); }
function hexToHsv(hex) { return rgbToHsv(...hexToRgb(hex)); }


/* ═══════════════════════════════════════════════════════════
   COLOR PICKER POPOVER
   ═══════════════════════════════════════════════════════════ */

const SWATCHES = [
  '#f38ba8','#fab387','#f9e2af','#a6e3a1','#94e2d5','#89b4fa','#cba6f7','#f5c2e7',
  '#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#3b82f6','#8b5cf6','#ec4899',
  '#dc2626','#ea580c','#ca8a04','#16a34a','#0891b2','#2563eb','#7c3aed','#db2777',
  '#1e1e2e','#313244','#45475a','#585b70','#6c7086','#a6adc8','#cdd6f4','#ffffff',
];

function initSwatches() {
  dom.cpickerSwatches.innerHTML = '';
  SWATCHES.forEach(c => {
    const el = document.createElement('div');
    el.className = 'ts-cpicker-sw';
    el.style.background = c;
    el.title = c;
    el.addEventListener('click', () => {
      cpickerSetFromHex(c);
      cpickerApply();
    });
    dom.cpickerSwatches.appendChild(el);
  });
}

function openColorPicker(swatch, hexInput, section, field) {
  cpickerTarget = { section, field, swatchEl: swatch, hexEl: hexInput };

  const hex = getVal(section, field) || '#3b82f6';
  const [h, s, v] = hexToHsv(hex);
  cpickerHue = h; cpickerSat = s; cpickerVal = v;
  updateCpickerUI();

  // Position near the swatch
  const rect = swatch.getBoundingClientRect();
  const pw = 260, ph = 340;
  let top  = rect.bottom + 8;
  let left = rect.left;
  if (left + pw > window.innerWidth) left = window.innerWidth - pw - 12;
  if (left < 8) left = 8;
  if (top + ph > window.innerHeight) top = rect.top - ph - 8;
  if (top < 8) top = 8;

  dom.cpicker.style.top  = top + 'px';
  dom.cpicker.style.left = left + 'px';
  dom.cpicker.classList.add('open');
}

function closeCpicker() {
  dom.cpicker.classList.remove('open');
  cpickerTarget = null;
}

function updateCpickerUI() {
  // Saturation area background color
  const hueHex = rgbToHex(...hsvToRgb(cpickerHue, 1, 1));
  dom.cpickerSat.style.background = hueHex;

  // Position cursors
  dom.cpickerSatCursor.style.left = (cpickerSat * 100) + '%';
  dom.cpickerSatCursor.style.top  = ((1 - cpickerVal) * 100) + '%';
  dom.cpickerHueCursor.style.left = (cpickerHue * 100) + '%';

  // Update hex display & preview
  const hex = hsvToHex(cpickerHue, cpickerSat, cpickerVal);
  dom.cpickerHex.value = hex;
  dom.cpickerPreview.style.background = hex;
  dom.cpickerSatCursor.style.background = hex;
  dom.cpickerHueCursor.style.background = hueHex;
}

function cpickerSetFromHex(hex) {
  if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
  const [h, s, v] = hexToHsv(hex);
  cpickerHue = h; cpickerSat = s; cpickerVal = v;
  updateCpickerUI();
}

function cpickerApply() {
  if (!cpickerTarget) return;
  const hex = hsvToHex(cpickerHue, cpickerSat, cpickerVal);
  const { section, field, swatchEl, hexEl } = cpickerTarget;
  swatchEl.style.background = hex;
  hexEl.value = hex;
  onFieldChange(section, field, hex);
}

/* Saturation/Value area dragging */
let satDragging = false;

function satUpdate(e) {
  e.preventDefault();
  const rect = dom.cpickerSat.getBoundingClientRect();
  cpickerSat = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
  cpickerVal = Math.max(0, Math.min(1, 1 - (e.clientY - rect.top) / rect.height));
  updateCpickerUI();
  cpickerApply();
}

dom.cpickerSat.addEventListener('mousedown', e => { satDragging = true; satUpdate(e); });

/* Hue bar dragging */
let hueDragging = false;

function hueUpdate(e) {
  e.preventDefault();
  const rect = dom.cpickerHue.getBoundingClientRect();
  cpickerHue = Math.max(0, Math.min(0.9999, (e.clientX - rect.left) / rect.width));
  updateCpickerUI();
  cpickerApply();
}

dom.cpickerHue.addEventListener('mousedown', e => { hueDragging = true; hueUpdate(e); });

/* Global mouse move/up for dragging */
document.addEventListener('mousemove', e => {
  if (satDragging) satUpdate(e);
  if (hueDragging) hueUpdate(e);
});
document.addEventListener('mouseup', () => {
  satDragging = false;
  hueDragging = false;
});

/* Hex input in picker */
dom.cpickerHex.addEventListener('input', () => {
  let v = dom.cpickerHex.value.trim();
  if (!v.startsWith('#')) v = '#' + v;
  if (/^#[0-9a-fA-F]{6}$/.test(v)) {
    cpickerSetFromHex(v);
    cpickerApply();
  }
});

/* Close color picker on outside click */
document.addEventListener('mousedown', e => {
  if (!dom.cpicker.classList.contains('open')) return;
  if (dom.cpicker.contains(e.target)) return;
  if (e.target.classList.contains('ts-color-swatch')) return;
  closeCpicker();
});

/* Initialize swatches */
initSwatches();


/* ═══════════════════════════════════════════════════════════
   RENDER CONTROL PANEL — ACCORDION SECTIONS & FIELDS
   ═══════════════════════════════════════════════════════════ */

const COLOR_PRESETS = [
  { name: 'Purple Dream', primary: '#7c3aed', secondary: '#a78bfa', accent: '#f59e0b' },
  { name: 'Ocean Blue',   primary: '#0ea5e9', secondary: '#06b6d4', accent: '#14b8a6' },
  { name: 'Forest Green', primary: '#16a34a', secondary: '#22c55e', accent: '#eab308' },
  { name: 'Sunset',       primary: '#f97316', secondary: '#ef4444', accent: '#fbbf24' },
  { name: 'Corporate',    primary: '#1e40af', secondary: '#3b82f6', accent: '#6366f1' },
  { name: 'Rose',         primary: '#e11d48', secondary: '#f43f5e', accent: '#ec4899' },
];

function renderPanel() {
  dom.panelSections.innerHTML = '';

  /* ── Color Presets ──────────────────────────────── */
  const presetsEl = document.createElement('div');
  presetsEl.className = 'ts-presets';
  presetsEl.innerHTML = '<div class="ts-presets-title">Color Presets</div>';
  const grid = document.createElement('div');
  grid.className = 'ts-presets-grid';

  COLOR_PRESETS.forEach(preset => {
    const btn = document.createElement('button');
    btn.className = 'ts-preset-btn';
    btn.title = preset.name;
    btn.innerHTML =
      '<div class="ts-preset-dots">' +
        '<span class="ts-preset-dot" style="background:' + preset.primary + '"></span>' +
        '<span class="ts-preset-dot" style="background:' + preset.secondary + '"></span>' +
        '<span class="ts-preset-dot" style="background:' + preset.accent + '"></span>' +
      '</div>' +
      '<span class="ts-preset-label">' + esc(preset.name) + '</span>';
    btn.addEventListener('click', () => {
      pushUndo();
      setVal('brand', 'primary_color', preset.primary);
      setVal('brand', 'secondary_color', preset.secondary);
      setVal('brand', 'accent_color', preset.accent);
      refreshAllFields();
      sendToPreview();
      scheduleSave();
      toast('Applied preset: ' + preset.name, 'success');
    });
    grid.appendChild(btn);
  });

  presetsEl.appendChild(grid);
  dom.panelSections.appendChild(presetsEl);

  const entries = Object.entries(SCHEMA);

  entries.forEach(([sectionKey, section], idx) => {
    const sEl = document.createElement('div');
    sEl.className = 'ts-section' + (idx === 0 ? ' open' : '');
    sEl.dataset.section = sectionKey;

    /* Section header */
    const header = document.createElement('div');
    header.className = 'ts-section-header';
    header.innerHTML =
      '<span class="ts-section-icon">' + (section.icon || '⚙️') + '</span>' +
      '<span class="ts-section-label">' + esc(section.label || sectionKey) + '</span>' +
      '<svg class="ts-section-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
      'stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';

    header.addEventListener('click', () => {
      sEl.classList.toggle('open');
    });

    /* Section body */
    const body   = document.createElement('div');
    body.className = 'ts-section-body';
    const fields = document.createElement('div');
    fields.className = 'ts-section-fields';

    Object.entries(section.fields || {}).forEach(([fieldKey, fieldDef]) => {
      fields.appendChild(buildField(sectionKey, fieldKey, fieldDef));
    });

    body.appendChild(fields);
    sEl.appendChild(header);
    sEl.appendChild(body);
    dom.panelSections.appendChild(sEl);
  });
}

/* ── Build a single field ──────────────────────────────── */
function buildField(section, key, def) {
  const wrap  = document.createElement('div');
  wrap.className    = 'ts-field';
  wrap.dataset.section = section;
  wrap.dataset.field   = key;

  const label = document.createElement('label');
  label.className   = 'ts-field-label';
  label.textContent = def.label || key;
  wrap.appendChild(label);

  const val = getVal(section, key);

  switch (def.type) {
    case 'text':     wrap.appendChild(buildText(section, key, val)); break;
    case 'textarea': wrap.appendChild(buildTextarea(section, key, val)); break;
    case 'color':    wrap.appendChild(buildColor(section, key, val)); break;
    case 'image':    wrap.appendChild(buildImage(section, key, val)); break;
    case 'toggle':   wrap.appendChild(buildToggle(section, key, val)); break;
    case 'select':   wrap.appendChild(buildSelect(section, key, val, def.options || {})); break;
    default:         wrap.appendChild(buildText(section, key, val)); break;
  }

  return wrap;
}

/* Text */
function buildText(section, key, val) {
  const inp = document.createElement('input');
  inp.type = 'text';
  inp.className = 'ts-input';
  inp.value = val ?? '';
  inp.placeholder = SCHEMA[section]?.fields?.[key]?.default || '';
  inp.addEventListener('input', () => onFieldChange(section, key, inp.value));
  return inp;
}

/* Textarea */
function buildTextarea(section, key, val) {
  const ta = document.createElement('textarea');
  ta.className = 'ts-textarea';
  ta.value = val ?? '';
  ta.rows = 3;
  ta.placeholder = SCHEMA[section]?.fields?.[key]?.default || '';
  ta.addEventListener('input', () => onFieldChange(section, key, ta.value));
  return ta;
}

/* Color */
function buildColor(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-color-field';

  const color = val || '#3b82f6';

  const swatch = document.createElement('div');
  swatch.className = 'ts-color-swatch';
  swatch.style.background = color;

  const hexInput = document.createElement('input');
  hexInput.type = 'text';
  hexInput.className = 'ts-color-hex';
  hexInput.value = color;
  hexInput.maxLength = 7;
  hexInput.spellcheck = false;

  swatch.addEventListener('click', () => openColorPicker(swatch, hexInput, section, key));

  hexInput.addEventListener('input', () => {
    let v = hexInput.value.trim();
    if (!v.startsWith('#')) v = '#' + v;
    if (/^#[0-9a-fA-F]{6}$/.test(v)) {
      swatch.style.background = v;
      onFieldChange(section, key, v);
    }
  });

  hexInput.addEventListener('blur', () => {
    let v = hexInput.value.trim();
    if (!v.startsWith('#')) v = '#' + v;
    if (/^#[0-9a-fA-F]{3}$/.test(v)) {
      v = '#' + v[1]+v[1]+v[2]+v[2]+v[3]+v[3];
      hexInput.value = v;
      swatch.style.background = v;
      onFieldChange(section, key, v);
    }
  });

  wrap.appendChild(swatch);
  wrap.appendChild(hexInput);
  return wrap;
}

/* Image upload */
function buildImage(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-image-field';

  /* Preview */
  const preview = document.createElement('div');
  preview.className = 'ts-image-preview' + (val ? ' has-image' : '');

  const img = document.createElement('img');
  img.src = val || '';
  img.alt = 'Preview';
  img.loading = 'lazy';

  const removeBtn = document.createElement('button');
  removeBtn.className = 'ts-image-remove';
  removeBtn.innerHTML = '✕';
  removeBtn.title = 'Remove image';
  removeBtn.addEventListener('click', e => {
    e.stopPropagation();
    img.src = '';
    preview.classList.remove('has-image');
    onFieldChange(section, key, null);
  });

  preview.appendChild(img);
  preview.appendChild(removeBtn);

  /* Browse button (opens media picker modal) */
  const drop = document.createElement('div');
  drop.className = 'ts-image-drop';
  drop.innerHTML =
    '<div class="ts-image-drop-icon">🖼️</div>' +
    '<div class="ts-image-drop-text">Click to <span>browse media library</span></div>';

  drop.addEventListener('click', () => {
    JTB.openMediaGallery((selectedUrl) => {
      img.src = selectedUrl;
      preview.classList.add('has-image');
      onFieldChange(section, key, selectedUrl);
    });
  });

  wrap.appendChild(preview);
  wrap.appendChild(drop);
  return wrap;
}

/* ═══════════════════════════════════════════════════════════
   MEDIA PICKER MODAL
   ═══════════════════════════════════════════════════════════ */

/* Toggle */
function buildToggle(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-toggle-wrap';

  const label = document.createElement('label');
  label.className = 'ts-toggle';

  const input = document.createElement('input');
  input.type = 'checkbox';
  input.checked = (val === true || val === 1 || val === '1' || val === 'true');

  const track = document.createElement('span');
  track.className = 'ts-toggle-track';
  const thumb = document.createElement('span');
  thumb.className = 'ts-toggle-thumb';

  label.appendChild(input);
  label.appendChild(track);
  label.appendChild(thumb);

  const lbl = document.createElement('span');
  lbl.className = 'ts-toggle-label-text';
  lbl.textContent = input.checked ? 'On' : 'Off';

  input.addEventListener('change', () => {
    lbl.textContent = input.checked ? 'On' : 'Off';
    onFieldChange(section, key, input.checked);
  });

  wrap.appendChild(label);
  wrap.appendChild(lbl);
  return wrap;
}

/* Select */
function buildSelect(section, key, val, options) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-select-wrap';

  const sel = document.createElement('select');
  sel.className = 'ts-select';

  Object.entries(options).forEach(([optVal, optLabel]) => {
    const opt = document.createElement('option');
    opt.value = optVal;
    opt.textContent = optLabel;
    if (String(optVal) === String(val)) opt.selected = true;
    sel.appendChild(opt);
  });

  sel.addEventListener('change', () => onFieldChange(section, key, sel.value));

  wrap.appendChild(sel);
  return wrap;
}


/* ═══════════════════════════════════════════════════════════
   FIELD CHANGE HANDLING & AUTO-SAVE
   ═══════════════════════════════════════════════════════════ */

function onFieldChange(section, field, value) {
  pushUndo();
  setVal(section, field, value);
  dirty = true;
  sendToPreview();
  scheduleSave();
}

function pushUndo() {
  undoStack.push(deepClone(values));
  if (undoStack.length > 60) undoStack.shift();
  redoStack = [];
  updateUndoRedoButtons();
}

function doUndo() {
  if (!undoStack.length) return;
  redoStack.push(deepClone(values));
  values = undoStack.pop();
  refreshAllFields();
  sendToPreview();
  scheduleSave();
  updateUndoRedoButtons();
}

function doRedo() {
  if (!redoStack.length) return;
  undoStack.push(deepClone(values));
  values = redoStack.pop();
  refreshAllFields();
  sendToPreview();
  scheduleSave();
  updateUndoRedoButtons();
}

function updateUndoRedoButtons() {
  dom.undoBtn.disabled = undoStack.length === 0;
  dom.redoBtn.disabled = redoStack.length === 0;
}

/* Refresh all field inputs to match current values */
function refreshAllFields() {
  $$('.ts-field').forEach(el => {
    const s = el.dataset.section, f = el.dataset.field;
    if (!s || !f) return;
    const val  = getVal(s, f);
    const type = SCHEMA[s]?.fields?.[f]?.type;

    switch (type) {
      case 'text': {
        const inp = $('.ts-input', el);
        if (inp && document.activeElement !== inp) inp.value = val ?? '';
        break;
      }
      case 'textarea': {
        const ta = $('.ts-textarea', el);
        if (ta && document.activeElement !== ta) ta.value = val ?? '';
        break;
      }
      case 'color': {
        const sw  = $('.ts-color-swatch', el);
        const hex = $('.ts-color-hex', el);
        const c   = val || '#3b82f6';
        if (sw) sw.style.background = c;
        if (hex && document.activeElement !== hex) hex.value = c;
        break;
      }
      case 'toggle': {
        const cb  = $('input[type="checkbox"]', el);
        const lbl = $('.ts-toggle-label-text', el);
        if (cb) {
          cb.checked = (val === true || val === 1 || val === '1' || val === 'true');
          if (lbl) lbl.textContent = cb.checked ? 'On' : 'Off';
        }
        break;
      }
      case 'select': {
        const sel = $('.ts-select', el);
        if (sel) sel.value = val ?? '';
        break;
      }
      case 'image': {
        const prev = $('.ts-image-preview', el);
        const img  = $('img', prev);
        if (val) {
          prev.classList.add('has-image');
          if (img) img.src = val;
        } else {
          prev.classList.remove('has-image');
          if (img) img.src = '';
        }
        break;
      }
    }
  });
}

/* Debounced auto-save (2 seconds) */
function scheduleSave() {
  clearTimeout(saveTimer);
  showSaveStatus('unsaved');
  saveTimer = setTimeout(() => doSave('Auto-save'), 2000);
}

async function doSave(label) {
  clearTimeout(saveTimer);
  showSaveStatus('saving');
  try {
    const res = await api('POST', 'save', { data: values, label: label || 'Save' });
    if (res.ok) {
      dirty = false;
      showSaveStatus('saved');
      if (res.history) { history = res.history; renderHistory(); }
    } else {
      throw new Error(res.error || 'Save failed');
    }
  } catch (e) {
    showSaveStatus('error');
    toast('Save failed: ' + e.message, 'error');
  }
}

function showSaveStatus(state) {
  dom.saveStatus.className = 'ts-save-indicator';
  switch (state) {
    case 'saving':
      dom.saveStatus.classList.add('saving');
      dom.saveText.textContent = 'Saving…';
      break;
    case 'saved':
      dom.saveStatus.classList.add('saved');
      dom.saveText.textContent = 'Saved';
      setTimeout(() => {
        if (dom.saveText.textContent === 'Saved') {
          dom.saveStatus.className = 'ts-save-indicator';
          dom.saveText.textContent = 'Ready';
        }
      }, 3000);
      break;
    case 'unsaved':
      dom.saveText.textContent = 'Unsaved changes';
      break;
    case 'error':
      dom.saveStatus.classList.add('error');
      dom.saveText.textContent = 'Save error';
      break;
    default:
      dom.saveText.textContent = 'Ready';
  }
}


/* ═══════════════════════════════════════════════════════════
   PUBLISH
   ═══════════════════════════════════════════════════════════ */

dom.publishBtn.addEventListener('click', async () => {
  clearTimeout(saveTimer);
  showSaveStatus('saving');
  try {
    const res = await api('POST', 'save', { data: values, label: 'Published' });
    if (res.ok) {
      dirty = false;
      showSaveStatus('saved');
      if (res.history) { history = res.history; renderHistory(); }
      toast('Theme published successfully!', 'success');
      // Reload iframe to show published state
      dom.iframe.src = dom.iframe.src;
    } else {
      throw new Error(res.error || 'Publish failed');
    }
  } catch (e) {
    showSaveStatus('error');
    toast('Publish failed: ' + e.message, 'error');
  }
});


/* ═══════════════════════════════════════════════════════════
   RESET
   ═══════════════════════════════════════════════════════════ */

dom.resetBtn.addEventListener('click', async () => {
  const ok = await confirmDialog(
    'Reset All Customizations?',
    'This will reset all theme customizations back to their default values. Your current changes will be lost.',
    'Reset'
  );
  if (!ok) return;

  showSaveStatus('saving');
  try {
    const res = await api('POST', 'reset', {});
    if (res.ok) {
      pushUndo();
      values = res.values || {};
      refreshAllFields();
      sendToPreview();
      showSaveStatus('saved');
      if (res.history) { history = res.history; renderHistory(); }
      toast('Theme reset to defaults', 'success');
    } else {
      throw new Error(res.error || 'Reset failed');
    }
  } catch (e) {
    showSaveStatus('error');
    toast('Reset failed: ' + e.message, 'error');
  }
});


/* ═══════════════════════════════════════════════════════════
   HISTORY
   ═══════════════════════════════════════════════════════════ */

function renderHistory() {
  dom.historyList.innerHTML = '';
  if (!history || !history.length) {
    dom.historyList.innerHTML = '<div class="ts-history-empty">No history snapshots yet</div>';
    return;
  }
  history.forEach((h, i) => {
    const item = document.createElement('div');
    item.className = 'ts-history-item';
    item.innerHTML =
      '<span class="ts-history-dot" style="background:' + (i === 0 ? 'var(--ts-green)' : 'var(--ts-blue)') + '"></span>' +
      '<div class="ts-history-info">' +
        '<div class="ts-history-label">' + esc(h.label || 'Snapshot') + '</div>' +
        '<div class="ts-history-date">' + esc(formatDate(h.created_at || h.date)) + '</div>' +
      '</div>';
    item.addEventListener('click', () => restoreSnapshot(h.id));
    dom.historyList.appendChild(item);
  });
}

async function restoreSnapshot(id) {
  const ok = await confirmDialog(
    'Restore Snapshot?',
    'This will replace your current customizations with the selected snapshot. You can still undo this change.',
    'Restore'
  );
  if (!ok) return;

  showSaveStatus('saving');
  try {
    const res = await api('POST', 'restore', { id });
    if (res.ok) {
      pushUndo();
      values = res.values || {};
      refreshAllFields();
      sendToPreview();
      showSaveStatus('saved');
      dom.historyDropdown.classList.remove('open');
      toast('Snapshot restored', 'success');
    } else {
      throw new Error(res.error || 'Restore failed');
    }
  } catch (e) {
    toast('Restore failed: ' + e.message, 'error');
  }
}

/* History dropdown toggle */
dom.historyBtn.addEventListener('click', e => {
  e.stopPropagation();
  dom.historyDropdown.classList.toggle('open');
});
dom.historyClose.addEventListener('click', () => dom.historyDropdown.classList.remove('open'));

/* Undo / Redo buttons */
dom.undoBtn.addEventListener('click', doUndo);
dom.redoBtn.addEventListener('click', doRedo);


/* ═══════════════════════════════════════════════════════════
   DEVICE PREVIEW TOGGLE
   ═══════════════════════════════════════════════════════════ */

$$('.ts-device-btns .ts-icon-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    $$('.ts-device-btns .ts-icon-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeDevice = btn.dataset.device;
    dom.previewContainer.className = 'ts-preview-container ' + activeDevice;
  });
});


/* ═══════════════════════════════════════════════════════════
   IFRAME COMMUNICATION (PostMessage)
   ═══════════════════════════════════════════════════════════ */

function sendToPreview() {
  try {
    dom.iframe.contentWindow.postMessage({
      type: 'theme-studio-update',
      values: values
    }, '*');
  } catch (e) {
    /* cross-origin — will happen in some setups, that's ok */
  }
}

dom.iframe.addEventListener('load', () => {
  dom.previewLoading.classList.add('hidden');
  /* Give iframe a moment to initialize, then send values */
  setTimeout(() => sendToPreview(), 200);
});

/* Listen for ready signal from iframe */
window.addEventListener('message', e => {
  if (e.data && e.data.type === 'theme-studio-ready') {
    sendToPreview();
  }
});


/* ═══════════════════════════════════════════════════════════
   AI CHAT
   ═══════════════════════════════════════════════════════════ */

if (AI_ON && aiDom) {
  /* Toggle AI panel */
  aiDom.toggle.addEventListener('click', () => {
    aiDom.panel.classList.toggle('collapsed');
    if (!aiDom.panel.classList.contains('collapsed')) {
      setTimeout(() => aiDom.input.focus(), 100);
      /* Load models on first open */
      if (!aiDom._modelsLoaded) loadAiModels();
    }
  });

  /* ── Model Selector ─────────────────────────────────── */

  async function loadAiModels() {
    aiDom._modelsLoaded = true;
    try {
      const res = await api('GET', 'ai/models');
      if (!res.ok || !res.providers) {
        aiDom.modelSelect.innerHTML = '<option value="">No AI models available</option>';
        return;
      }
      
      aiProviders = res.providers;
      const defaultProvider = res.default_provider;
      const defaultModel = res.default_model;
      
      let html = '';
      let firstValue = '';
      let defaultValue = '';
      
      Object.entries(res.providers).forEach(([provKey, prov]) => {
        /* Split into current and legacy groups */
        const current = prov.models.filter(m => !m.legacy);
        const legacy = prov.models.filter(m => m.legacy);
        
        if (current.length) {
          html += '<optgroup label="' + esc(prov.icon + ' ' + prov.label) + '">';
          current.forEach(m => {
            const val = provKey + '::' + m.id;
            if (!firstValue) firstValue = val;
            const isDefault = (provKey === defaultProvider && m.id === defaultModel);
            const star = m.recommended ? ' ⭐' : '';
            const tierLabel = m.tier && m.tier !== 'standard' ? ' [' + m.tier.toUpperCase() + ']' : '';
            html += '<option value="' + esc(val) + '"' + (isDefault ? ' selected' : '') + '>' +
                    esc(m.name + tierLabel + star) + '</option>';
            if (isDefault) defaultValue = val;
          });
          html += '</optgroup>';
        }
        
        if (legacy.length) {
          html += '<optgroup label="' + esc('   ↳ ' + prov.label + ' Legacy') + '">';
          legacy.forEach(m => {
            const val = provKey + '::' + m.id;
            html += '<option value="' + esc(val) + '" style="color:#585b70">' +
                    esc(m.name) + '</option>';
          });
          html += '</optgroup>';
        }
      });
      
      aiDom.modelSelect.innerHTML = html;
      
      /* Set initial selection: configured default > first recommended > first model */
      const selected = defaultValue || firstValue;
      if (selected) {
        aiDom.modelSelect.value = selected;
        updateModelSelection(selected);
      }
    } catch (e) {
      aiDom.modelSelect.innerHTML = '<option value="">Error loading models</option>';
      console.error('[Theme Studio] Failed to load AI models:', e);
    }
  }

  aiDom.modelSelect.addEventListener('change', () => {
    updateModelSelection(aiDom.modelSelect.value);
  });

  function updateModelSelection(val) {
    if (!val) return;
    const [provider, model] = val.split('::');
    aiSelectedProvider = provider;
    aiSelectedModel = model;
    
    /* Find model info for tier badge + description */
    const prov = aiProviders[provider];
    if (prov) {
      const modelDef = prov.models.find(m => m.id === model);
      if (modelDef) {
        let infoHtml = '<span class="ts-ai-model-tier ' + esc(modelDef.tier || '') + '">' +
                       esc(modelDef.tier || '') + '</span>';
        if (modelDef.desc) {
          infoHtml += '<span style="font-size:9px;color:var(--ts-overlay)">' + esc(modelDef.desc) + '</span>';
        }
        aiDom.modelInfo.innerHTML = infoHtml;
      } else {
        aiDom.modelInfo.innerHTML = '';
      }
    }
  }

  function getAiPayload(extra) {
    const payload = extra || {};
    if (aiSelectedProvider) payload.provider = aiSelectedProvider;
    if (aiSelectedModel) payload.model = aiSelectedModel;
    return payload;
  }

  function updateUsage(tokens) {
    if (tokens && tokens > 0) {
      aiTotalTokens += tokens;
      aiDom.usage.textContent = aiTotalTokens.toLocaleString() + ' tokens used this session';
      aiDom.usage.classList.add('visible');
    }
  }

  /* Quick suggestion chips */
  aiDom.chips.forEach(chip => {
    chip.addEventListener('click', () => {
      aiDom.input.value = chip.dataset.prompt;
      sendAiPrompt();
    });
  });

  /* Send button & Enter key */
  aiDom.send.addEventListener('click', sendAiPrompt);
  aiDom.input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendAiPrompt();
    }
  });

  async function sendAiPrompt() {
    const prompt = aiDom.input.value.trim();
    if (!prompt) return;
    aiDom.input.value = '';

    /* Remove welcome message */
    const welcome = $('.ts-ai-welcome', aiDom.messages);
    if (welcome) welcome.remove();

    /* User message bubble (with model indicator) */
    const modelLabel = aiSelectedModel ? ' · ' + aiSelectedModel : '';
    appendAiMsg(prompt, 'user', modelLabel);

    /* Typing indicator */
    const typing = document.createElement('div');
    typing.className = 'ts-ai-typing';
    typing.innerHTML = '<span class="ts-ai-typing-dot"></span>' +
                       '<span class="ts-ai-typing-dot"></span>' +
                       '<span class="ts-ai-typing-dot"></span>';
    aiDom.messages.appendChild(typing);
    aiDom.messages.scrollTop = aiDom.messages.scrollHeight;

    aiDom.send.disabled = true;
    const startTime = Date.now();

    try {
      const res = await api('POST', 'ai/customize', getAiPayload({ prompt }));
      typing.remove();
      
      const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
      updateUsage(res.tokens);

      if (res.ok && res.changes && Object.keys(res.changes).length > 0) {
        const providerInfo = (res.provider || aiSelectedProvider || '') + ' · ' + elapsed + 's';
        appendAiResponse(res.message || 'Here are my suggestions:', res.changes, providerInfo);
      } else if (res.ok && res.message) {
        appendAiMsg(res.message, 'assistant');
      } else {
        appendAiMsg(res.error || 'I could not generate suggestions for that request. Try being more specific.', 'assistant');
      }
    } catch (e) {
      typing.remove();
      appendAiMsg('Connection error. Please try again.', 'assistant');
    } finally {
      aiDom.send.disabled = false;
      aiDom.input.focus();
    }
  }

  function appendAiMsg(text, role, extra) {
    const msg = document.createElement('div');
    msg.className = 'ts-ai-msg ' + role;
    let html = esc(text);
    if (extra) {
      html += '<span style="display:block;font-size:10px;opacity:0.5;margin-top:4px">' + esc(extra) + '</span>';
    }
    msg.innerHTML = html;
    aiDom.messages.appendChild(msg);
    aiDom.messages.scrollTop = aiDom.messages.scrollHeight;
  }

  function appendAiResponse(text, changes, providerInfo) {
    const msg = document.createElement('div');
    msg.className = 'ts-ai-msg assistant';

    let html = '<div>' + esc(text) + '</div>';
    html += '<div class="ts-ai-changes">';

    const entries = [];
    Object.entries(changes).forEach(([section, fields]) => {
      if (typeof fields !== 'object' || fields === null) return;
      Object.entries(fields).forEach(([field, value]) => {
        const sLabel = SCHEMA[section]?.label || section;
        const fLabel = SCHEMA[section]?.fields?.[field]?.label || field;
        const display = typeof value === 'boolean' ? (value ? 'On' : 'Off') : String(value);
        html += '<div class="ts-ai-change-item">' +
          '<span class="ts-ai-change-section">' + esc(sLabel) + '</span>' +
          '<span class="ts-ai-change-arrow">→</span>' +
          '<span class="ts-ai-change-field">' + esc(fLabel) + ':</span>' +
          '<span class="ts-ai-change-value">' + esc(display.length > 60 ? display.substring(0, 57) + '...' : display) + '</span>' +
        '</div>';
        entries.push({ section, field, value });
      });
    });

    html += '</div>';
    html += '<div class="ts-ai-actions">' +
      '<button class="ts-btn ts-btn-primary ts-btn-sm ts-ai-apply-btn">✓ Apply All (' + entries.length + ')</button>' +
      '<button class="ts-btn ts-btn-ghost ts-btn-sm ts-ai-discard-btn">Discard</button>' +
    '</div>';
    
    if (providerInfo) {
      html += '<div style="font-size:10px;color:var(--ts-overlay);margin-top:6px">' + esc(providerInfo) + '</div>';
    }

    msg.innerHTML = html;
    aiDom.messages.appendChild(msg);
    aiDom.messages.scrollTop = aiDom.messages.scrollHeight;

    /* Apply all */
    const applyBtn = msg.querySelector('.ts-ai-apply-btn');
    const discardBtn = msg.querySelector('.ts-ai-discard-btn');
    const actionsEl = msg.querySelector('.ts-ai-actions');

    applyBtn.addEventListener('click', () => {
      pushUndo();
      entries.forEach(({ section, field, value }) => setVal(section, field, value));
      refreshAllFields();
      sendToPreview();
      scheduleSave();
      actionsEl.innerHTML = '<span style="color:var(--ts-green);font-size:12px;font-weight:600">✓ Applied ' + entries.length + ' changes</span>';
      toast('AI suggestions applied (' + entries.length + ' changes)', 'success');
    });

    discardBtn.addEventListener('click', () => {
      actionsEl.innerHTML = '<span style="color:var(--ts-subtext);font-size:12px">Discarded</span>';
    });
  }
}


/* ═══════════════════════════════════════════════════════════
   KEYBOARD SHORTCUTS
   ═══════════════════════════════════════════════════════════ */

document.addEventListener('keydown', e => {
  const tag = document.activeElement?.tagName;
  const isInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

  /* Ctrl+S — Save immediately */
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    clearTimeout(saveTimer);
    doSave('Manual save');
    return;
  }

  /* Ctrl+Z — Undo (only if not in an input) */
  if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey && !isInput) {
    e.preventDefault();
    doUndo();
    return;
  }

  /* Ctrl+Shift+Z / Ctrl+Y — Redo */
  if ((e.ctrlKey || e.metaKey) && ((e.key === 'z' && e.shiftKey) || e.key === 'y') && !isInput) {
    e.preventDefault();
    doRedo();
    return;
  }

  /* Escape — Close open panels/modals */
  if (e.key === 'Escape') {
    closeCpicker();
    dom.historyDropdown.classList.remove('open');
    closeModal(false);
    return;
  }
});


/* ═══════════════════════════════════════════════════════════
   OUTSIDE-CLICK HANDLERS
   ═══════════════════════════════════════════════════════════ */

document.addEventListener('click', e => {
  /* Close history dropdown on outside click */
  if (dom.historyDropdown.classList.contains('open') &&
      !dom.historyDropdown.contains(e.target) &&
      !dom.historyBtn.contains(e.target)) {
    dom.historyDropdown.classList.remove('open');
  }
});


/* ═══════════════════════════════════════════════════════════
   WARN ON LEAVE WITH UNSAVED CHANGES
   ═══════════════════════════════════════════════════════════ */

window.addEventListener('beforeunload', e => {
  if (dirty) {
    e.preventDefault();
    e.returnValue = '';
  }
});


/* ═══════════════════════════════════════════════════════════
   INITIALIZATION
   ═══════════════════════════════════════════════════════════ */

renderPanel();
renderHistory();
updateUndoRedoButtons();


/* ═══════════════════════════════════════════════════════════
   TAB NAVIGATION
   ═══════════════════════════════════════════════════════════ */

$$('.ts-tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const tab = btn.dataset.tab;
    
    /* Update active tab button */
    $$('.ts-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    /* Switch tab content */
    $$('.ts-tab-content').forEach(tc => {
      tc.classList.remove('active');
      tc.style.display = 'none';
    });
    
    const targetContent = $('[data-tab="' + tab + '"].ts-tab-content');
    if (targetContent) {
      targetContent.classList.add('active');
      targetContent.style.display = 'block';
    }
    
    /* Load sections on first switch */
    if (tab === 'sections' && !sectionsLoaded) {
      loadSections();
    }
  });
});


/* ═══════════════════════════════════════════════════════════
   SECTION MANAGER — Drag & Drop + Inline Content Editor
   ═══════════════════════════════════════════════════════════ */

let sectionsLoaded = false;
let sectionsData = [];
let draggedWrap = null;

const sectionsList = $('#sections-list');
const sectionsSaveBtn = $('#sections-save-btn');

async function loadSections() {
  sectionsList.innerHTML = '<div style="text-align:center;padding:24px;color:var(--ts-subtext);font-size:13px">Loading sections…</div>';
  try {
    const res = await api('GET', 'sections');
    if (res.ok && res.sections) {
      sectionsData = res.sections;
      sectionsLoaded = true;
      renderSections();
    } else {
      sectionsList.innerHTML = '<div style="text-align:center;padding:24px;color:var(--ts-red);font-size:13px">Failed to load sections</div>';
    }
  } catch (e) {
    sectionsList.innerHTML = '<div style="text-align:center;padding:24px;color:var(--ts-red);font-size:13px">Error: ' + esc(e.message) + '</div>';
  }
}

/**
 * Map homepage_section ID → schema section ID.
 * Some sections use different data-ts names (e.g. "features" file uses "articles.*" data-ts).
 * This mapping resolves the correct schema section for editing.
 */
function sectionSchemaId(sectionId) {
  /* Direct match first */
  if (SCHEMA[sectionId]) return sectionId;
  /* Common mappings (theme section file → data-ts section) */
  const map = {
    'features': 'articles',
    'showcase': 'pages',
    'recent': 'articles',
    'categories': 'pages',
    'newsletter': 'cta',
    'testimonials': 'articles',
    'services': 'services',
    'work': 'pages',
  };
  const mapped = map[sectionId];
  if (mapped && SCHEMA[mapped]) return mapped;
  return null;
}

/** Get schema fields for a section ID (from SCHEMA global) */
function getSectionFields(sectionId) {
  const schemaId = sectionSchemaId(sectionId);
  if (!schemaId || !SCHEMA[schemaId]) return null;
  return SCHEMA[schemaId].fields || null;
}

/** Build inline editor HTML for a section */
function buildSectionEditor(sectionId) {
  const schemaId = sectionSchemaId(sectionId);
  const fields = getSectionFields(sectionId);
  if (!fields || Object.keys(fields).length === 0) {
    return '<div class="ts-section-no-fields">No editable fields for this section</div>';
  }

  let html = '';
  for (const [key, def] of Object.entries(fields)) {
    const val = (values[schemaId] && values[schemaId][key]) || def.default || '';
    const fieldId = 'sec-' + sectionId + '-' + key;

    html += '<div class="ts-field">';
    html += '<label for="' + fieldId + '">' + esc(def.label || key) + '</label>';

    if (def.type === 'textarea') {
      html += '<textarea id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '" rows="3">' + esc(val) + '</textarea>';
    } else if (def.type === 'image') {
      html += '<div class="ts-img-field">';
      html += '<div class="ts-img-preview" id="' + fieldId + '-preview">';
      if (val) {
        html += '<img src="' + esc(val) + '">';
      } else {
        html += '🖼️';
      }
      html += '</div>';
      html += '<button type="button" class="ts-img-btn" data-field-id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '">Choose Image</button>';
      html += '<input type="hidden" id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '" value="' + esc(val) + '">';
      html += '</div>';
    } else if (def.type === 'color') {
      html += '<div style="display:flex;gap:8px;align-items:center">';
      html += '<input type="color" id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '" value="' + esc(val || '#000000') + '" style="width:36px;height:30px;border:1px solid var(--ts-border);border-radius:4px;cursor:pointer;background:var(--ts-mantle);padding:2px">';
      html += '<input type="text" data-mirror="' + fieldId + '" value="' + esc(val) + '" style="flex:1" placeholder="#hex">';
      html += '</div>';
    } else if (def.type === 'toggle') {
      html += '<label class="ts-section-toggle" style="margin-top:4px">';
      html += '<input type="checkbox" id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '"' + (val ? ' checked' : '') + '>';
      html += '<span class="ts-section-toggle-track"></span>';
      html += '<span class="ts-section-toggle-thumb"></span>';
      html += '</label>';
    } else {
      html += '<input type="text" id="' + fieldId + '" data-section="' + sectionId + '" data-key="' + key + '" value="' + esc(val) + '" placeholder="' + esc(def.label || '') + '">';
    }

    html += '</div>';
  }

  html += '<div class="ts-section-editor-actions">';
  html += '<button type="button" class="ts-section-editor-save" data-section="' + sectionId + '">💾 Save Section</button>';
  html += '</div>';
  return html;
}

function renderSections() {
  sectionsList.innerHTML = '';
  
  sectionsData.forEach((sec) => {
    /* Wrapper div */
    const wrap = document.createElement('div');
    wrap.className = 'ts-section-item-wrap collapsed';
    wrap.dataset.id = sec.id;

    /* Header row (drag handle + icon + label + edit btn + toggle) */
    const item = document.createElement('div');
    item.className = 'ts-section-item' + (!sec.enabled ? ' disabled' : '');
    item.dataset.id = sec.id;
    item.draggable = true;
    
    const isRequired = !!sec.required;
    const hasFields = !!getSectionFields(sec.id);
    
    item.innerHTML =
      '<span class="ts-section-item-handle">⠿</span>' +
      '<span class="ts-section-item-icon">' + esc(sec.icon || '📋') + '</span>' +
      '<span class="ts-section-item-label">' + esc(sec.label || sec.id) + '</span>' +
      (isRequired ? '<span class="ts-section-item-badge">Required</span>' : '') +
      (hasFields ? '<button type="button" class="ts-section-edit-btn" title="Edit content">✏️</button>' : '') +
      '<label class="ts-section-toggle">' +
        '<input type="checkbox" ' + (sec.enabled ? 'checked' : '') + (isRequired ? ' checked disabled' : '') + '>' +
        '<span class="ts-section-toggle-track"></span>' +
        '<span class="ts-section-toggle-thumb"></span>' +
      '</label>';
    
    /* Toggle handler */
    const checkbox = item.querySelector('input[type="checkbox"]');
    if (!isRequired) {
      checkbox.addEventListener('change', () => {
        sec.enabled = checkbox.checked;
        item.classList.toggle('disabled', !checkbox.checked);
      });
    }

    /* Edit button → expand/collapse editor */
    const editBtn = item.querySelector('.ts-section-edit-btn');
    if (editBtn) {
      editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = !wrap.classList.contains('collapsed');
        /* Close all others */
        $$('.ts-section-item-wrap:not(.collapsed)', sectionsList).forEach(w => w.classList.add('collapsed'));
        if (!isOpen) {
          wrap.classList.remove('collapsed');
        }
      });
    }

    /* Editor panel */
    const editor = document.createElement('div');
    editor.className = 'ts-section-editor';
    if (hasFields) {
      editor.innerHTML = buildSectionEditor(sec.id);
      bindEditorEvents(editor, sec.id);
    }
    
    /* Drag & Drop — on the wrapper */
    item.addEventListener('dragstart', e => {
      draggedWrap = wrap;
      wrap.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', sec.id);
    });
    
    item.addEventListener('dragend', () => {
      wrap.classList.remove('dragging');
      $$('.ts-section-item-wrap.drag-over', sectionsList).forEach(el => el.classList.remove('drag-over'));
      draggedWrap = null;
    });
    
    wrap.addEventListener('dragover', e => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      if (draggedWrap && draggedWrap !== wrap) {
        wrap.classList.add('drag-over');
      }
    });
    
    wrap.addEventListener('dragleave', () => {
      wrap.classList.remove('drag-over');
    });
    
    wrap.addEventListener('drop', e => {
      e.preventDefault();
      wrap.classList.remove('drag-over');
      if (!draggedWrap || draggedWrap === wrap) return;
      
      const allWraps = [...sectionsList.querySelectorAll('.ts-section-item-wrap')];
      const fromIdx = allWraps.indexOf(draggedWrap);
      const toIdx = allWraps.indexOf(wrap);
      
      if (fromIdx < toIdx) {
        sectionsList.insertBefore(draggedWrap, wrap.nextSibling);
      } else {
        sectionsList.insertBefore(draggedWrap, wrap);
      }
      
      reorderSectionsData();
    });

    wrap.appendChild(item);
    wrap.appendChild(editor);
    sectionsList.appendChild(wrap);
  });
}

/** Bind events inside a section editor panel */
function bindEditorEvents(editorEl, sectionId) {
  /* Save button */
  const saveBtn = editorEl.querySelector('.ts-section-editor-save');
  if (saveBtn) {
    saveBtn.addEventListener('click', () => saveSectionContent(sectionId, editorEl));
  }

  /* Image buttons → open JTB Media Gallery */
  editorEl.querySelectorAll('.ts-img-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const fieldId = btn.dataset.fieldId;
      const secId = btn.dataset.section;
      const key = btn.dataset.key;
      openMediaForSection(fieldId, secId, key);
    });
  });

  /* Color mirror sync */
  editorEl.querySelectorAll('input[data-mirror]').forEach(mirror => {
    const colorInput = editorEl.querySelector('#' + mirror.dataset.mirror);
    if (!colorInput) return;
    mirror.addEventListener('input', () => { colorInput.value = mirror.value; });
    colorInput.addEventListener('input', () => { mirror.value = colorInput.value; });
  });

  /* Live preview on input change (debounced) */
  let previewTimer = null;
  editorEl.querySelectorAll('input[data-section], textarea[data-section]').forEach(inp => {
    inp.addEventListener('input', () => {
      clearTimeout(previewTimer);
      previewTimer = setTimeout(() => sendSectionPreview(sectionId, editorEl), 300);
    });
  });
  editorEl.querySelectorAll('input[type="checkbox"][data-section]').forEach(inp => {
    inp.addEventListener('change', () => sendSectionPreview(sectionId, editorEl));
  });
}

/** Collect field values from a section editor */
function collectSectionValues(editorEl) {
  const data = {};
  editorEl.querySelectorAll('[data-section][data-key]').forEach(el => {
    const key = el.dataset.key;
    if (el.type === 'checkbox') {
      data[key] = el.checked;
    } else {
      data[key] = el.value;
    }
  });
  return data;
}

/** Save section content to DB */
async function saveSectionContent(sectionId, editorEl) {
  const schemaId = sectionSchemaId(sectionId) || sectionId;
  const btn = editorEl.querySelector('.ts-section-editor-save');
  const fieldValues = collectSectionValues(editorEl);
  if (Object.keys(fieldValues).length === 0) return;

  btn.disabled = true;
  btn.textContent = 'Saving…';

  try {
    const payload = { data: { [schemaId]: fieldValues } };
    const res = await api('POST', 'save', payload);
    if (res.ok) {
      /* Update local values cache */
      if (!values[schemaId]) values[schemaId] = {};
      Object.assign(values[schemaId], fieldValues);

      btn.textContent = '✅ Saved!';
      toast('Section "' + sectionId + '" saved', 'success');
      /* Reload preview */
      dom.iframe.src = dom.iframe.src;
      setTimeout(() => { btn.textContent = '💾 Save Section'; btn.disabled = false; }, 1500);
    } else {
      throw new Error(res.error || 'Save failed');
    }
  } catch (e) {
    btn.textContent = '❌ Error';
    toast('Save failed: ' + e.message, 'error');
    setTimeout(() => { btn.textContent = '💾 Save Section'; btn.disabled = false; }, 2000);
  }
}

/** Send live preview update via postMessage */
function sendSectionPreview(sectionId, editorEl) {
  const schemaId = sectionSchemaId(sectionId) || sectionId;
  const fieldValues = collectSectionValues(editorEl);
  const iframeWin = dom.iframe.contentWindow;
  if (!iframeWin) return;

  /* Send each field as a postMessage update (use schemaId for data-ts matching) */
  for (const [key, val] of Object.entries(fieldValues)) {
    iframeWin.postMessage({
      type: 'themeStudio',
      action: 'update',
      section: schemaId,
      field: key,
      value: val,
    }, '*');
  }
}

/** Open media gallery for section image field */
function openMediaForSection(fieldId, sectionId, key) {
  if (typeof JTB_MediaGallery !== 'undefined') {
    JTB_MediaGallery.open(function(url) {
      const input = document.getElementById(fieldId);
      const preview = document.getElementById(fieldId + '-preview');
      if (input) input.value = url;
      if (preview) preview.innerHTML = '<img src="' + esc(url) + '">';
      /* Trigger preview */
      const editor = input.closest('.ts-section-editor');
      if (editor) sendSectionPreview(sectionId, editor);
    });
  } else {
    /* Fallback: simple prompt */
    const url = prompt('Enter image URL:');
    if (url) {
      const input = document.getElementById(fieldId);
      const preview = document.getElementById(fieldId + '-preview');
      if (input) input.value = url;
      if (preview) preview.innerHTML = '<img src="' + esc(url) + '">';
    }
  }
}

function reorderSectionsData() {
  const orderedIds = [...sectionsList.querySelectorAll('.ts-section-item-wrap')].map(el => el.dataset.id);
  const newData = [];
  orderedIds.forEach(id => {
    const sec = sectionsData.find(s => s.id === id);
    if (sec) newData.push(sec);
  });
  sectionsData = newData;
}

/* Save sections */
sectionsSaveBtn.addEventListener('click', async () => {
  reorderSectionsData();
  
  const order = sectionsData.map(s => s.id);
  const enabled = {};
  sectionsData.forEach(s => { enabled[s.id] = !!s.enabled; });
  
  sectionsSaveBtn.disabled = true;
  sectionsSaveBtn.textContent = 'Saving…';
  
  try {
    const res = await api('POST', 'sections/save', { order, enabled });
    if (res.ok) {
      sectionsSaveBtn.classList.add('saved');
      sectionsSaveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> Saved!';
      toast('Section order saved', 'success');
      /* Reload the preview iframe */
      dom.iframe.src = dom.iframe.src;
      setTimeout(() => {
        sectionsSaveBtn.classList.remove('saved');
        sectionsSaveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> Save Section Order';
        sectionsSaveBtn.disabled = false;
      }, 2000);
    } else {
      throw new Error(res.error || 'Save failed');
    }
  } catch (e) {
    toast('Failed to save sections: ' + e.message, 'error');
    sectionsSaveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> Save Section Order';
    sectionsSaveBtn.disabled = false;
  }
});


/* Report readiness */
console.log('[Theme Studio] Initialized for theme:', THEME_SLUG);

})();
</script>

<?php
// JTB Media Gallery Modal
$jtbMediaPath = CMS_ROOT . '/plugins/jessie-theme-builder/includes/jtb-media-gallery.php';
if (file_exists($jtbMediaPath)) {
    require_once $jtbMediaPath;
    $pexelsKey = '';
    try {
        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'pexels_api_key'");
        $stmt->execute();
        $pexelsKey = $stmt->fetchColumn() ?: '';
    } catch (\Throwable $e) {}
    jtb_render_media_gallery_modal($csrfToken ?? '', $pexelsKey);
}
?>
<script src="/plugins/jessie-theme-builder/assets/js/media-gallery.js"></script>
</body>
</html>
