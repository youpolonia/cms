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
.ts-section-reset{
  background:none;border:none;color:var(--ts-subtext);cursor:pointer;
  font-size:14px;padding:2px 6px;border-radius:4px;opacity:0;
  transition:opacity .15s,color .15s,background .15s;margin-left:auto;margin-right:4px;
}
.ts-section-header:hover .ts-section-reset{opacity:1}
.ts-section-reset:hover{color:var(--ts-red);background:rgba(243,139,168,.1)}
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

/* ── Range Slider Field ───────────────────────────────── */
.ts-range-wrap{display:flex;align-items:center;gap:12px;width:100%}
.ts-range{
  -webkit-appearance:none;appearance:none;flex:1;height:6px;
  background:var(--ts-overlay);border-radius:3px;outline:none;cursor:pointer;
}
.ts-range::-webkit-slider-thumb{
  -webkit-appearance:none;width:18px;height:18px;border-radius:50%;
  background:var(--ts-blue);border:2px solid var(--ts-surface);
  box-shadow:0 1px 4px rgba(0,0,0,.3);cursor:grab;transition:transform .15s;
}
.ts-range::-webkit-slider-thumb:hover{transform:scale(1.15)}
.ts-range::-webkit-slider-thumb:active{cursor:grabbing;transform:scale(1.1)}
.ts-range::-moz-range-thumb{
  width:18px;height:18px;border-radius:50%;border:2px solid var(--ts-surface);
  background:var(--ts-blue);box-shadow:0 1px 4px rgba(0,0,0,.3);cursor:grab;
}
.ts-range::-moz-range-track{background:var(--ts-overlay);height:6px;border-radius:3px}
.ts-range-val{
  min-width:48px;padding:4px 8px;text-align:center;
  font-size:12px;font-family:'SF Mono',Monaco,Consolas,monospace;
  color:var(--ts-text);background:var(--ts-bg);
  border:1px solid var(--ts-border);border-radius:var(--ts-radius-sm);
}
.ts-range-unit{font-size:11px;color:var(--ts-subtext);margin-left:-4px}

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
.ts-sections-panel{padding:0;display:flex;flex-direction:column;height:100%}
.ts-sections-header{
  padding:16px 16px 12px;border-bottom:1px solid var(--ts-border);
}
.ts-sections-header h3{
  font-size:14px;font-weight:700;color:var(--ts-text);margin-bottom:2px;
}
.ts-sections-header p{
  font-size:11px;color:var(--ts-subtext);line-height:1.4;
}
.ts-sections-list{
  padding:8px;min-height:100px;flex:1;overflow-y:auto;
}
.ts-sections-actions{padding:8px;border-top:1px solid var(--ts-border)}
.ts-sections-save{
  display:flex;align-items:center;justify-content:center;gap:8px;
  width:100%;padding:10px 16px;
  background:var(--ts-blue);color:var(--ts-bg);border:none;
  border-radius:var(--ts-radius-sm);font-size:13px;font-weight:600;
  cursor:pointer;transition:all var(--ts-transition);font-family:inherit;
}
.ts-sections-save:hover{background:#a8c7fc;box-shadow:0 0 12px rgba(137,180,250,.3)}
.ts-sections-save:disabled{opacity:0.5;cursor:default}
.ts-sections-save.saved{background:var(--ts-green)}

/* Section card */
.ts-sec{margin-bottom:6px;border-radius:var(--ts-radius-sm);overflow:hidden;
  border:1px solid var(--ts-border);transition:border-color 0.2s}
.ts-sec:hover{border-color:rgba(137,180,250,.3)}
.ts-sec.open{border-color:var(--ts-blue)}
.ts-sec.drag-over{border-color:var(--ts-green);box-shadow:0 0 0 2px rgba(166,227,161,.15)}
.ts-sec.dragging{opacity:0.4}
.ts-sec.off{opacity:0.45}
.ts-sec.off .ts-sec-label{text-decoration:line-through;color:var(--ts-subtext)}

/* Section header row */
.ts-sec-head{
  display:flex;align-items:center;gap:8px;
  padding:9px 10px;background:var(--ts-bg);
  cursor:pointer;user-select:none;
}
.ts-sec-drag{color:var(--ts-overlay);font-size:14px;cursor:grab;width:18px;
  text-align:center;flex-shrink:0;line-height:1;letter-spacing:-1px}
.ts-sec-drag:active{cursor:grabbing}
.ts-sec-icon{font-size:16px;flex-shrink:0;width:22px;text-align:center}
.ts-sec-label{flex:1;font-size:13px;font-weight:600;color:var(--ts-text);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ts-sec-badge{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;
  padding:2px 5px;border-radius:3px;flex-shrink:0;
  background:rgba(203,166,247,.12);color:var(--ts-mauve)}
.ts-sec-chevron{width:16px;height:16px;flex-shrink:0;color:var(--ts-subtext);
  transition:transform 0.2s;transform:rotate(0deg)}
.ts-sec.open .ts-sec-chevron{transform:rotate(90deg);color:var(--ts-blue)}

/* Toggle switch */
.ts-sw{position:relative;width:34px;height:18px;flex-shrink:0;cursor:pointer;display:block}
.ts-sw input{position:absolute;opacity:0;width:0;height:0}
.ts-sw-track{position:absolute;inset:0;border-radius:9px;background:var(--ts-overlay);transition:background 0.2s}
.ts-sw input:checked+.ts-sw-track{background:var(--ts-green)}
.ts-sw-dot{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;
  background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.3);transition:transform 0.2s;pointer-events:none}
.ts-sw input:checked~.ts-sw-dot{transform:translateX(16px)}
.ts-sw input:disabled{cursor:not-allowed}
.ts-sw input:disabled+.ts-sw-track{background:var(--ts-green);opacity:0.5}
.ts-sw input:disabled~.ts-sw-dot{transform:translateX(16px)}

/* Section editor body */
.ts-sec-body{display:none;padding:0 12px 12px;background:rgba(24,24,37,.7);
  border-top:1px solid var(--ts-border)}
.ts-sec.open .ts-sec-body{display:block}
.ts-sec-fields{padding-top:10px}

/* Fields */
.ts-sf{margin-bottom:10px}
.ts-sf:last-child{margin-bottom:0}
.ts-sf-label{display:flex;align-items:center;gap:6px;margin-bottom:5px;
  font-size:10.5px;font-weight:700;color:var(--ts-subtext);
  text-transform:uppercase;letter-spacing:0.5px}
.ts-sf-label .ts-sf-type{font-weight:500;color:var(--ts-overlay);font-size:9px;
  padding:1px 4px;border-radius:3px;background:rgba(69,71,90,.5);text-transform:lowercase;letter-spacing:0}
.ts-sf input[type="text"],.ts-sf textarea{
  display:block;width:100%;padding:8px 10px;font-size:13px;font-family:inherit;
  background:var(--ts-mantle);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);transition:all 0.15s;
  box-sizing:border-box;
}
.ts-sf input:focus,.ts-sf textarea:focus{
  border-color:var(--ts-blue);outline:none;box-shadow:0 0 0 2px rgba(137,180,250,.12)}
.ts-sf textarea{min-height:64px;resize:vertical;line-height:1.5}

/* Image field */
.ts-sf-img{display:flex;align-items:center;gap:10px}
.ts-sf-img-thumb{width:56px;height:56px;border-radius:8px;border:1px dashed var(--ts-border);
  background:var(--ts-mantle);overflow:hidden;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;color:var(--ts-overlay);transition:border-color 0.15s}
.ts-sf-img-thumb:hover{border-color:var(--ts-blue)}
.ts-sf-img-thumb img{width:100%;height:100%;object-fit:cover}
.ts-sf-img-actions{display:flex;flex-direction:column;gap:4px}
.ts-sf-img-btn{padding:5px 10px;font-size:11px;font-weight:600;
  background:var(--ts-surface);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  cursor:pointer;transition:all 0.15s;font-family:inherit;text-align:left}
.ts-sf-img-btn:hover{border-color:var(--ts-blue);color:var(--ts-blue)}
.ts-sf-img-clear{padding:3px 8px;font-size:10px;color:var(--ts-red);
  background:none;border:none;cursor:pointer;font-family:inherit}
.ts-sf-img-clear:hover{text-decoration:underline}

/* Section save bar */
.ts-sec-save-bar{display:flex;gap:8px;margin-top:12px;padding-top:10px;
  border-top:1px solid var(--ts-border)}
.ts-sec-save-btn{
  flex:1;display:flex;align-items:center;justify-content:center;gap:6px;
  padding:8px 14px;font-size:12px;font-weight:600;
  background:var(--ts-green);color:var(--ts-crust);border:none;
  border-radius:var(--ts-radius-sm);cursor:pointer;font-family:inherit;transition:all 0.15s}
.ts-sec-save-btn:hover{filter:brightness(1.1)}
.ts-sec-save-btn:disabled{opacity:0.5;cursor:default}
.ts-sec-save-btn.saving{background:var(--ts-blue)}
.ts-sec-save-btn.saved{background:var(--ts-green)}
.ts-sec-save-btn.error{background:var(--ts-red)}
.ts-sec-nofields{padding:14px;text-align:center;font-size:12px;
  color:var(--ts-subtext);font-style:italic}

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

/* ── Font Picker ──────────────────────────────────────── */
.ts-font-picker{position:relative;width:100%}
.ts-font-picker-selected{
  display:flex;align-items:center;justify-content:space-between;
  width:100%;padding:9px 32px 9px 12px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-text);
  font-size:13px;cursor:pointer;
  transition:border-color var(--ts-transition);
  user-select:none;position:relative;
}
.ts-font-picker-selected:hover{border-color:var(--ts-blue)}
.ts-font-picker-selected::after{
  content:'';position:absolute;right:12px;top:50%;
  transform:translateY(-50%);
  border:5px solid transparent;border-top-color:var(--ts-subtext);
  pointer-events:none;
}
.ts-font-picker.open .ts-font-picker-selected{
  border-color:var(--ts-blue);
  border-radius:var(--ts-radius-sm) var(--ts-radius-sm) 0 0;
}
.ts-font-picker.open .ts-font-picker-selected::after{
  border-top-color:transparent;border-bottom-color:var(--ts-subtext);
  transform:translateY(-75%);
}
.ts-font-picker-dropdown{
  display:none;position:absolute;top:100%;left:0;right:0;
  background:var(--ts-surface);border:1px solid var(--ts-blue);
  border-top:none;border-radius:0 0 var(--ts-radius-sm) var(--ts-radius-sm);
  z-index:200;max-height:280px;
  box-shadow:0 8px 24px rgba(0,0,0,.35);
  overflow:hidden;
  display:flex;flex-direction:column;
}
.ts-font-picker:not(.open) .ts-font-picker-dropdown{display:none}
.ts-font-picker.open .ts-font-picker-dropdown{display:flex}
.ts-font-search{
  width:100%;padding:8px 12px;
  background:var(--ts-bg);border:none;border-bottom:1px solid var(--ts-border);
  color:var(--ts-text);font-size:12px;outline:none;
  font-family:inherit;flex-shrink:0;
}
.ts-font-search::placeholder{color:var(--ts-subtext)}
.ts-font-options{
  overflow-y:auto;flex:1;overscroll-behavior:contain;
  scrollbar-width:thin;scrollbar-color:var(--ts-overlay) transparent;
}
.ts-font-option{
  padding:8px 12px;cursor:pointer;font-size:14px;
  color:var(--ts-text);transition:background 0.15s;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.ts-font-option:hover{background:var(--ts-overlay)}
.ts-font-option.active{background:rgba(59,130,246,.15);color:var(--ts-blue)}

/* ── Font Pairing Suggestions ─────────────────────────── */
.ts-font-pairings{
  margin-top:6px;display:flex;flex-wrap:wrap;gap:4px;
  align-items:center;
}
.ts-font-pairings-label{
  font-size:10px;color:var(--ts-subtext);margin-right:2px;
}
.ts-font-pair-chip{
  font-size:11px;padding:3px 8px;border-radius:12px;
  background:var(--ts-overlay);color:var(--ts-text);
  border:none;cursor:pointer;transition:all .15s;
  font-family:inherit;
}
.ts-font-pair-chip:hover{
  background:var(--ts-blue);color:var(--ts-bg);
}

/* ── Color from Image ─────────────────────────────────── */
.ts-extract-btn{
  display:flex;align-items:center;gap:6px;
  width:100%;padding:8px 12px;margin-top:8px;
  background:var(--ts-overlay);border:1px dashed var(--ts-border);
  border-radius:var(--ts-radius-sm);color:var(--ts-subtext);
  font-size:12px;cursor:pointer;transition:all .15s;
  font-family:inherit;
}
.ts-extract-btn:hover{
  border-color:var(--ts-blue);color:var(--ts-text);
  background:rgba(137,180,250,.1);
}
.ts-extracted-row{
  display:flex;gap:4px;align-items:center;
  margin-top:8px;padding:8px;
  background:var(--ts-bg);border-radius:var(--ts-radius-sm);
  border:1px solid var(--ts-border);
}
.ts-extracted-dot{
  width:24px;height:24px;border-radius:50%;
  border:2px solid rgba(255,255,255,.15);
  flex-shrink:0;cursor:pointer;
  transition:transform .15s;
}
.ts-extracted-dot:hover{transform:scale(1.2)}
.ts-extracted-apply{
  margin-left:auto;font-size:11px;padding:4px 10px;
  background:var(--ts-blue);color:var(--ts-bg);
  border:none;border-radius:12px;cursor:pointer;
  font-weight:600;transition:background .15s;
}
.ts-extracted-apply:hover{background:#a8c7fc}

/* ── Compare / Before-After Split ─────────────────────── */
.ts-compare-active #ts-preview{position:relative;overflow:hidden}
.ts-compare-wrap{
  position:absolute;inset:0;z-index:50;
  display:flex;background:var(--ts-bg);
}
.ts-compare-before,
.ts-compare-after{
  flex:1;overflow:hidden;position:relative;
}
.ts-compare-before{border-right:2px solid var(--ts-blue)}
.ts-compare-label{
  position:absolute;top:8px;z-index:2;
  padding:4px 12px;border-radius:12px;
  font-size:11px;font-weight:700;
  text-transform:uppercase;letter-spacing:0.5px;
}
.ts-compare-before .ts-compare-label{left:8px;background:var(--ts-overlay);color:var(--ts-subtext)}
.ts-compare-after .ts-compare-label{right:8px;background:var(--ts-blue);color:var(--ts-bg)}
.ts-compare-divider{
  position:absolute;top:0;bottom:0;width:4px;
  background:var(--ts-blue);cursor:col-resize;
  z-index:51;left:50%;transform:translateX(-50%);
}
.ts-compare-divider::after{
  content:'⇔';position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%);
  background:var(--ts-blue);color:var(--ts-bg);
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;font-weight:bold;
}
.ts-compare-wrap iframe{
  width:100%;height:100%;border:none;
}

/* ── Gradient Builder ─────────────────────────────────── */
.ts-gradient-builder{
  padding:8px 0;
}
.ts-gradient-preview{
  height:40px;border-radius:var(--ts-radius-sm);
  border:1px solid var(--ts-border);margin-bottom:10px;
}
.ts-gradient-dir{
  display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;
}
.ts-gradient-dir-btn{
  width:30px;height:30px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:4px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;color:var(--ts-subtext);
  transition:all .15s;
}
.ts-gradient-dir-btn:hover{border-color:var(--ts-blue);color:var(--ts-text)}
.ts-gradient-dir-btn.active{
  background:var(--ts-blue);color:var(--ts-bg);border-color:var(--ts-blue);
}
.ts-gradient-stops{
  display:flex;flex-direction:column;gap:6px;
}
.ts-gradient-stop{
  display:flex;align-items:center;gap:6px;
}
.ts-gradient-stop-swatch{
  width:28px;height:28px;border-radius:4px;
  border:1px solid var(--ts-border);cursor:pointer;
  flex-shrink:0;
}
.ts-gradient-stop-pos{
  width:50px;padding:4px 6px;
  background:var(--ts-bg);border:1px solid var(--ts-border);
  border-radius:4px;color:var(--ts-text);font-size:12px;
  text-align:center;outline:none;
}
.ts-gradient-stop-remove{
  background:none;border:none;color:var(--ts-red);
  cursor:pointer;font-size:16px;opacity:0.6;
  transition:opacity .15s;
}
.ts-gradient-stop-remove:hover{opacity:1}
.ts-gradient-add-stop{
  font-size:11px;padding:4px 10px;
  background:var(--ts-overlay);color:var(--ts-text);
  border:1px dashed var(--ts-border);border-radius:4px;
  cursor:pointer;transition:all .15s;margin-top:4px;
  font-family:inherit;
}
.ts-gradient-add-stop:hover{border-color:var(--ts-blue);color:var(--ts-blue)}

/* ── Box Shadow Editor ────────────────────────────────── */
.ts-shadow-editor{padding:8px 0}
.ts-shadow-preview-box{
  width:80px;height:60px;margin:0 auto 12px;
  background:var(--ts-surface);border-radius:8px;
  transition:box-shadow .2s;
}
.ts-shadow-row{
  display:flex;align-items:center;gap:8px;margin-bottom:8px;
}
.ts-shadow-row label{
  font-size:11px;color:var(--ts-subtext);width:50px;
  flex-shrink:0;text-align:right;
}
.ts-shadow-row input[type="range"]{flex:1}
.ts-shadow-row .ts-shadow-val{
  font-size:11px;color:var(--ts-text);width:36px;text-align:right;
}

/* ── Spacing Visual Editor (Box Model) ────────────────── */
.ts-spacing-editor{padding:8px 0}
.ts-box-model{
  position:relative;width:100%;max-width:280px;
  margin:0 auto;user-select:none;
}
.ts-box-layer{
  position:relative;
  border:2px dashed;
  display:flex;align-items:center;justify-content:center;
  min-height:40px;
}
.ts-box-margin{
  border-color:rgba(249,115,22,.5);
  background:rgba(249,115,22,.08);
  padding:16px;
}
.ts-box-padding{
  border-color:rgba(34,197,94,.5);
  background:rgba(34,197,94,.08);
  padding:16px;
  width:100%;
}
.ts-box-content{
  background:var(--ts-overlay);
  border-radius:4px;padding:8px;
  text-align:center;font-size:10px;
  color:var(--ts-subtext);
  width:100%;
}
.ts-box-label{
  position:absolute;top:2px;left:4px;
  font-size:9px;font-weight:700;
  text-transform:uppercase;letter-spacing:0.5px;
}
.ts-box-margin > .ts-box-label{color:rgb(249,115,22)}
.ts-box-padding > .ts-box-label{color:rgb(34,197,94)}
.ts-box-val{
  position:absolute;
  background:transparent;border:none;
  color:var(--ts-text);font-size:11px;
  text-align:center;width:32px;
  font-family:inherit;outline:none;
  padding:1px;
}
.ts-box-val:focus{
  background:var(--ts-bg);border-radius:3px;
  box-shadow:0 0 0 2px var(--ts-blue);
}
.ts-box-val-top{top:2px;left:50%;transform:translateX(-50%)}
.ts-box-val-right{right:2px;top:50%;transform:translateY(-50%)}
.ts-box-val-bottom{bottom:2px;left:50%;transform:translateX(-50%)}
.ts-box-val-left{left:2px;top:50%;transform:translateY(-50%)}

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

    <!-- Compare (Before/After) -->
    <button class="ts-icon-btn" id="ts-compare-btn" title="Before/After compare (split view)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="3" x2="12" y2="21"/></svg>
    </button>

    <div class="ts-topbar-sep"></div>

    <button class="ts-btn ts-btn-ghost" id="ts-export-btn" title="Export theme settings">⬇ Export</button>
    <button class="ts-btn ts-btn-ghost" id="ts-import-btn" title="Import theme settings">⬆ Import</button>
    <input type="file" id="ts-import-file" accept=".json" style="display:none">
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
            <p>Drag to reorder · Toggle visibility · Click to edit content</p>
          </div>
          <div class="ts-sections-list" id="sections-list">
            <!-- Populated by JS -->
          </div>
          <div class="ts-sections-actions">
            <button class="ts-sections-save" id="sections-save-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
              Save Layout
            </button>
          </div>
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


<!-- Hidden canvas for color sampling -->
<canvas id="ts-extract-canvas" style="display:none"></canvas>

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

/* Override numeric fields → range sliders with proper min/max/step/unit */
const RANGE_OVERRIDES = {
  'effects.shadow_strength':   { type:'range', min:0,   max:100,  step:1,   unit:'%',  label:'Shadow Strength' },
  'effects.hover_scale':       { type:'range', min:1.0, max:1.2,  step:0.01,unit:'×',  label:'Hover Scale' },
  'effects.transition_speed':  { type:'range', min:50,  max:800,  step:10,  unit:'ms', label:'Transition Speed' },
  'buttons.border_radius':     { type:'range', min:0,   max:30,   step:1,   unit:'px', label:'Border Radius' },
  'buttons.padding_x':         { type:'range', min:8,   max:60,   step:2,   unit:'px', label:'Padding X' },
  'buttons.padding_y':         { type:'range', min:4,   max:30,   step:1,   unit:'px', label:'Padding Y' },
  'layout.container_width':    { type:'range', min:800, max:1600, step:20,  unit:'px', label:'Container Width' },
  'layout.section_spacing':    { type:'range', min:20,  max:200,  step:5,   unit:'px', label:'Section Spacing' },
  'layout.border_radius':      { type:'range', min:0,   max:40,   step:1,   unit:'px', label:'Border Radius' },
  'typography.base_font_size': { type:'range', min:12,  max:24,   step:1,   unit:'px', label:'Base Font Size' },
  'typography.line_height':    { type:'range', min:1.0, max:2.5,  step:0.05,unit:'',   label:'Line Height' },
};
Object.entries(RANGE_OVERRIDES).forEach(([path, cfg]) => {
  const [sec, key] = path.split('.');
  if (SCHEMA[sec]?.fields?.[key]) Object.assign(SCHEMA[sec].fields[key], cfg);
});

/* ── Google Fonts – top 60 popular fonts (hardcoded, no API call) ── */
const GOOGLE_FONTS = [
  'Inter','Roboto','Open Sans','Lato','Montserrat','Poppins','Raleway',
  'Playfair Display','Merriweather','Source Sans Pro','Nunito','Work Sans',
  'DM Sans','Space Grotesk','Outfit','Plus Jakarta Sans','Sora','Manrope',
  'Unbounded','Clash Display','IBM Plex Sans','Noto Sans','Mulish','Quicksand',
  'Karla','Oswald','Roboto Condensed','Roboto Slab','PT Sans','Ubuntu',
  'Fira Sans','Rubik','Barlow','Josefin Sans','Cabin','Libre Baskerville',
  'Bitter','EB Garamond','Crimson Text','Lora','Cormorant Garamond',
  'DM Serif Display','Arvo','Vollkorn','Spectral','Alegreya',
  'Archivo','Red Hat Display','Lexend','Jost','Albert Sans','Figtree',
  'Geist','Satoshi','General Sans','Bricolage Grotesque','Instrument Sans',
  'Onest','Schibsted Grotesk','Wix Madefor Display'
];

const _loadedGoogleFonts = new Set();
function loadGoogleFont(fontName) {
  if (!fontName || _loadedGoogleFonts.has(fontName)) return;
  _loadedGoogleFonts.add(fontName);
  const family = fontName.replace(/ /g, '+');
  const url = `https://fonts.googleapis.com/css2?family=${family}:wght@400;500;600;700;800&display=swap`;
  // Load in parent document
  const link = document.createElement('link');
  link.rel = 'stylesheet'; link.href = url;
  document.head.appendChild(link);
  // Load in iframe too
  try {
    const iframeDoc = dom.iframe?.contentDocument || dom.iframe?.contentWindow?.document;
    if (iframeDoc) {
      const iLink = iframeDoc.createElement('link');
      iLink.rel = 'stylesheet'; iLink.href = url;
      iframeDoc.head.appendChild(iLink);
    }
  } catch(e) { /* cross-origin silenced */ }
}

/* Override heading_font and body_font to use fontpicker */
if (SCHEMA.typography?.fields?.heading_font) {
  SCHEMA.typography.fields.heading_font.type = 'fontpicker';
}
if (SCHEMA.typography?.fields?.body_font) {
  SCHEMA.typography.fields.body_font.type = 'fontpicker';
}

/* ── Add missing schema fields (favicon, OG image, announcement bar) ── */
if (SCHEMA.brand) {
  if (!SCHEMA.brand.fields.favicon) SCHEMA.brand.fields.favicon = { type:'image', label:'Favicon', default:null };
  if (!SCHEMA.brand.fields.og_image) SCHEMA.brand.fields.og_image = { type:'image', label:'Social Share Image (OG)', default:null };
}
if (!SCHEMA.announcement) {
  SCHEMA.announcement = { label:'Announcement Bar', fields:{
    enabled:  { type:'toggle',   label:'Show Announcement Bar', default:false },
    text:     { type:'text',     label:'Announcement Text', default:'' },
    link:     { type:'text',     label:'Link URL', default:'' },
    bg_color: { type:'color',    label:'Background Color', default:'#6366f1' },
    text_color:{ type:'color',   label:'Text Color', default:'#ffffff' },
  }};
}

/* ── Inject gradient field into Effects ── */
if (SCHEMA.effects && !SCHEMA.effects.fields.gradient) {
  SCHEMA.effects.fields.gradient = { type:'gradient', label:'Background Gradient', default:'' };
}
/* ── Inject box_shadow field into Effects ── */
if (SCHEMA.effects && !SCHEMA.effects.fields.box_shadow) {
  SCHEMA.effects.fields.box_shadow = { type:'boxshadow', label:'Box Shadow', default:'' };
}
/* ── Inject spacing fields into Layout ── */
if (SCHEMA.layout) {
  if (!SCHEMA.layout.fields.section_padding) SCHEMA.layout.fields.section_padding = { type:'spacing', label:'Section Spacing (Box Model)', default:'', _kind:'combined' };
}

/* ── Font Pairing Data ── */
const FONT_PAIRINGS = {
  'Inter':['Lora','Merriweather','Crimson Text'],
  'Roboto':['Roboto Slab','Open Sans','Lato'],
  'Montserrat':['Open Sans','Lora','Source Sans Pro'],
  'Poppins':['Lato','Nunito','Open Sans'],
  'Playfair Display':['Source Sans Pro','Lato','Raleway'],
  'Merriweather':['Open Sans','Lato','Nunito'],
  'Raleway':['Lora','Merriweather','Open Sans'],
  'DM Sans':['DM Serif Display','Lora','Source Sans Pro'],
  'Space Grotesk':['Inter','Work Sans','Nunito'],
  'Outfit':['Lora','Source Sans Pro','Inter'],
  'Plus Jakarta Sans':['Crimson Text','Lora','Inter'],
  'Sora':['Inter','DM Serif Display','Lato'],
  'Manrope':['Lora','Spectral','Inter'],
  'Oswald':['Open Sans','Lato','Source Sans Pro'],
  'Lato':['Merriweather','Playfair Display','Lora'],
  'Work Sans':['Lora','DM Serif Display','Source Sans Pro'],
  'Barlow':['Lora','Crimson Text','Open Sans'],
  'Josefin Sans':['Lora','Open Sans','Lato'],
  'Libre Baskerville':['Lato','Raleway','Open Sans'],
  'EB Garamond':['Open Sans','Lato','Work Sans'],
  'Cormorant Garamond':['Poppins','Raleway','Montserrat'],
  'DM Serif Display':['DM Sans','Inter','Nunito'],
  'Arvo':['Lato','Open Sans','Source Sans Pro'],
  'Lexend':['Inter','Lora','Open Sans'],
  'Jost':['Lora','Source Sans Pro','Inter'],
  'Red Hat Display':['Open Sans','Lato','Inter'],
  'Figtree':['Merriweather','Lora','Inter'],
  'Rubik':['Lora','Open Sans','Nunito'],
  'Nunito':['Playfair Display','Merriweather','Lora'],
  'Quicksand':['Lora','Crimson Text','EB Garamond'],
};

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
  exportBtn       : $('#ts-export-btn'),
  importBtn       : $('#ts-import-btn'),
  importFile      : $('#ts-import-file'),
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

/* New feature elements */
const domCompare    = $('#ts-compare-btn');
const domExtractCanvas = $('#ts-extract-canvas');

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

  const hex = (section && field) ? (getVal(section, field) || '#3b82f6') : (hexInput.value || '#3b82f6');
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
  if (section && field) onFieldChange(section, field, hex);
  /* Fire custom event for non-standard color picker users */
  hexEl.dispatchEvent(new Event('input', {bubbles:true}));
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
  /* ── Popular ───────────────────── */
  { name: 'Purple Dream', primary: '#7c3aed', secondary: '#a78bfa', accent: '#f59e0b' },
  { name: 'Ocean Blue',   primary: '#0ea5e9', secondary: '#06b6d4', accent: '#14b8a6' },
  { name: 'Forest Green', primary: '#16a34a', secondary: '#22c55e', accent: '#eab308' },
  { name: 'Sunset',       primary: '#f97316', secondary: '#ef4444', accent: '#fbbf24' },
  { name: 'Corporate',    primary: '#1e40af', secondary: '#3b82f6', accent: '#6366f1' },
  { name: 'Rose',         primary: '#e11d48', secondary: '#f43f5e', accent: '#ec4899' },
  /* ── Restaurant / Food ─────────── */
  { name: 'Warm Gold',    primary: '#d4a574', secondary: '#c49464', accent: '#fef3c7' },
  { name: 'Olive Garden', primary: '#65a30d', secondary: '#84cc16', accent: '#d4a574' },
  { name: 'Wine & Dine',  primary: '#9f1239', secondary: '#be123c', accent: '#fbbf24' },
  /* ── Tech / SaaS ───────────────── */
  { name: 'Indigo SaaS',  primary: '#6366f1', secondary: '#818cf8', accent: '#06b6d4' },
  { name: 'Neon Cyber',   primary: '#8b5cf6', secondary: '#a78bfa', accent: '#22d3ee' },
  { name: 'Mint Tech',    primary: '#059669', secondary: '#34d399', accent: '#3b82f6' },
  /* ── Medical / Health ──────────── */
  { name: 'Clean Medical', primary: '#0284c7', secondary: '#38bdf8', accent: '#10b981' },
  { name: 'Wellness',     primary: '#14b8a6', secondary: '#5eead4', accent: '#f0abfc' },
  /* ── Photography / Creative ────── */
  { name: 'Monochrome',   primary: '#a3a3a3', secondary: '#737373', accent: '#fbbf24' },
  { name: 'Dark Studio',  primary: '#f5f5f5', secondary: '#d4d4d4', accent: '#ef4444' },
  { name: 'Pastel Dream', primary: '#c084fc', secondary: '#f9a8d4', accent: '#67e8f9' },
  /* ── Real Estate / Business ────── */
  { name: 'Navy Trust',   primary: '#1e3a5f', secondary: '#2563eb', accent: '#f59e0b' },
  { name: 'Emerald Pro',  primary: '#047857', secondary: '#10b981', accent: '#fbbf24' },
  /* ── E-commerce ────────────────── */
  { name: 'Luxury Black',  primary: '#1c1917', secondary: '#44403c', accent: '#d4a574' },
  { name: 'Shopify Green', primary: '#008060', secondary: '#5cbf7c', accent: '#f97316' },
  /* ── Education / Non-profit ────── */
  { name: 'Campus Blue',  primary: '#1d4ed8', secondary: '#60a5fa', accent: '#f59e0b' },
  { name: 'Charity Warm', primary: '#dc2626', secondary: '#f87171', accent: '#fbbf24' },
  /* ── Dark Themes ───────────────── */
  { name: 'Midnight',     primary: '#818cf8', secondary: '#6366f1', accent: '#f472b6' },
  { name: 'Dracula',      primary: '#bd93f9', secondary: '#ff79c6', accent: '#50fa7b' },
  { name: 'Nord',         primary: '#88c0d0', secondary: '#81a1c1', accent: '#a3be8c' },
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
  /* Content-only sections go in Section Manager, not Design tab.
     hero is a homepage section — editable via Sections tab only. */
  const DESIGN_SECTIONS = new Set(['brand','announcement','header','footer','typography','buttons','layout','effects','custom_css','theme_info']);

  entries.filter(([k]) => DESIGN_SECTIONS.has(k)).forEach(([sectionKey, section], idx) => {
    const sEl = document.createElement('div');
    sEl.className = 'ts-section' + (idx === 0 ? ' open' : '');
    sEl.dataset.section = sectionKey;

    /* Section header */
    const header = document.createElement('div');
    header.className = 'ts-section-header';
    header.innerHTML =
      '<span class="ts-section-icon">' + (section.icon || '⚙️') + '</span>' +
      '<span class="ts-section-label">' + esc(section.label || sectionKey) + '</span>' +
      '<button class="ts-section-reset" title="Reset this section to defaults">↩</button>' +
      '<svg class="ts-section-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
      'stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';

    header.addEventListener('click', e => {
      if (e.target.closest('.ts-section-reset')) return;
      sEl.classList.toggle('open');
    });

    /* Reset section */
    const resetBtn = header.querySelector('.ts-section-reset');
    if (resetBtn) resetBtn.addEventListener('click', async e => {
      e.stopPropagation();
      if (!confirm('Reset "' + (section.label || sectionKey) + '" to defaults?')) return;
      pushUndo();
      try {
        const res = await api('POST', 'reset', { section: sectionKey });
        if (res.ok) {
          values = res.values || values;
          refreshAllFields();
          sendToPreview();
          toast('"' + (section.label || sectionKey) + '" reset to defaults', 'success');
        }
      } catch (err) { toast('Reset failed: ' + err.message, 'error'); }
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
    case 'range':    wrap.appendChild(buildRange(section, key, val, def)); break;
    case 'fontpicker': wrap.appendChild(buildFontPicker(section, key, val)); break;
    case 'gradient':   wrap.appendChild(buildGradient(section, key, val)); break;
    case 'boxshadow':  wrap.appendChild(buildBoxShadow(section, key, val)); break;
    case 'spacing':    wrap.appendChild(buildSpacing(section, key, val)); break;
    case 'hidden':     break; /* hidden fields — no UI */
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

/* Font Picker (searchable Google Fonts dropdown) */
function buildFontPicker(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-font-picker';

  const currentFont = val || 'Inter';

  // Preload selected font
  loadGoogleFont(currentFont);

  // Selected display
  const selected = document.createElement('div');
  selected.className = 'ts-font-picker-selected';
  selected.textContent = currentFont;
  selected.style.fontFamily = `'${currentFont}', sans-serif`;

  // Dropdown container
  const dropdown = document.createElement('div');
  dropdown.className = 'ts-font-picker-dropdown';

  // Search input
  const search = document.createElement('input');
  search.type = 'text';
  search.className = 'ts-font-search';
  search.placeholder = 'Search fonts…';
  dropdown.appendChild(search);

  // Options container
  const optionsWrap = document.createElement('div');
  optionsWrap.className = 'ts-font-options';
  dropdown.appendChild(optionsWrap);

  // Intersection observer for lazy font loading
  const fontObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const fontName = e.target.dataset.font;
        if (fontName) {
          loadGoogleFont(fontName);
          e.target.style.fontFamily = `'${fontName}', sans-serif`;
        }
        fontObserver.unobserve(e.target);
      }
    });
  }, { root: optionsWrap, rootMargin: '40px' });

  function renderOptions(filter) {
    optionsWrap.innerHTML = '';
    const q = (filter || '').toLowerCase();
    const fonts = GOOGLE_FONTS.filter(f => !q || f.toLowerCase().includes(q));
    fonts.forEach(fontName => {
      const opt = document.createElement('div');
      opt.className = 'ts-font-option' + (fontName === currentFont ? ' active' : '');
      opt.textContent = fontName;
      opt.dataset.font = fontName;
      // Lazy load font on scroll visibility
      fontObserver.observe(opt);
      opt.addEventListener('click', () => {
        loadGoogleFont(fontName);
        selected.textContent = fontName;
        selected.style.fontFamily = `'${fontName}', sans-serif`;
        wrap.classList.remove('open');
        search.value = '';
        onFieldChange(section, key, fontName);
        showPairings(fontName);
      });
      optionsWrap.appendChild(opt);
    });
  }

  renderOptions('');

  // Toggle dropdown
  selected.addEventListener('click', (e) => {
    e.stopPropagation();
    const wasOpen = wrap.classList.contains('open');
    // Close all other font pickers
    document.querySelectorAll('.ts-font-picker.open').forEach(fp => fp.classList.remove('open'));
    if (!wasOpen) {
      wrap.classList.add('open');
      search.focus();
      renderOptions('');
    }
  });

  // Search handler
  search.addEventListener('input', () => renderOptions(search.value));
  search.addEventListener('click', (e) => e.stopPropagation());

  // Close on outside click
  document.addEventListener('click', () => wrap.classList.remove('open'));
  dropdown.addEventListener('click', (e) => e.stopPropagation());

  /* ── Font Pairing Suggestions ── */
  const pairingWrap = document.createElement('div');
  pairingWrap.className = 'ts-font-pairings';
  pairingWrap.style.display = 'none';

  function showPairings(fontName) {
    pairingWrap.innerHTML = '';
    const pairs = FONT_PAIRINGS[fontName];
    if (!pairs || key !== 'heading_font') { pairingWrap.style.display = 'none'; return; }
    pairingWrap.style.display = 'flex';
    const lbl = document.createElement('span');
    lbl.className = 'ts-font-pairings-label';
    lbl.textContent = 'Pairs with:';
    pairingWrap.appendChild(lbl);
    pairs.forEach(p => {
      const chip = document.createElement('button');
      chip.className = 'ts-font-pair-chip';
      chip.textContent = p;
      chip.title = 'Apply ' + p + ' as body font';
      chip.addEventListener('click', e => {
        e.stopPropagation();
        loadGoogleFont(p);
        setVal('typography', 'body_font', p);
        /* Update body font picker if visible */
        $$('.ts-field[data-field="body_font"] .ts-font-picker-selected').forEach(sel => {
          sel.textContent = p;
          sel.style.fontFamily = "'" + p + "', sans-serif";
        });
        onFieldChange('typography', 'body_font', p);
        toast('Body font set to ' + p, 'success');
      });
      pairingWrap.appendChild(chip);
    });
  }

  /* Show pairings for initial font */
  showPairings(currentFont);

  wrap.appendChild(selected);
  wrap.appendChild(dropdown);
  wrap.appendChild(pairingWrap);
  return wrap;
}

/* Range slider */
function buildRange(section, key, val, def) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-range-wrap';

  const min = def.min ?? 0;
  const max = def.max ?? 100;
  const step = def.step ?? 1;
  const unit = def.unit || '';
  const current = val !== undefined && val !== '' ? parseFloat(val) : parseFloat(def.default || min);

  const range = document.createElement('input');
  range.type = 'range';
  range.className = 'ts-range';
  range.min = min;
  range.max = max;
  range.step = step;
  range.value = current;

  const valDisplay = document.createElement('span');
  valDisplay.className = 'ts-range-val';
  valDisplay.textContent = current + unit;

  range.addEventListener('input', () => {
    valDisplay.textContent = range.value + unit;
    onFieldChange(section, key, range.value);
  });

  wrap.appendChild(range);
  wrap.appendChild(valDisplay);
  return wrap;
}


/* ═══════════════════════════════════════════════════════════
   GRADIENT BUILDER
   ═══════════════════════════════════════════════════════════ */

function buildGradient(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-gradient-builder';

  const DIRS = [
    {label:'↑',deg:'to top'},{label:'↗',deg:'to top right'},{label:'→',deg:'to right'},{label:'↘',deg:'to bottom right'},
    {label:'↓',deg:'to bottom'},{label:'↙',deg:'to bottom left'},{label:'←',deg:'to left'},{label:'↖',deg:'to top left'}
  ];

  let stops = [{color:'#6366f1',pos:0},{color:'#ec4899',pos:100}];
  let dir = 'to right';

  /* Parse existing value */
  if (val) {
    const m = val.match(/linear-gradient\(([^,]+),\s*(.+)\)/);
    if (m) {
      dir = m[1].trim();
      stops = m[2].split(/,(?![^(]*\))/).map(s => {
        const parts = s.trim().match(/(#[0-9a-fA-F]{3,8}|rgba?\([^)]+\))\s*(\d+)%?/);
        return parts ? {color:parts[1],pos:parseInt(parts[2])} : null;
      }).filter(Boolean);
      if (stops.length < 2) stops = [{color:'#6366f1',pos:0},{color:'#ec4899',pos:100}];
    }
  }

  function getGradientCSS() {
    const s = stops.map(st => st.color + ' ' + st.pos + '%').join(', ');
    return 'linear-gradient(' + dir + ', ' + s + ')';
  }

  /* Preview */
  const preview = document.createElement('div');
  preview.className = 'ts-gradient-preview';
  function updatePreview() {
    const css = getGradientCSS();
    preview.style.background = css;
    onFieldChange(section, key, css);
  }

  /* Direction buttons */
  const dirWrap = document.createElement('div');
  dirWrap.className = 'ts-gradient-dir';
  DIRS.forEach(d => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'ts-gradient-dir-btn' + (d.deg === dir ? ' active' : '');
    btn.textContent = d.label;
    btn.title = d.deg;
    btn.addEventListener('click', () => {
      dir = d.deg;
      dirWrap.querySelectorAll('.ts-gradient-dir-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      updatePreview();
    });
    dirWrap.appendChild(btn);
  });

  /* Stops */
  const stopsWrap = document.createElement('div');
  stopsWrap.className = 'ts-gradient-stops';

  function renderStops() {
    stopsWrap.innerHTML = '';
    stops.forEach((st, i) => {
      const row = document.createElement('div');
      row.className = 'ts-gradient-stop';

      const swatch = document.createElement('input');
      swatch.type = 'color';
      swatch.className = 'ts-gradient-stop-swatch';
      swatch.value = st.color;
      swatch.style.cursor = 'pointer';
      swatch.addEventListener('input', () => {
        st.color = swatch.value;
        updatePreview();
      });

      const posInput = document.createElement('input');
      posInput.className = 'ts-gradient-stop-pos';
      posInput.type = 'number'; posInput.min = 0; posInput.max = 100;
      posInput.value = st.pos;
      posInput.addEventListener('input', () => { st.pos = parseInt(posInput.value) || 0; updatePreview(); });

      const pctLabel = document.createElement('span');
      pctLabel.style.cssText = 'font-size:11px;color:var(--ts-subtext)';
      pctLabel.textContent = '%';

      row.appendChild(swatch);
      row.appendChild(posInput);
      row.appendChild(pctLabel);

      if (stops.length > 2) {
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'ts-gradient-stop-remove';
        del.textContent = '✕';
        del.addEventListener('click', () => { stops.splice(i, 1); renderStops(); updatePreview(); });
        row.appendChild(del);
      }
      stopsWrap.appendChild(row);
    });
  }

  /* Add stop button */
  const addBtn = document.createElement('button');
  addBtn.type = 'button';
  addBtn.className = 'ts-gradient-add-stop';
  addBtn.textContent = '+ Add color stop';
  addBtn.addEventListener('click', () => {
    if (stops.length >= 5) return;
    stops.push({color:'#94e2d5', pos: 50});
    renderStops();
    updatePreview();
  });

  /* Clear button */
  const clearBtn = document.createElement('button');
  clearBtn.type = 'button';
  clearBtn.className = 'ts-gradient-add-stop';
  clearBtn.textContent = '✕ Clear gradient';
  clearBtn.style.marginLeft = '8px';
  clearBtn.addEventListener('click', () => {
    onFieldChange(section, key, '');
    preview.style.background = 'var(--ts-overlay)';
  });

  renderStops();
  updatePreview();

  const btnRow = document.createElement('div');
  btnRow.style.display = 'flex';
  btnRow.appendChild(addBtn);
  btnRow.appendChild(clearBtn);

  wrap.appendChild(preview);
  wrap.appendChild(dirWrap);
  wrap.appendChild(stopsWrap);
  wrap.appendChild(btnRow);
  return wrap;
}


/* ═══════════════════════════════════════════════════════════
   BOX SHADOW EDITOR
   ═══════════════════════════════════════════════════════════ */

function buildBoxShadow(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-shadow-editor';

  let sh = { x:2, y:4, blur:12, spread:0, color:'rgba(0,0,0,0.15)', inset:false };

  /* Parse existing */
  if (val) {
    const m = val.match(/(inset\s+)?(-?\d+)px\s+(-?\d+)px\s+(\d+)px\s+(-?\d+)px\s+(.+)/);
    if (m) {
      sh.inset = !!m[1]; sh.x = parseInt(m[2]); sh.y = parseInt(m[3]);
      sh.blur = parseInt(m[4]); sh.spread = parseInt(m[5]); sh.color = m[6].trim();
    }
  }

  function getShadowCSS() {
    return (sh.inset ? 'inset ' : '') + sh.x + 'px ' + sh.y + 'px ' + sh.blur + 'px ' + sh.spread + 'px ' + sh.color;
  }

  /* Preview box */
  const previewBox = document.createElement('div');
  previewBox.className = 'ts-shadow-preview-box';

  function update() {
    const css = getShadowCSS();
    previewBox.style.boxShadow = css;
    onFieldChange(section, key, css);
  }

  /* Slider rows */
  const sliders = [
    { label:'X', prop:'x', min:-30, max:30, unit:'px' },
    { label:'Y', prop:'y', min:-30, max:30, unit:'px' },
    { label:'Blur', prop:'blur', min:0, max:60, unit:'px' },
    { label:'Spread', prop:'spread', min:-20, max:30, unit:'px' },
  ];

  const rows = document.createElement('div');
  sliders.forEach(s => {
    const row = document.createElement('div');
    row.className = 'ts-shadow-row';
    const lbl = document.createElement('label');
    lbl.textContent = s.label;
    const range = document.createElement('input');
    range.type = 'range'; range.min = s.min; range.max = s.max; range.value = sh[s.prop];
    range.className = 'ts-range';
    const valEl = document.createElement('span');
    valEl.className = 'ts-shadow-val';
    valEl.textContent = sh[s.prop] + s.unit;
    range.addEventListener('input', () => {
      sh[s.prop] = parseInt(range.value);
      valEl.textContent = sh[s.prop] + s.unit;
      update();
    });
    row.appendChild(lbl); row.appendChild(range); row.appendChild(valEl);
    rows.appendChild(row);
  });

  /* Color row */
  const colorRow = document.createElement('div');
  colorRow.className = 'ts-shadow-row';
  const colorLbl = document.createElement('label');
  colorLbl.textContent = 'Color';
  const colorSwatch = document.createElement('div');
  colorSwatch.className = 'ts-color-swatch';
  colorSwatch.style.cssText = 'width:28px;height:28px;border-radius:4px;cursor:pointer;background:' + sh.color;
  const colorHex = document.createElement('input');
  colorHex.type = 'text'; colorHex.className = 'ts-color-hex';
  colorHex.value = sh.color; colorHex.style.flex = '1';
  colorHex.addEventListener('input', () => { sh.color = colorHex.value; colorSwatch.style.background = sh.color; update(); });
  colorSwatch.addEventListener('click', () => openColorPicker(colorSwatch, colorHex, null, null));
  colorRow.appendChild(colorLbl); colorRow.appendChild(colorSwatch); colorRow.appendChild(colorHex);

  /* Inset toggle */
  const insetRow = document.createElement('div');
  insetRow.className = 'ts-shadow-row';
  const insetLbl = document.createElement('label');
  insetLbl.textContent = 'Inset';
  const insetCb = document.createElement('input');
  insetCb.type = 'checkbox'; insetCb.checked = sh.inset;
  insetCb.addEventListener('change', () => { sh.inset = insetCb.checked; update(); });
  insetRow.appendChild(insetLbl); insetRow.appendChild(insetCb);

  /* Clear */
  const clearRow = document.createElement('div');
  clearRow.style.cssText = 'margin-top:8px';
  const clearBtn = document.createElement('button');
  clearBtn.type = 'button';
  clearBtn.className = 'ts-gradient-add-stop';
  clearBtn.textContent = '✕ Clear shadow';
  clearBtn.addEventListener('click', () => {
    onFieldChange(section, key, '');
    previewBox.style.boxShadow = 'none';
  });
  clearRow.appendChild(clearBtn);

  wrap.appendChild(previewBox);
  wrap.appendChild(rows);
  wrap.appendChild(colorRow);
  wrap.appendChild(insetRow);
  wrap.appendChild(clearRow);
  update();
  return wrap;
}


/* ═══════════════════════════════════════════════════════════
   SPACING VISUAL EDITOR (BOX MODEL)
   ═══════════════════════════════════════════════════════════ */

function buildSpacing(section, key, val) {
  const wrap = document.createElement('div');
  wrap.className = 'ts-spacing-editor';

  /* Parse stored JSON or defaults */
  let sp = { mt:0, mr:0, mb:0, ml:0, pt:20, pr:20, pb:20, pl:20 };
  if (val) {
    try { const parsed = JSON.parse(val); Object.assign(sp, parsed); } catch(e) {}
  }

  function getJSON() { return JSON.stringify(sp); }

  function update() {
    onFieldChange(section, key, getJSON());
  }

  /* Box model diagram */
  const model = document.createElement('div');
  model.className = 'ts-box-model';

  const marginBox = document.createElement('div');
  marginBox.className = 'ts-box-layer ts-box-margin';
  const mLabel = document.createElement('span');
  mLabel.className = 'ts-box-label';
  mLabel.textContent = 'margin';

  const paddingBox = document.createElement('div');
  paddingBox.className = 'ts-box-layer ts-box-padding';
  const pLabel = document.createElement('span');
  pLabel.className = 'ts-box-label';
  pLabel.textContent = 'padding';

  const content = document.createElement('div');
  content.className = 'ts-box-content';
  content.textContent = 'content';

  function makeInput(prop, cls) {
    const inp = document.createElement('input');
    inp.className = 'ts-box-val ' + cls;
    inp.type = 'number'; inp.value = sp[prop]; inp.min = 0; inp.max = 200;
    inp.title = prop;
    inp.addEventListener('input', () => { sp[prop] = parseInt(inp.value) || 0; update(); });
    return inp;
  }

  /* Margin inputs */
  marginBox.appendChild(mLabel);
  marginBox.appendChild(makeInput('mt', 'ts-box-val-top'));
  marginBox.appendChild(makeInput('mr', 'ts-box-val-right'));
  marginBox.appendChild(makeInput('mb', 'ts-box-val-bottom'));
  marginBox.appendChild(makeInput('ml', 'ts-box-val-left'));

  /* Padding inputs */
  paddingBox.appendChild(pLabel);
  paddingBox.appendChild(makeInput('pt', 'ts-box-val-top'));
  paddingBox.appendChild(makeInput('pr', 'ts-box-val-right'));
  paddingBox.appendChild(makeInput('pb', 'ts-box-val-bottom'));
  paddingBox.appendChild(makeInput('pl', 'ts-box-val-left'));

  paddingBox.appendChild(content);
  marginBox.appendChild(paddingBox);
  model.appendChild(marginBox);
  wrap.appendChild(model);
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
      case 'range': {
        const rng = $('.ts-range', el);
        const rv  = $('.ts-range-val', el);
        const unit = SCHEMA[s]?.fields?.[f]?.unit || '';
        if (rng && document.activeElement !== rng) rng.value = val ?? rng.min;
        if (rv) rv.textContent = (val ?? rng?.min ?? '') + unit;
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
      case 'fontpicker': {
        const sel = $('.ts-font-picker-selected', el);
        if (sel) {
          const fontName = val || 'Inter';
          sel.textContent = fontName;
          sel.style.fontFamily = `'${fontName}', sans-serif`;
          loadGoogleFont(fontName);
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
   EXPORT / IMPORT
   ═══════════════════════════════════════════════════════════ */

dom.exportBtn.addEventListener('click', () => {
  const blob = new Blob([JSON.stringify({ theme: THEME_SLUG, values, exportedAt: new Date().toISOString() }, null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = THEME_SLUG + '-settings-' + new Date().toISOString().slice(0,10) + '.json';
  a.click();
  URL.revokeObjectURL(a.href);
  toast('Settings exported', 'success');
});

dom.importBtn.addEventListener('click', () => dom.importFile.click());
dom.importFile.addEventListener('change', () => {
  const file = dom.importFile.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = async e => {
    try {
      const data = JSON.parse(e.target.result);
      if (!data.values || typeof data.values !== 'object') throw new Error('Invalid format');
      if (data.theme && data.theme !== THEME_SLUG && !confirm('This was exported from "' + data.theme + '" but current theme is "' + THEME_SLUG + '". Import anyway?')) return;
      pushUndo();
      values = data.values;
      refreshAllFields();
      sendToPreview();
      scheduleSave();
      toast('Settings imported from ' + file.name, 'success');
    } catch (err) {
      toast('Import failed: ' + err.message, 'error');
    }
    dom.importFile.value = '';
  };
  reader.readAsText(file);
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
let dragEl = null;

const secList = $('#sections-list');
const secSaveBtn = $('#sections-save-btn');

/* ── Schema Mapping ──────────────────────────────────────── */
const SEC_MAP = {
  features:'articles', showcase:'pages', recent:'articles',
  categories:'pages', newsletter:'cta', testimonials:'articles', work:'pages'
};
function schemaOf(id) { return SCHEMA[id] ? id : (SEC_MAP[id] && SCHEMA[SEC_MAP[id]]) ? SEC_MAP[id] : null; }
function fieldsOf(id) { const s = schemaOf(id); return s && SCHEMA[s] ? SCHEMA[s].fields : null; }

/* ── Load ────────────────────────────────────────────────── */
async function loadSections() {
  secList.innerHTML = '<div style="text-align:center;padding:32px;color:var(--ts-subtext)"><div style="font-size:24px;margin-bottom:8px">⏳</div><div style="font-size:12px">Loading sections…</div></div>';
  try {
    const r = await api('GET', 'sections');
    if (r.ok && r.sections) { sectionsData = r.sections; sectionsLoaded = true; renderSec(); }
    else secList.innerHTML = '<div style="text-align:center;padding:24px;color:var(--ts-red);font-size:13px">Failed to load</div>';
  } catch(e) {
    secList.innerHTML = '<div style="text-align:center;padding:24px;color:var(--ts-red);font-size:13px">' + esc(e.message) + '</div>';
  }
}

/* ── Build Field HTML (Section Manager) ──────────────────── */
function secBuildField(sid, key, def, val) {
  const fid = 'sf-' + sid + '-' + key;
  const v = val !== undefined && val !== null && val !== '' ? val : (def.default || '');
  let h = '<div class="ts-sf"><div class="ts-sf-label"><span>' + esc(def.label || key) + '</span><span class="ts-sf-type">' + def.type + '</span></div>';

  if (def.type === 'textarea') {
    h += '<textarea id="' + fid + '" data-s="' + sid + '" data-k="' + key + '" rows="3" placeholder="' + esc(def.label || '') + '">' + esc(v) + '</textarea>';
  } else if (def.type === 'image') {
    h += '<div class="ts-sf-img">';
    h += '<div class="ts-sf-img-thumb" id="' + fid + '-thumb">' + (v ? '<img src="' + esc(v) + '">' : '🖼️') + '</div>';
    h += '<div class="ts-sf-img-actions">';
    h += '<button type="button" class="ts-sf-img-btn" data-fid="' + fid + '">📁 Choose image</button>';
    if (v) h += '<button type="button" class="ts-sf-img-clear" data-fid="' + fid + '">✕ Remove</button>';
    h += '</div>';
    h += '<input type="hidden" id="' + fid + '" data-s="' + sid + '" data-k="' + key + '" value="' + esc(v) + '">';
    h += '</div>';
  } else if (def.type === 'color') {
    h += '<div style="display:flex;gap:8px;align-items:center">';
    h += '<input type="color" id="' + fid + '" data-s="' + sid + '" data-k="' + key + '" value="' + esc(v || '#000000') + '" style="width:36px;height:32px;border:1px solid var(--ts-border);border-radius:4px;cursor:pointer;background:var(--ts-mantle);padding:2px">';
    h += '<input type="text" data-mirror="' + fid + '" value="' + esc(v) + '" style="flex:1" placeholder="#hex">';
    h += '</div>';
  } else if (def.type === 'toggle') {
    h += '<label class="ts-sw" style="margin-top:2px"><input type="checkbox" id="' + fid + '" data-s="' + sid + '" data-k="' + key + '"' + (v ? ' checked' : '') + '>';
    h += '<span class="ts-sw-track"></span><span class="ts-sw-dot"></span></label>';
  } else {
    h += '<input type="text" id="' + fid + '" data-s="' + sid + '" data-k="' + key + '" value="' + esc(v) + '" placeholder="' + esc(def.label || '') + '">';
  }
  h += '</div>';
  return h;
}

/* ── Render All Sections ─────────────────────────────────── */
function renderSec() {
  secList.innerHTML = '';
  sectionsData.forEach(sec => {
    const sid = sec.id;
    const schId = schemaOf(sid);
    const fields = fieldsOf(sid);
    const hasFields = fields && Object.keys(fields).length > 0;
    const isReq = !!sec.required;

    /* Card */
    const card = document.createElement('div');
    card.className = 'ts-sec' + (!sec.enabled ? ' off' : '');
    card.dataset.id = sid;

    /* Header */
    const head = document.createElement('div');
    head.className = 'ts-sec-head';
    head.draggable = true;
    head.innerHTML =
      '<span class="ts-sec-drag">⠿</span>' +
      '<span class="ts-sec-icon">' + esc(sec.icon || '📋') + '</span>' +
      '<span class="ts-sec-label">' + esc(sec.label || sid) + '</span>' +
      (isReq ? '<span class="ts-sec-badge">Required</span>' : '') +
      '<svg class="ts-sec-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>' +
      '<label class="ts-sw" onclick="event.stopPropagation()">' +
        '<input type="checkbox"' + (sec.enabled ? ' checked' : '') + (isReq ? ' checked disabled' : '') + '>' +
        '<span class="ts-sw-track"></span><span class="ts-sw-dot"></span>' +
      '</label>';

    /* Toggle visibility */
    const cb = head.querySelector('input[type="checkbox"]');
    if (!isReq) cb.addEventListener('change', () => { sec.enabled = cb.checked; card.classList.toggle('off', !cb.checked); });

    /* Click header → expand/collapse */
    head.addEventListener('click', e => {
      if (e.target.closest('.ts-sw') || e.target.closest('.ts-sec-drag')) return;
      const wasOpen = card.classList.contains('open');
      /* Close others */
      $$('.ts-sec.open', secList).forEach(c => c.classList.remove('open'));
      if (!wasOpen && hasFields) card.classList.add('open');
    });

    /* Drag */
    head.addEventListener('dragstart', e => {
      dragEl = card; card.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', sid);
    });
    head.addEventListener('dragend', () => {
      card.classList.remove('dragging');
      $$('.ts-sec.drag-over', secList).forEach(c => c.classList.remove('drag-over'));
      dragEl = null;
    });
    card.addEventListener('dragover', e => {
      e.preventDefault(); if (dragEl && dragEl !== card) card.classList.add('drag-over');
    });
    card.addEventListener('dragleave', () => card.classList.remove('drag-over'));
    card.addEventListener('drop', e => {
      e.preventDefault(); card.classList.remove('drag-over');
      if (!dragEl || dragEl === card) return;
      const cards = [...secList.querySelectorAll('.ts-sec')];
      const fi = cards.indexOf(dragEl), ti = cards.indexOf(card);
      if (fi < ti) secList.insertBefore(dragEl, card.nextSibling);
      else secList.insertBefore(dragEl, card);
      reorderData();
    });

    /* Body (editor) */
    const body = document.createElement('div');
    body.className = 'ts-sec-body';

    if (hasFields) {
      let fieldsHtml = '<div class="ts-sec-fields">';
      for (const [key, def] of Object.entries(fields)) {
        const curVal = schId && values[schId] ? values[schId][key] : undefined;
        fieldsHtml += secBuildField(sid, key, def, curVal);
      }
      fieldsHtml += '<div class="ts-sec-save-bar">';
      fieldsHtml += '<button type="button" class="ts-sec-save-btn" data-sid="' + sid + '">💾 Save Changes</button>';
      fieldsHtml += '</div></div>';
      body.innerHTML = fieldsHtml;
      wireEditor(body, sid, schId);
    } else {
      body.innerHTML = '<div class="ts-sec-nofields">This section has no editable text fields.<br>It displays dynamic content (articles, pages, etc.)</div>';
    }

    card.appendChild(head);
    card.appendChild(body);
    secList.appendChild(card);
  });
}

/* ── Wire Editor Events ──────────────────────────────────── */
function wireEditor(bodyEl, sid, schId) {
  /* Save btn */
  const btn = bodyEl.querySelector('.ts-sec-save-btn');
  if (btn) btn.addEventListener('click', () => saveSecContent(sid, schId, bodyEl, btn));

  /* Image choose */
  bodyEl.querySelectorAll('.ts-sf-img-btn').forEach(b => {
    b.addEventListener('click', () => {
      const fid = b.dataset.fid;
      if (typeof JTB !== 'undefined' && JTB.openMediaGallery) {
        JTB.openMediaGallery(url => {
          const inp = document.getElementById(fid);
          const thumb = document.getElementById(fid + '-thumb');
          if (inp) inp.value = url;
          if (thumb) thumb.innerHTML = '<img src="' + esc(url) + '">';
          previewSec(sid, schId, bodyEl);
        });
      } else {
        const url = prompt('Image URL:');
        if (url) {
          const inp = document.getElementById(fid);
          const thumb = document.getElementById(fid + '-thumb');
          if (inp) inp.value = url;
          if (thumb) thumb.innerHTML = '<img src="' + esc(url) + '">';
        }
      }
    });
  });

  /* Image clear */
  bodyEl.querySelectorAll('.ts-sf-img-clear').forEach(b => {
    b.addEventListener('click', () => {
      const fid = b.dataset.fid;
      const inp = document.getElementById(fid);
      const thumb = document.getElementById(fid + '-thumb');
      if (inp) inp.value = '';
      if (thumb) thumb.innerHTML = '🖼️';
      b.remove();
      previewSec(sid, schId, bodyEl);
    });
  });

  /* Color mirrors */
  bodyEl.querySelectorAll('[data-mirror]').forEach(m => {
    const c = document.getElementById(m.dataset.mirror);
    if (!c) return;
    m.addEventListener('input', () => { c.value = m.value; previewSec(sid, schId, bodyEl); });
    c.addEventListener('input', () => { m.value = c.value; previewSec(sid, schId, bodyEl); });
  });

  /* Live preview on typing (debounced) */
  let pt = null;
  bodyEl.querySelectorAll('[data-s][data-k]').forEach(el => {
    const evt = el.type === 'checkbox' ? 'change' : 'input';
    el.addEventListener(evt, () => { clearTimeout(pt); pt = setTimeout(() => previewSec(sid, schId, bodyEl), 250); });
  });
}

/* ── Collect Values ──────────────────────────────────────── */
function collectVals(bodyEl) {
  const d = {};
  bodyEl.querySelectorAll('[data-s][data-k]').forEach(el => {
    d[el.dataset.k] = el.type === 'checkbox' ? el.checked : el.value;
  });
  return d;
}

/* ── Save Section Content ────────────────────────────────── */
async function saveSecContent(sid, schId, bodyEl, btn) {
  const fv = collectVals(bodyEl);
  console.log('[SectionManager] Saving', sid, '→ schema:', schId, 'values:', fv);
  if (!Object.keys(fv).length) { console.warn('[SectionManager] No values to save'); return; }
  const saveKey = schId || sid;

  btn.disabled = true; btn.className = 'ts-sec-save-btn saving'; btn.textContent = '⏳ Saving…';
  try {
    const r = await api('POST', 'save', { data: { [saveKey]: fv } });
    console.log('[SectionManager] Save response:', r);
    if (r.ok) {
      if (!values[saveKey]) values[saveKey] = {};
      Object.assign(values[saveKey], fv);
      btn.className = 'ts-sec-save-btn saved'; btn.textContent = '✅ Saved!';
      toast('"' + sid + '" saved', 'success');
      dom.iframe.src = dom.iframe.src; /* Full preview reload */
      setTimeout(() => { btn.className = 'ts-sec-save-btn'; btn.textContent = '💾 Save Changes'; btn.disabled = false; }, 1800);
    } else throw new Error(r.error || 'Save failed');
  } catch(e) {
    console.error('[SectionManager] Save error:', e);
    btn.className = 'ts-sec-save-btn error'; btn.textContent = '❌ ' + e.message;
    toast('Save failed: ' + e.message, 'error');
    setTimeout(() => { btn.className = 'ts-sec-save-btn'; btn.textContent = '💾 Save Changes'; btn.disabled = false; }, 2500);
  }
}

/* ── Live Preview ────────────────────────────────────────── */
function previewSec(sid, schId, bodyEl) {
  const fv = collectVals(bodyEl);
  const key = schId || sid;
  /* Update local values so sendToPreview sends everything */
  if (!values[key]) values[key] = {};
  Object.assign(values[key], fv);
  /* Use same format as Design tab — preview JS expects this */
  sendToPreview();
}

/* ── Reorder Data ────────────────────────────────────────── */
function reorderData() {
  const ids = [...secList.querySelectorAll('.ts-sec')].map(c => c.dataset.id);
  const nd = []; ids.forEach(id => { const s = sectionsData.find(x => x.id === id); if (s) nd.push(s); });
  sectionsData = nd;
}

/* ── Save Layout (order + enabled) ───────────────────────── */
secSaveBtn.addEventListener('click', async () => {
  reorderData();
  const order = sectionsData.map(s => s.id);
  const enabled = {}; sectionsData.forEach(s => { enabled[s.id] = !!s.enabled; });

  secSaveBtn.disabled = true; secSaveBtn.textContent = 'Saving…';
  try {
    const r = await api('POST', 'sections/save', { order, enabled });
    if (r.ok) {
      secSaveBtn.classList.add('saved');
      secSaveBtn.innerHTML = '✅ Saved!';
      toast('Layout saved', 'success');
      dom.iframe.src = dom.iframe.src;
      setTimeout(() => {
        secSaveBtn.classList.remove('saved');
        secSaveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> Save Layout';
        secSaveBtn.disabled = false;
      }, 2000);
    } else throw new Error(r.error || 'Failed');
  } catch(e) {
    toast('Error: ' + e.message, 'error');
    secSaveBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg> Save Layout';
    secSaveBtn.disabled = false;
  }
});


/* ═══════════════════════════════════════════════════════════
   COLOR FROM IMAGE (Extract Palette)
   ═══════════════════════════════════════════════════════════ */

function extractPaletteFromUrl(imageUrl) {
  if (!imageUrl) return;
  toast('Extracting colors…', 'info');
  const img = new Image();
  img.crossOrigin = 'anonymous';
  img.onload = () => {
    const canvas = domExtractCanvas;
    const ctx = canvas.getContext('2d');
    const size = 64; /* Sample at small size for speed */
    canvas.width = size; canvas.height = size;
    ctx.drawImage(img, 0, 0, size, size);
    let data;
    try { data = ctx.getImageData(0, 0, size, size).data; }
    catch(e) { toast('Could not read image pixels (CORS)', 'error'); return; }

    /* Collect pixel colors */
    const pixels = [];
    for (let i = 0; i < data.length; i += 4) {
      const r = data[i], g = data[i+1], b = data[i+2], a = data[i+3];
      if (a < 128) continue;
      pixels.push([r, g, b]);
    }
    if (pixels.length < 10) { toast('Not enough pixel data', 'error'); return; }

    /* Simple k-means with 5 clusters */
    const k = 5;
    let centers = pixels.filter((_, i) => i % Math.max(1, Math.floor(pixels.length / k)) === 0).slice(0, k);
    while (centers.length < k) centers.push([128, 128, 128]);

    for (let iter = 0; iter < 10; iter++) {
      const clusters = centers.map(() => []);
      pixels.forEach(px => {
        let minD = Infinity, minI = 0;
        centers.forEach((c, ci) => {
          const d = (px[0]-c[0])**2 + (px[1]-c[1])**2 + (px[2]-c[2])**2;
          if (d < minD) { minD = d; minI = ci; }
        });
        clusters[minI].push(px);
      });
      centers = clusters.map((cl, i) => {
        if (!cl.length) return centers[i];
        const avg = [0, 0, 0];
        cl.forEach(px => { avg[0] += px[0]; avg[1] += px[1]; avg[2] += px[2]; });
        return avg.map(v => Math.round(v / cl.length));
      });
    }

    /* Sort by saturation (most colorful first) */
    const hexColors = centers.map(c => rgbToHex(c[0], c[1], c[2]));
    const sorted = hexColors.map(hex => {
      const rgb = hexToRgb(hex);
      const hsv = rgbToHsv(rgb[0], rgb[1], rgb[2]);
      return { hex, sat: hsv[1], val: hsv[2] };
    }).sort((a, b) => (b.sat * b.val) - (a.sat * a.val));

    showExtractedPalette(sorted.map(s => s.hex));
  };
  img.onerror = () => toast('Could not load image', 'error');
  img.src = imageUrl;
}

function showExtractedPalette(colors) {
  /* Remove previous extracted row */
  const old = document.querySelector('.ts-extracted-row');
  if (old) old.remove();

  const row = document.createElement('div');
  row.className = 'ts-extracted-row';

  colors.forEach(c => {
    const dot = document.createElement('div');
    dot.className = 'ts-extracted-dot';
    dot.style.background = c;
    dot.title = c;
    row.appendChild(dot);
  });

  const apply = document.createElement('button');
  apply.className = 'ts-extracted-apply';
  apply.textContent = 'Apply';
  apply.addEventListener('click', () => {
    pushUndo();
    setVal('brand', 'primary_color', colors[0] || '#6366f1');
    setVal('brand', 'secondary_color', colors[1] || '#818cf8');
    setVal('brand', 'accent_color', colors[2] || '#f59e0b');
    refreshAllFields();
    sendToPreview();
    scheduleSave();
    toast('Palette applied from image', 'success');
  });
  row.appendChild(apply);

  /* Insert after color presets grid */
  const presetsEl = document.querySelector('.ts-presets');
  if (presetsEl) presetsEl.appendChild(row);
}


/* ═══════════════════════════════════════════════════════════
   BEFORE / AFTER COMPARE (SPLIT VIEW)
   ═══════════════════════════════════════════════════════════ */

let compareMode = false;
let savedValuesSnapshot = null;

domCompare.addEventListener('click', () => {
  if (compareMode) {
    exitCompare();
  } else {
    enterCompare();
  }
});

function enterCompare() {
  compareMode = true;
  domCompare.classList.add('active');
  savedValuesSnapshot = deepClone(values);

  const main = document.getElementById('ts-preview');

  /* Create compare wrapper */
  const wrap = document.createElement('div');
  wrap.className = 'ts-compare-wrap';
  wrap.id = 'ts-compare-wrap';

  /* Before side */
  const before = document.createElement('div');
  before.className = 'ts-compare-before';
  const beforeLabel = document.createElement('div');
  beforeLabel.className = 'ts-compare-label';
  beforeLabel.textContent = 'BEFORE';
  const beforeIframe = document.createElement('iframe');
  beforeIframe.src = '/admin/theme-studio/preview';
  beforeIframe.style.cssText = 'width:100%;height:100%;border:none';
  before.appendChild(beforeLabel);
  before.appendChild(beforeIframe);

  /* Divider */
  const divider = document.createElement('div');
  divider.className = 'ts-compare-divider';

  /* After side */
  const after = document.createElement('div');
  after.className = 'ts-compare-after';
  const afterLabel = document.createElement('div');
  afterLabel.className = 'ts-compare-label';
  afterLabel.textContent = 'AFTER';
  const afterIframe = document.createElement('iframe');
  afterIframe.src = '/admin/theme-studio/preview';
  afterIframe.style.cssText = 'width:100%;height:100%;border:none';
  after.appendChild(afterLabel);
  after.appendChild(afterIframe);

  wrap.appendChild(before);
  wrap.appendChild(divider);
  wrap.appendChild(after);

  /* Hide normal preview, show compare */
  dom.previewContainer.style.display = 'none';
  main.appendChild(wrap);

  /* After iframe loads, send current values */
  afterIframe.addEventListener('load', () => {
    setTimeout(() => {
      try { afterIframe.contentWindow.postMessage({ type:'theme-studio-update', values }, '*'); } catch(e) {}
    }, 300);
  });

  /* Drag divider */
  let dragging = false;
  divider.addEventListener('mousedown', e => { dragging = true; e.preventDefault(); });
  document.addEventListener('mousemove', e => {
    if (!dragging) return;
    const rect = wrap.getBoundingClientRect();
    let pct = ((e.clientX - rect.left) / rect.width) * 100;
    pct = Math.max(15, Math.min(85, pct));
    before.style.flex = '0 0 ' + pct + '%';
    after.style.flex = '0 0 ' + (100 - pct) + '%';
    divider.style.left = pct + '%';
  });
  document.addEventListener('mouseup', () => { dragging = false; });
}

function exitCompare() {
  compareMode = false;
  domCompare.classList.remove('active');
  const wrap = document.getElementById('ts-compare-wrap');
  if (wrap) wrap.remove();
  dom.previewContainer.style.display = '';
  savedValuesSnapshot = null;
}


/* ═══════════════════════════════════════════════════════════
   DARK / LIGHT MODE TOGGLE
   ═══════════════════════════════════════════════════════════ */

/* ═══════════════════════════════════════════════════════════
   INJECT "EXTRACT FROM IMAGE" INTO PRESETS
   ═══════════════════════════════════════════════════════════ */

/* Override renderPanel to inject extract button after presets grid */
const _origRenderPanel = renderPanel;
/* Monkey-patch: after renderPanel runs, inject the extract button */
setTimeout(() => {
  const presetsEl = document.querySelector('.ts-presets');
  if (presetsEl) {
    const extractBtn = document.createElement('button');
    extractBtn.type = 'button';
    extractBtn.className = 'ts-extract-btn';
    extractBtn.innerHTML = '🎨 Extract palette from image…';
    extractBtn.addEventListener('click', () => {
      if (typeof JTB !== 'undefined' && JTB.openMediaGallery) {
        JTB.openMediaGallery((selectedUrl) => {
          if (selectedUrl) extractPaletteFromUrl(selectedUrl);
        });
      } else {
        toast('Media gallery not available', 'error');
      }
    });
    presetsEl.appendChild(extractBtn);
  }
}, 0);


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
