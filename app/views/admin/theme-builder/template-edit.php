<?php
/**
 * Theme Builder 3.0 - Template Editor (Clean Build)
 * For global templates: Header, Footer, Sidebar, Single, Archive, 404, etc.
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 5)); }

// Theme Builder Shared Components
if (!defined("CMS_CORE")) { define("CMS_CORE", CMS_ROOT . "/core"); }
require_once CMS_CORE . "/theme-builder/components/media-gallery.php";

// Template variables from controller
$templateName = esc(is_array($tplRecord) ? ($tplRecord['name'] ?? 'New Template') : 'New Template');
$templateId = (int)(is_array($tplRecord) ? ($tplRecord['id'] ?? 0) : 0);
$templateType = esc($templateType ?? 'header');
$typeLabel = esc(is_array($typeInfo) ? ($typeInfo['label'] ?? ucfirst($templateType)) : ucfirst($templateType));
$typeIcon = is_array($typeInfo) ? ($typeInfo['icon'] ?? '📄') : '📄';
$priority = (int)(is_array($tplRecord) ? ($tplRecord['priority'] ?? 0) : 0);
$isActive = (int)(is_array($tplRecord) ? ($tplRecord['is_active'] ?? 1) : 1);
$savedConditions = is_array($tplRecord) && !empty($tplRecord['conditions']) 
    ? $tplRecord['conditions'] 
    : '{"type":"all"}';

// JSON data from controller
$contentJson = $contentJson ?? '{"sections":[]}';
$modulesJson = $modulesJson ?? '{}';
$categoriesJson = $categoriesJson ?? '{}';
$themeColorsJson = $themeColorsJson ?? '{}';
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $typeLabel ?> Template - <?= $templateName ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/core/theme-builder/css/components.css">
    <style>
        :root {
            --tb-bg: #1e1e2e;
            --tb-bg-secondary: #181825;
            --tb-bg-tertiary: #313244;
            --tb-surface: #45475a;
            --tb-text: #cdd6f4;
            --tb-text-dim: #a6adc8;
            --tb-accent: #89b4fa;
            --tb-success: #a6e3a1;
            --tb-warning: #f9e2af;
            --tb-danger: #f38ba8;
            --tb-border: #45475a;
            --tb-surface-2: #313244;
            --tb-text-muted: #6c7086;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--tb-bg); color: var(--tb-text); overflow: hidden; }
        
        /* Layout */
        .tb-container { display: flex; flex-direction: column; height: 100vh; }
        .tb-toolbar { display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; background: var(--tb-bg-secondary); border-bottom: 1px solid var(--tb-border); }
        .tb-toolbar-left, .tb-toolbar-right { display: flex; align-items: center; gap: 12px; }
        .tb-main { display: flex; flex: 1; overflow: hidden; }
        
        /* Panels */
        .tb-panel { background: var(--tb-bg-secondary); border-right: 1px solid var(--tb-border); }
        .tb-panel-left { width: 280px; display: flex; flex-direction: column; }
        .tb-panel-header { padding: 16px; border-bottom: 1px solid var(--tb-border); font-weight: 600; }
        .tb-panel-content { padding: 16px; overflow-y: auto; flex: 1; }
        
        /* Canvas */
        .tb-canvas { flex: 1; background: var(--tb-bg); overflow: auto; padding: 20px; }
        .tb-canvas-inner { min-height: 400px; background: var(--tb-bg-tertiary); border: 2px dashed var(--tb-border); border-radius: 8px; padding: 20px; }
        
        /* Modules list */
        .tb-category { margin-bottom: 20px; }
        .tb-category-title { font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--tb-text-dim); margin-bottom: 10px; letter-spacing: 0.5px; }
        .tb-modules-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .tb-module-item { display: flex; flex-direction: column; align-items: center; padding: 12px 8px; background: var(--tb-bg-tertiary); border-radius: 6px; cursor: grab; transition: all 0.2s; }
        .tb-module-item:hover { background: var(--tb-surface); transform: translateY(-2px); }
        .tb-module-item.dragging { opacity: 0.5; }
        .tb-module-icon { font-size: 24px; margin-bottom: 6px; }
        .tb-module-name { font-size: 11px; color: var(--tb-text-dim); text-align: center; }
        
        /* Buttons */
        .tb-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--tb-bg-tertiary); color: var(--tb-text); border: 1px solid var(--tb-border); border-radius: 6px; cursor: pointer; font-size: 13px; transition: all 0.2s; }
        .tb-btn:hover { background: var(--tb-surface); }
        .tb-btn-primary { background: var(--tb-accent); color: #1e1e2e; border-color: var(--tb-accent); }
        .tb-btn-primary:hover { filter: brightness(1.1); }
        .tb-btn-icon { padding: 8px; }
        
        /* Form elements */
        .tb-input { width: 100%; padding: 10px 12px; background: var(--tb-bg-tertiary); border: 1px solid var(--tb-border); border-radius: 6px; color: var(--tb-text); font-size: 14px; }
        .tb-input:focus { outline: none; border-color: var(--tb-accent); }
        .tb-label { display: block; font-size: 12px; color: var(--tb-text-dim); margin-bottom: 6px; }
        .tb-field { margin-bottom: 16px; }
        
        /* Section/Row/Column */
        .tb-section { background: var(--tb-bg-secondary); border: 1px solid var(--tb-border); border-radius: 8px; margin-bottom: 16px; }
        .tb-section-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: var(--tb-bg-tertiary); border-radius: 8px 8px 0 0; }
        .tb-section-content { padding: 16px; }
        .tb-row { display: flex; gap: 16px; min-height: 80px; }
        .tb-column { flex: 1; background: var(--tb-bg-tertiary); border: 2px dashed var(--tb-border); border-radius: 6px; padding: 12px; min-height: 60px; transition: all 0.2s; }
        .tb-column.drag-over { border-color: var(--tb-accent); background: rgba(137, 180, 250, 0.1); }
        
        /* Module in canvas */
        .tb-module { background: var(--tb-surface); border-radius: 6px; padding: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s; }
        .tb-module:hover { box-shadow: 0 0 0 2px var(--tb-accent); }
        .tb-module.selected { box-shadow: 0 0 0 2px var(--tb-accent); }
        .tb-module-type { font-size: 11px; color: var(--tb-accent); font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .tb-module-preview { font-size: 13px; color: var(--tb-text-dim); }
        
        /* Empty state */
        .tb-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px; color: var(--tb-text-dim); }
        .tb-empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        
        /* Search */
        .tb-search { padding: 12px 16px; border-bottom: 1px solid var(--tb-border); }
        .tb-search input { width: 100%; padding: 8px 12px; background: var(--tb-bg-tertiary); border: 1px solid var(--tb-border); border-radius: 6px; color: var(--tb-text); }
        
        /* Toggle */
        .tb-toggle { position: relative; width: 44px; height: 24px; }
        .tb-toggle input { opacity: 0; width: 0; height: 0; }
        .tb-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: var(--tb-bg-tertiary); border-radius: 24px; transition: 0.3s; }
        .tb-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: var(--tb-text); border-radius: 50%; transition: 0.3s; }
        .tb-toggle input:checked + .tb-toggle-slider { background: var(--tb-accent); }
        .tb-toggle input:checked + .tb-toggle-slider:before { transform: translateX(20px); }

        /* Toggle Switch (for animation) */
        .tb-toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .tb-toggle-switch input { opacity: 0; width: 0; height: 0; }
        .tb-toggle-switch .tb-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--tb-bg-tertiary); transition: .4s; border-radius: 24px; }
        .tb-toggle-switch .tb-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: var(--tb-text); transition: .4s; border-radius: 50%; }
        .tb-toggle-switch input:checked + .tb-toggle-slider { background-color: var(--tb-accent); }
        .tb-toggle-switch input:checked + .tb-toggle-slider:before { transform: translateX(20px); background-color: var(--tb-bg); }

        /* Small buttons */
        .tb-btn-sm { padding: 4px 10px; font-size: 11px; }

        /* Typography section */
        .tb-typography-section { margin-bottom: 16px; }
        .tb-typography-content { padding-left: 4px; }
        
        /* Tabs */
        .tb-tabs { display: flex; border-bottom: 1px solid var(--tb-border); }
        .tb-tab { padding: 12px 20px; cursor: pointer; border-bottom: 2px solid transparent; color: var(--tb-text-dim); transition: all 0.2s; }
        .tb-tab:hover { color: var(--tb-text); }
        .tb-tab.active { color: var(--tb-accent); border-bottom-color: var(--tb-accent); }
        
        /* Toast */
        .tb-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(100px); padding: 12px 24px; background: var(--tb-surface); border-radius: 8px; opacity: 0; transition: all 0.3s; z-index: 1000; }
        .tb-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .tb-toast.success { background: var(--tb-success); color: #1e1e2e; }
        .tb-toast.error { background: var(--tb-danger); color: #1e1e2e; }
        
        /* Module actions */
        .tb-module-actions { position: absolute; top: 4px; right: 4px; display: none; gap: 4px; }
        .tb-module:hover .tb-module-actions { display: flex; }
        .tb-module-action { width: 24px; height: 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center; background: var(--tb-bg-tertiary); color: var(--tb-text); }
        .tb-module-action.edit { background: var(--tb-success, #10b981); color: #fff; }
        .tb-module-action.delete { background: var(--tb-danger); color: #fff; }
        .tb-module-action.duplicate { background: var(--tb-accent); color: #1e1e2e; }
        .tb-module { position: relative; }
        
        /* Row header with layout picker */
        .tb-row-wrapper { margin-bottom: 16px; }
        .tb-row-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; padding: 6px 10px; background: var(--tb-bg); border-radius: 4px; }
        .tb-row-label { font-size: 11px; color: var(--tb-text-dim); }
        .tb-row-actions { display: flex; gap: 8px; align-items: center; }
        .tb-row-layout { display: flex; gap: 4px; }
        .tb-row-layout-btn { width: 36px; height: 24px; border: 1px solid var(--tb-border); background: var(--tb-bg-tertiary); border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 2px; padding: 4px; }
        .tb-row-layout-btn:hover, .tb-row-layout-btn.active { border-color: var(--tb-accent); background: rgba(137,180,250,0.2); }
        .tb-row-layout-btn .col { background: var(--tb-accent); height: 100%; border-radius: 2px; }
        
        /* Layout picker modal options */
        .tb-layout-option { padding: 16px; background: var(--tb-bg-tertiary); border: 2px solid var(--tb-border); border-radius: 8px; cursor: pointer; transition: all 0.2s; text-align: center; }
        .tb-layout-option:hover { border-color: var(--tb-accent); background: rgba(137,180,250,0.1); transform: translateY(-2px); }
        .tb-layout-preview { display: flex; gap: 4px; height: 40px; margin-bottom: 10px; }
        .tb-layout-preview .col { background: var(--tb-accent); border-radius: 4px; opacity: 0.7; height: 100%; min-width: 20px; }
        .tb-layout-label { font-size: 12px; color: var(--tb-text); font-weight: 500; }
        
        /* Modal */
        .tb-modal { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .tb-modal.show { display: flex; }
        .tb-modal-content { background: var(--tb-bg-secondary); border-radius: 12px; width: 90%; max-width: 800px; max-height: 80vh; overflow: hidden; display: flex; flex-direction: column; }
        .tb-modal-header { padding: 16px 20px; border-bottom: 1px solid var(--tb-border); display: flex; justify-content: space-between; align-items: center; }
        .tb-modal-header h3 { font-size: 16px; font-weight: 600; }
        .tb-modal-close { background: none; border: none; color: var(--tb-text); font-size: 24px; cursor: pointer; }
        .tb-modal-body { padding: 20px; overflow-y: auto; flex: 1; }
        .tb-modal-footer { padding: 16px 20px; border-top: 1px solid var(--tb-border); display: flex; justify-content: flex-end; gap: 12px; }
        
        /* Media Gallery */
        .tb-media-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .tb-media-item { aspect-ratio: 1; background: var(--tb-bg-tertiary); border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
        .tb-media-item:hover { border-color: var(--tb-accent); }
        .tb-media-item.selected { border-color: var(--tb-accent); box-shadow: 0 0 0 2px var(--tb-accent); }
        .tb-media-item img { width: 100%; height: 100%; object-fit: cover; }
        .tb-media-upload { display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed var(--tb-border); color: var(--tb-text-dim); gap: 8px; }
        .tb-media-upload:hover { border-color: var(--tb-accent); color: var(--tb-accent); }
        
        /* Image field with picker button */
        .tb-image-field { display: flex; gap: 8px; }
        .tb-image-field input { flex: 1; }
        .tb-image-picker-btn { padding: 0 12px; background: var(--tb-accent); color: #1e1e2e; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; }

        /* ═══════════════════════════════════════════════════════════
           ICON PICKER MODAL
           ═══════════════════════════════════════════════════════════ */
        .tb-icon-picker-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }
        .tb-icon-picker-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .tb-icon-picker-modal {
            background: var(--tb-bg-secondary);
            border: 1px solid var(--tb-border);
            border-radius: 12px;
            width: 600px;
            max-width: 95vw;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            transform: scale(0.95);
            transition: transform 0.2s ease;
            overflow: hidden;
        }
        .tb-icon-picker-overlay.active .tb-icon-picker-modal {
            transform: scale(1);
        }
        .tb-icon-picker-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--tb-border);
        }
        .tb-icon-picker-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--tb-text);
        }
        .tb-icon-picker-close {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: var(--tb-text-dim);
            font-size: 20px;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tb-icon-picker-close:hover {
            background: var(--tb-surface);
            color: var(--tb-text);
        }
        .tb-icon-picker-tabs {
            display: flex;
            gap: 8px;
            padding: 12px 20px;
            border-bottom: 1px solid var(--tb-border);
            background: var(--tb-bg);
        }
        .tb-icon-tab {
            padding: 8px 16px;
            background: transparent;
            border: none;
            border-radius: 6px;
            color: var(--tb-text-dim);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }
        .tb-icon-tab:hover {
            background: var(--tb-surface);
            color: var(--tb-text);
        }
        .tb-icon-tab.active {
            background: var(--tb-accent);
            color: #1e1e2e;
        }
        .tb-icon-picker-search {
            padding: 12px 20px;
            border-bottom: 1px solid var(--tb-border);
        }
        .tb-icon-picker-search input {
            width: 100%;
            padding: 10px 14px;
            background: var(--tb-bg);
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            color: var(--tb-text);
            font-size: 14px;
        }
        .tb-icon-picker-search input:focus {
            outline: none;
            border-color: var(--tb-accent);
        }
        .tb-icon-picker-grid {
            flex: 1;
            overflow-y: auto;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
            gap: 8px;
            align-content: start;
            max-height: 400px;
        }
        .tb-icon-option {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tb-bg);
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            color: var(--tb-text);
            transition: all 0.15s;
        }
        .tb-icon-option:hover {
            background: var(--tb-accent);
            border-color: var(--tb-accent);
            color: #1e1e2e;
            transform: scale(1.1);
        }
        .tb-icon-option i {
            font-size: 20px;
        }
        .tb-icon-option svg {
            width: 24px;
            height: 24px;
        }
        .tb-icon-option.emoji {
            font-size: 24px;
        }
        .tb-icon-picker-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: var(--tb-text-dim);
        }
        /* Icon field with preview and browse button */
        .tb-icon-field {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .tb-icon-field input {
            flex: 1;
        }
        .tb-icon-preview {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--tb-bg);
            border: 1px solid var(--tb-border);
            border-radius: 6px;
            font-size: 20px;
        }
        .tb-icon-browse-btn {
            padding: 0 12px;
            height: 40px;
            background: var(--tb-accent);
            color: #1e1e2e;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            white-space: nowrap;
        }

        /* ════════════════════════════════════════════════════════════
           MODULE SUB-TABS (Content / Design / Advanced)
           ════════════════════════════════════════════════════════════ */
        .tb-module-tabs {
            display: flex;
            border-bottom: 1px solid var(--tb-border);
            background: var(--tb-bg-secondary);
            padding: 0 8px;
        }
        .tb-module-tab {
            padding: 10px 16px;
            cursor: pointer;
            border: none;
            background: transparent;
            border-bottom: 2px solid transparent;
            color: var(--tb-text-dim);
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .tb-module-tab:hover { color: var(--tb-text); }
        .tb-module-tab.active {
            color: var(--tb-accent);
            border-bottom-color: var(--tb-accent);
        }

        /* ════════════════════════════════════════════════════════════
           DEVICE TOGGLE (Desktop / Tablet / Mobile)
           ════════════════════════════════════════════════════════════ */
        .tb-device-toggle {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: var(--tb-bg);
            border-bottom: 1px solid var(--tb-border);
        }
        .tb-device-toggle-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--tb-text-dim);
            margin-right: 8px;
        }
        .tb-device-btn {
            padding: 5px 8px;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 4px;
            color: var(--tb-text-dim);
            cursor: pointer;
            font-size: 14px;
            transition: all 0.15s;
        }
        .tb-device-btn:hover {
            background: var(--tb-bg-tertiary);
            color: var(--tb-text);
        }
        .tb-device-btn.active {
            background: var(--tb-accent);
            color: var(--tb-bg);
            border-color: var(--tb-accent);
        }

        /* ════════════════════════════════════════════════════════════
           SPACING BOX (Divi-style margin/padding visual editor)
           ════════════════════════════════════════════════════════════ */
        .tb-spacing-box {
            position: relative;
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        .tb-spacing-box-outer {
            position: relative;
            background: rgba(249, 226, 175, 0.15);
            border: 2px dashed rgba(249, 226, 175, 0.5);
            border-radius: 8px;
            padding: 24px;
        }
        .tb-spacing-box-inner {
            position: relative;
            background: rgba(166, 227, 161, 0.15);
            border: 2px dashed rgba(166, 227, 161, 0.5);
            border-radius: 6px;
            padding: 20px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tb-spacing-box-content {
            background: var(--tb-bg-tertiary);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 11px;
            color: var(--tb-text-dim);
        }
        .tb-spacing-label {
            position: absolute;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .tb-spacing-label-margin {
            top: 4px;
            left: 8px;
            background: rgba(249, 226, 175, 0.3);
            color: #c9a227;
        }
        .tb-spacing-label-padding {
            top: 4px;
            left: 8px;
            background: rgba(166, 227, 161, 0.3);
            color: #40a02b;
        }
        .tb-spacing-input-wrap { position: absolute; }
        .tb-spacing-input-wrap input {
            width: 44px;
            padding: 4px;
            background: var(--tb-bg-secondary);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
            text-align: center;
        }
        .tb-spacing-input-wrap input:focus { outline: none; border-color: var(--tb-accent); }
        .tb-spacing-margin-top { top: -12px; left: 50%; transform: translateX(-50%); }
        .tb-spacing-margin-right { right: -12px; top: 50%; transform: translateY(-50%); }
        .tb-spacing-margin-bottom { bottom: -12px; left: 50%; transform: translateX(-50%); }
        .tb-spacing-margin-left { left: -12px; top: 50%; transform: translateY(-50%); }
        .tb-spacing-padding-top { top: 4px; left: 50%; transform: translateX(-50%); }
        .tb-spacing-padding-right { right: 4px; top: 50%; transform: translateY(-50%); }
        .tb-spacing-padding-bottom { bottom: 4px; left: 50%; transform: translateX(-50%); }
        .tb-spacing-padding-left { left: 4px; top: 50%; transform: translateY(-50%); }
        .tb-spacing-link-btn {
            position: absolute;
            width: 20px; height: 20px;
            border: none; border-radius: 50%;
            background: var(--tb-bg-tertiary);
            cursor: pointer; font-size: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .tb-spacing-link-btn.linked { background: var(--tb-accent); color: var(--tb-bg); }
        .tb-spacing-link-margin { top: 50%; right: 4px; transform: translateY(-50%); }
        .tb-spacing-link-padding { top: 50%; right: 4px; transform: translateY(-50%); }

        /* ════════════════════════════════════════════════════════════
           DESIGN SETTINGS SECTIONS (collapsible)
           ════════════════════════════════════════════════════════════ */
        .tb-design-section { border-bottom: 1px solid var(--tb-border); margin-bottom: 12px; }
        .tb-design-section-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0; cursor: pointer;
            font-size: 12px; font-weight: 600; color: var(--tb-text);
        }
        .tb-design-section-header:hover { color: var(--tb-accent); }
        .tb-design-section-toggle { font-size: 10px; transition: transform 0.2s; }
        .tb-design-section.collapsed .tb-design-section-toggle { transform: rotate(-90deg); }
        .tb-design-section.collapsed .tb-design-section-body { display: none; }
        .tb-design-section-body { padding-bottom: 12px; }

        /* Setting groups */
        .tb-setting-group { margin-bottom: 16px; }
        .tb-setting-label { font-size: 12px; font-weight: 500; color: var(--tb-text-dim); margin-bottom: 6px; display: block; }
        .tb-setting-input {
            width: 100%; padding: 8px 10px;
            background: var(--tb-bg-tertiary); border: 1px solid var(--tb-border);
            border-radius: 6px; color: var(--tb-text); font-size: 13px;
        }
        .tb-setting-input:focus { outline: none; border-color: var(--tb-accent); }

        /* ═══════════════════════════════════════════════════════════
           BORDER UI (Divi-style visual border controls)
           ═══════════════════════════════════════════════════════════ */
        .tb-border-section {
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .tb-border-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: var(--tb-surface-2);
            cursor: pointer;
            user-select: none;
        }
        .tb-border-section-header:hover { background: var(--tb-border); }
        .tb-border-section-title {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tb-border-section-toggle {
            font-size: 10px;
            color: var(--tb-text-muted);
            transition: transform 0.2s;
        }
        .tb-border-section.collapsed .tb-border-section-toggle { transform: rotate(-90deg); }
        .tb-border-section-body { padding: 12px; display: block; }
        .tb-border-section.collapsed .tb-border-section-body { display: none; }

        .tb-border-box {
            position: relative;
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        .tb-border-box-outer {
            position: relative;
            background: rgba(180, 190, 254, 0.1);
            border: 2px dashed rgba(180, 190, 254, 0.4);
            border-radius: 8px;
            padding: 28px;
        }
        .tb-border-box-inner {
            position: relative;
            background: var(--tb-surface);
            border: 2px solid rgba(180, 190, 254, 0.6);
            border-radius: 6px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tb-border-box-content {
            font-size: 11px;
            color: var(--tb-text-muted);
            text-align: center;
            padding: 8px;
        }
        .tb-border-label {
            position: absolute;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 2px 6px;
            border-radius: 3px;
            top: 4px;
            left: 8px;
            background: rgba(180, 190, 254, 0.3);
            color: #7c7fea;
        }
        .tb-border-input-wrap {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 2px;
        }
        .tb-border-input-wrap input {
            width: 44px;
            padding: 4px 6px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
            text-align: center;
        }
        .tb-border-input-wrap input:focus {
            outline: none;
            border-color: var(--tb-accent);
        }
        .tb-border-width-top { top: -12px; left: 50%; transform: translateX(-50%); }
        .tb-border-width-right { right: -12px; top: 50%; transform: translateY(-50%); }
        .tb-border-width-bottom { bottom: -12px; left: 50%; transform: translateX(-50%); }
        .tb-border-width-left { left: -12px; top: 50%; transform: translateY(-50%); }

        .tb-border-link-btn {
            position: absolute;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--tb-surface-2);
            border: 1px solid var(--tb-border);
            color: var(--tb-text-muted);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .tb-border-link-btn:hover {
            background: var(--tb-accent);
            color: #1e1e2e;
            border-color: var(--tb-accent);
        }
        .tb-border-link-btn.linked {
            background: var(--tb-accent);
            color: #1e1e2e;
            border-color: var(--tb-accent);
        }

        .tb-radius-box {
            position: relative;
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        .tb-radius-box-visual {
            position: relative;
            width: 100%;
            height: 100px;
            background: var(--tb-surface);
            border: 2px solid rgba(245, 194, 231, 0.6);
            border-radius: 8px;
            transition: border-radius 0.2s;
        }
        .tb-radius-corner {
            position: absolute;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tb-radius-corner input {
            width: 36px;
            padding: 4px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 10px;
            text-align: center;
        }
        .tb-radius-corner input:focus {
            outline: none;
            border-color: var(--tb-accent);
        }
        .tb-radius-tl { top: -18px; left: -18px; }
        .tb-radius-tr { top: -18px; right: -18px; }
        .tb-radius-br { bottom: -18px; right: -18px; }
        .tb-radius-bl { bottom: -18px; left: -18px; }

        .tb-radius-link-btn {
            position: absolute;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--tb-surface-2);
            border: 1px solid var(--tb-border);
            color: var(--tb-text-muted);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .tb-radius-link-btn:hover,
        .tb-radius-link-btn.linked {
            background: var(--tb-accent);
            color: #1e1e2e;
            border-color: var(--tb-accent);
        }
        .tb-radius-label {
            position: absolute;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 2px 6px;
            border-radius: 3px;
            top: 4px;
            left: 8px;
            background: rgba(245, 194, 231, 0.3);
            color: #d5679d;
        }

        .tb-border-style-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
        }
        .tb-border-style-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 50px;
        }
        .tb-border-style-row select {
            flex: 1;
            padding: 6px 8px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        .tb-border-color-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 12px;
        }
        .tb-border-color-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 50px;
        }
        .tb-border-color-row input[type="color"] {
            width: 36px;
            height: 28px;
            padding: 2px;
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            cursor: pointer;
        }
        .tb-border-color-row input[type="text"] {
            flex: 1;
            padding: 6px 8px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }

        /* ═══════════════════════════════════════════════════════════
           BOX SHADOW UI (Divi-style controls)
           ═══════════════════════════════════════════════════════════ */
        .tb-shadow-section {
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .tb-shadow-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: var(--tb-surface-2);
            cursor: pointer;
            user-select: none;
        }
        .tb-shadow-section-header:hover { background: var(--tb-border); }
        .tb-shadow-section-title {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tb-shadow-section-toggle {
            font-size: 10px;
            color: var(--tb-text-muted);
            transition: transform 0.2s;
        }
        .tb-shadow-section.collapsed .tb-shadow-section-toggle { transform: rotate(-90deg); }
        .tb-shadow-section-body { padding: 12px; display: block; }
        .tb-shadow-section.collapsed .tb-shadow-section-body { display: none; }

        .tb-shadow-preview-box {
            width: 60px;
            height: 60px;
            background: #ffffff;
            border-radius: 8px;
            margin: 0 auto 16px;
            transition: box-shadow 0.2s ease;
        }
        .tb-shadow-controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .tb-shadow-control-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tb-shadow-control-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 80px;
            flex-shrink: 0;
        }
        .tb-shadow-control-row input[type="range"] {
            flex: 1;
            height: 4px;
            -webkit-appearance: none;
            appearance: none;
            background: var(--tb-border);
            border-radius: 2px;
            outline: none;
        }
        .tb-shadow-control-row input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            background: var(--tb-accent);
            border-radius: 50%;
            cursor: pointer;
        }
        .tb-shadow-control-row input[type="number"] {
            width: 54px;
            padding: 4px 6px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
            text-align: center;
        }
        .tb-shadow-control-row input[type="color"] {
            width: 36px;
            height: 28px;
            padding: 2px;
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            cursor: pointer;
        }
        .tb-shadow-control-row input[type="text"] {
            flex: 1;
            padding: 6px 8px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        .tb-shadow-enable-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid var(--tb-border);
            margin-bottom: 12px;
        }
        .tb-shadow-enable-row label {
            font-size: 12px;
            color: var(--tb-text);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tb-shadow-enable-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .tb-shadow-inset-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--tb-border);
            margin-top: 12px;
        }
        .tb-shadow-inset-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tb-shadow-preset-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--tb-border);
            margin-bottom: 12px;
        }
        .tb-shadow-preset-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 50px;
        }
        .tb-shadow-preset-row select {
            flex: 1;
            padding: 6px 8px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        .tb-shadow-disabled {
            opacity: 0.4;
            pointer-events: none;
        }

        /* ═══════════════════════════════════════════════════════════
           TRANSFORM SECTION STYLES (Divi-style controls)
           ═══════════════════════════════════════════════════════════ */
        .tb-transform-section {
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .tb-transform-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: linear-gradient(135deg, rgba(137, 180, 250, 0.15), rgba(137, 180, 250, 0.08));
            cursor: pointer;
            user-select: none;
            border-bottom: 1px solid var(--tb-border);
        }
        .tb-transform-section-header:hover {
            background: linear-gradient(135deg, rgba(137, 180, 250, 0.25), rgba(137, 180, 250, 0.12));
        }
        .tb-transform-section-title {
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #89b4fa;
        }
        .tb-transform-section-toggle {
            font-size: 10px;
            color: var(--tb-text-muted);
            transition: transform 0.2s;
        }
        .tb-transform-section.collapsed .tb-transform-section-toggle { transform: rotate(-90deg); }
        .tb-transform-section-body { padding: 12px; display: block; }
        .tb-transform-section.collapsed .tb-transform-section-body { display: none; }

        .tb-transform-subsection {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(137, 180, 250, 0.15);
        }
        .tb-transform-subsection:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .tb-transform-subsection-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--tb-text-muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tb-transform-control-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .tb-transform-control-row:last-child { margin-bottom: 0; }
        .tb-transform-control-row label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 80px;
            flex-shrink: 0;
        }
        .tb-transform-control-row input[type="range"] {
            flex: 1;
            height: 4px;
            -webkit-appearance: none;
            appearance: none;
            background: var(--tb-border);
            border-radius: 2px;
            outline: none;
        }
        .tb-transform-control-row input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            background: #89b4fa;
            border-radius: 50%;
            cursor: pointer;
        }
        .tb-transform-control-row input[type="number"] {
            width: 60px;
            padding: 4px 6px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
            text-align: center;
        }
        .tb-transform-control-row .tb-unit-label {
            font-size: 11px;
            color: var(--tb-text-muted);
            min-width: 20px;
        }

        .tb-scale-link-toggle {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
            padding: 6px 8px;
            background: rgba(137, 180, 250, 0.1);
            border-radius: 4px;
            cursor: pointer;
            user-select: none;
            font-size: 11px;
            color: var(--tb-text-muted);
        }
        .tb-scale-link-toggle:hover { background: rgba(137, 180, 250, 0.15); }
        .tb-scale-link-toggle input[type="checkbox"] {
            width: 14px;
            height: 14px;
            accent-color: #89b4fa;
        }
        .tb-scale-link-toggle.linked {
            background: rgba(137, 180, 250, 0.2);
            color: #89b4fa;
        }

        .tb-transform-origin-wrapper {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .tb-transform-origin-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            width: 90px;
            height: 90px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 6px;
            padding: 6px;
        }
        .tb-transform-origin-point {
            width: 100%;
            aspect-ratio: 1;
            background: var(--tb-border);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tb-transform-origin-point:hover { background: rgba(137, 180, 250, 0.4); }
        .tb-transform-origin-point.active {
            background: #89b4fa;
            box-shadow: 0 0 8px rgba(137, 180, 250, 0.5);
        }
        .tb-transform-origin-point::after {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--tb-text-muted);
            border-radius: 50%;
            opacity: 0.5;
        }
        .tb-transform-origin-point.active::after {
            background: #1e1e2e;
            opacity: 1;
        }
        .tb-transform-origin-value {
            font-size: 11px;
            color: var(--tb-text-muted);
            text-align: center;
            padding: 4px 8px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
        }
        .tb-transform-origin-custom {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .tb-transform-origin-custom input {
            width: 60px;
            padding: 4px 6px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
            text-align: center;
        }
        .tb-transform-origin-custom label {
            font-size: 10px;
            color: var(--tb-text-muted);
            display: flex;
            flex-direction: column;
            gap: 2px;
            align-items: center;
        }
        .tb-transform-reset-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 8px 12px;
            background: rgba(137, 180, 250, 0.1);
            border: 1px solid rgba(137, 180, 250, 0.2);
            border-radius: 6px;
            color: #89b4fa;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 12px;
            transition: all 0.15s;
        }
        .tb-transform-reset-btn:hover { background: rgba(137, 180, 250, 0.2); }

        /* ═══════════════════════════════════════════════════════════
           AI BUTTON STYLING
           ═══════════════════════════════════════════════════════════ */
        .tb-btn-ai {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.25);
        }
        .tb-btn-ai:hover {
            background: linear-gradient(135deg, #7c4ddb, #5558e3);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
        }
        .tb-btn-ai:disabled {
            opacity: 0.6;
            cursor: wait;
            transform: none;
        }
        .tb-btn-ai.loading::after {
            content: '';
            width: 12px;
            height: 12px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: tb-ai-spin 0.8s linear infinite;
            margin-left: 6px;
        }
        @keyframes tb-ai-spin {
            to { transform: rotate(360deg); }
        }

        /* Media Gallery Button */
        .tb-btn-media {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--tb-surface);
            border: 1px solid var(--tb-border);
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .tb-btn-media:hover {
            background: var(--tb-surface-hover);
            border-color: var(--tb-accent);
        }

        /* ═══════════════════════════════════════════════════════════
           TYPOGRAPHY PANEL STYLING
           ═══════════════════════════════════════════════════════════ */
        .tb-typography-section {
            margin-bottom: 16px;
            border: 1px solid var(--tb-border);
            border-radius: 8px;
            overflow: hidden;
        }
        .tb-typography-section > .tb-setting-group:first-child {
            background: var(--tb-surface-2);
            margin: 0;
            padding: 10px 12px;
        }
        .tb-typography-toggle {
            transition: transform 0.2s;
        }
        .tb-typography-content {
            padding: 12px;
        }
        .tb-typography-content[style*="display: none"] + .tb-typography-toggle,
        .tb-typography-section.collapsed .tb-typography-toggle {
            transform: rotate(-90deg);
        }
        .tb-device-icon {
            display: inline-flex;
            width: 16px;
            height: 16px;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            border-radius: 3px;
            margin-right: 4px;
        }
        .tb-device-icon.desktop { background: rgba(139, 180, 250, 0.2); color: #89b4fa; }
        .tb-device-icon.tablet { background: rgba(166, 227, 161, 0.2); color: #a6e3a1; }
        .tb-device-icon.mobile { background: rgba(249, 226, 175, 0.2); color: #f9e2af; }
        .tb-responsive-badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: 600;
            background: rgba(139, 180, 250, 0.15);
            color: #89b4fa;
            border-radius: 3px;
            margin-left: 6px;
        }
        .tb-responsive-badge.has-responsive {
            background: rgba(249, 226, 175, 0.2);
            color: #f9e2af;
        }

        /* Content Panel AI Field Styling */
        .tb-field-with-ai {
            position: relative;
        }
        .tb-field-with-ai .tb-btn-ai {
            position: absolute;
            right: 8px;
            bottom: 8px;
        }
        .tb-ai-field-row {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }
        .tb-ai-field-row .tb-input,
        .tb-ai-field-row textarea {
            flex: 1;
        }
        /* State Toggle (Normal/Hover) */
        .tb-state-toggle {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            padding: 4px;
            background: var(--tb-bg-tertiary);
            border-radius: 8px;
        }
        .tb-state-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: var(--tb-text-dim);
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .tb-state-btn:hover {
            background: var(--tb-bg-secondary);
            color: var(--tb-text);
        }
        .tb-state-btn.active {
            background: var(--tb-accent);
            color: #fff;
        }
        .tb-state-btn.hover-state.active {
            background: #f59e0b;
        }
        .tb-state-indicator {
            font-size: 8px;
        }
        .tb-hover-badge {
            background: #10b981;
            color: #fff;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 4px;
            margin-left: 4px;
        }
        
        /* Hover Controls */
        .tb-hover-controls {
            transition: opacity 0.2s;
        }
        .tb-hover-controls.tb-hover-disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .tb-hover-enable-row {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--tb-border);
        }
        .tb-hover-enable-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 13px;
        }
        .tb-hover-control-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .tb-hover-control-row label {
            min-width: 80px;
            font-size: 11px;
            color: var(--tb-text-dim);
        }
        .tb-hover-control-row input[type="range"] {
            flex: 1;
            height: 4px;
            -webkit-appearance: none;
            background: var(--tb-bg-tertiary);
            border-radius: 2px;
        }
        .tb-hover-control-row input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 14px;
            height: 14px;
            background: var(--tb-accent);
            border-radius: 50%;
            cursor: pointer;
        }
        .tb-hover-control-row input[type="number"],
        .tb-hover-control-row input[type="text"] {
            width: 60px;
            padding: 4px 8px;
            background: var(--tb-bg-secondary);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        .tb-hover-control-row select {
            flex: 1;
            padding: 4px 8px;
            background: var(--tb-bg-secondary);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        
        /* Badge for active features */
        .tb-badge-active {
            background: #10b981;
            color: #fff;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 8px;
        }
        
        /* Typography section collapsible */
        .tb-typography-section {
            border: 1px solid var(--tb-border);
            border-radius: 6px;
            margin-bottom: 8px;
            overflow: hidden;
        }
        .tb-typography-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: var(--tb-bg-tertiary);
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
        }
        .tb-typography-header:hover {
            background: var(--tb-bg-secondary);
        }
        .tb-typography-body {
            padding: 12px;
            border-top: 1px solid var(--tb-border);
        }
        .tb-typography-section.collapsed .tb-typography-body {
            display: none;
        }
        .tb-typography-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .tb-typography-row:last-child {
            margin-bottom: 0;
        }
        .tb-typography-row label {
            min-width: 90px;
            font-size: 11px;
            color: var(--tb-text-dim);
        }
        .tb-typography-row select,
        .tb-typography-row input {
            flex: 1;
            padding: 6px 8px;
            background: var(--tb-bg-secondary);
            border: 1px solid var(--tb-border);
            border-radius: 4px;
            color: var(--tb-text);
            font-size: 11px;
        }
        .tb-typography-row .unit-select {
            width: 50px;
            flex: none;
        }
        
        /* Filter preview */
        .tb-filter-preview {
            width: 100%;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .tb-filter-preview-icon {
            font-size: 32px;
        }

    </style>
    <!-- Font Awesome (local) -->
    <link rel="stylesheet" href="/assets/css/fontawesome.min.css">
</head>
<body>
<div class="tb-container">
    <!-- Toolbar -->
    <header class="tb-toolbar">
        <div class="tb-toolbar-left">
            <a href="/admin/theme-builder/templates" class="tb-btn tb-btn-icon" title="Back to Templates">←</a>
            <span style="font-size:20px"><?= $typeIcon ?></span>
            <input type="text" id="templateName" class="tb-input" style="width:200px" value="<?= $templateName ?>" placeholder="Template Name">
            <span style="background:var(--tb-accent);color:#1e1e2e;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600"><?= $typeLabel ?></span>
        </div>
        <div class="tb-toolbar-right">
            <button class="tb-btn" onclick="TB.openLibrary()" title="Preset Library">📚 Library</button>
            <button class="tb-btn" onclick="TB.preview()" title="Preview">👁 Preview</button>
            <button class="tb-btn tb-btn-primary" id="saveBtn" onclick="TB.save()">💾 Save</button>
        </div>
    </header>
    
    <!-- Main Layout -->
    <div class="tb-main">
        <!-- Left Panel - Modules -->
        <aside class="tb-panel tb-panel-left">
            <div class="tb-search">
                <input type="text" id="moduleSearch" placeholder="Search modules..." oninput="TB.filterModules(this.value)">
            </div>
            <div class="tb-panel-content" id="modulesList"></div>
        </aside>
        
        <!-- Canvas -->
        <main class="tb-canvas">
            <div class="tb-canvas-inner" id="canvas"></div>
        </main>
        
        </aside>
    </div>
</div>

<div class="tb-toast" id="toast"></div>

<!-- Media Gallery Modal -->
<div class="tb-modal" id="mediaModal">
    <div class="tb-modal-content">
        <div class="tb-modal-header">
            <h3>📷 Media Gallery</h3>
            <button class="tb-modal-close" onclick="TB.closeMediaModal()">&times;</button>
        </div>
        <div class="tb-modal-body">
            <div class="tb-media-grid" id="mediaGrid">
                <div class="tb-media-item tb-media-upload" onclick="TB.uploadMedia()">
                    <span style="font-size:32px">📤</span>
                    <span>Upload Image</span>
                </div>
            </div>
        </div>
        <div class="tb-modal-footer">
            <button class="tb-btn" onclick="TB.closeMediaModal()">Cancel</button>
            <button class="tb-btn tb-btn-primary" onclick="TB.selectMedia()">Select</button>
        </div>
    </div>
</div>

<!-- Layout Picker Modal -->
<div class="tb-modal" id="layoutModal">
    <div class="tb-modal-content" style="max-width:600px">
        <div class="tb-modal-header">
            <h3>📐 Choose Row Layout</h3>
            <button class="tb-modal-close" onclick="TB.closeLayoutModal()">&times;</button>
        </div>
        <div class="tb-modal-body">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px" id="layoutOptions"></div>
        </div>
    </div>
</div>

<!-- Preset Library Modal -->
<div class="tb-modal" id="libraryModal">
    <div class="tb-modal-content" style="max-width:900px">
        <div class="tb-modal-header">
            <h3>📚 Preset Library</h3>
            <button class="tb-modal-close" onclick="TB.closeLibrary()">&times;</button>
        </div>
        <div class="tb-modal-body" style="padding:0">
            <div class="tb-library-search" style="padding:16px;border-bottom:1px solid var(--tb-border)">
                <input type="text" class="tb-input" id="librarySearch" placeholder="Search presets..." oninput="TB.filterPresets(this.value)">
            </div>
            <div class="tb-library-grid" id="libraryGrid" style="padding:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-height:60vh;overflow-y:auto">
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tb-text-dim)">
                    <span style="font-size:32px;display:block;margin-bottom:8px">⏳</span>
                    Loading presets...
                </div>
            </div>
        </div>
        <div class="tb-modal-footer">
            <button class="tb-btn" onclick="TB.closeLibrary()">Cancel</button>
            <button class="tb-btn tb-btn-primary" id="usePresetBtn" onclick="TB.usePreset()" disabled>Use This Preset</button>
        </div>
    </div>
</div>

<style>
.tb-preset-card {
    background: var(--tb-bg-tertiary);
    border: 2px solid var(--tb-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}
.tb-preset-card:hover {
    border-color: var(--tb-accent);
    transform: translateY(-2px);
}
.tb-preset-card.selected {
    border-color: var(--tb-accent);
    box-shadow: 0 0 0 3px rgba(137,180,250,0.3);
}
.tb-preset-thumb {
    height: 120px;
    background: var(--tb-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: var(--tb-text-dim);
}
.tb-preset-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.tb-preset-info {
    padding: 12px;
}
.tb-preset-name {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
}
.tb-preset-desc {
    font-size: 12px;
    color: var(--tb-text-dim);
    line-height: 1.4;
}
.tb-preset-tags {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.tb-preset-tag {
    font-size: 10px;
    padding: 2px 6px;
    background: var(--tb-surface);
    border-radius: 4px;
    color: var(--tb-text-dim);
}
.tb-preset-premium {
    background: linear-gradient(135deg, #f9e2af, #fab387);
    color: #1e1e2e;
}
.tb-library-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--tb-text-dim);
}
.tb-library-empty-icon {
    font-size: 48px;
    display: block;
    margin-bottom: 12px;
    opacity: 0.5;
}
</style>


<script>
// Theme Builder 3.0 - Template Editor Initialization
const TB = {
    templateId: <?= $templateId ?>,
    templateType: '<?= $templateType ?>',
    csrfToken: '<?= $csrfToken ?>',
    content: <?= $contentJson ?>,
    modules: <?= $modulesJson ?>,
    categories: <?= $categoriesJson ?>,
    themeColors: <?= $themeColorsJson ?>,
    savedConditions: <?= $savedConditions ?>,
    selectedElement: null,
    currentDevice: 'desktop',
    currentModuleTab: 'content',
    isDirty: false,
    textModules: ['text', 'heading', 'button', 'quote', 'cta', 'testimonial', 'blurb', 'hero', 'pricing'],
    elementModules: ['toggle', 'accordion', 'tabs', 'button']
};
</script>

<!-- Theme Builder 3.0 Modular JS -->
<script src="/core/theme-builder/js/tb-core.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-helpers.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modules-preview.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modules-content.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modules-design.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-structure.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-events.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-render.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-library.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modal-editor.js?v=20260104k"></script>

<!-- Element Design System (Continued) -->
<script src="/core/theme-builder/js/tb-element-schemas.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modal-element-design.js?v=20260105G"></script>
<script src="/core/theme-builder/js/tb-modal-element-design-part2.js?v=20260104k"></script>
<script src="/core/theme-builder/js/tb-modal-element-design-part3.js?v=20260104k"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    TB.init();
    if (typeof TB.initMediaGalleryEvents === 'function') {
        TB.initMediaGalleryEvents();
    }
});
</script>
</body>
</html>
