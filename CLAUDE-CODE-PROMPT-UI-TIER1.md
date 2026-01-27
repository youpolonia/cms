# TB4 UI TIER 1 - PROFESSIONAL FIELD CONTROLS
## Claude Code Implementation Prompt

**Date:** 2026-01-11
**Author:** Claude (Architect)
**Task:** Build professional UI controls for TB4 Theme Builder

---

## CRITICAL CONSTRAINTS (MUST FOLLOW)

```
- NO CLI (system/exec/shell_exec FORBIDDEN)
- Pure PHP 8.1+ only (but this task is JS/CSS)
- FTP-only deployment (no build tools)
- require_once only for PHP
- No closing ?> in PHP files
- ALL FILENAMES LOWERCASE
- No npm/webpack/build tools
- VANILLA JAVASCRIPT ONLY (NO React, Vue, jQuery)
- ES6+ syntax allowed (const, let, arrow functions, classes, template literals)
```

---

## TASK OVERVIEW

Create professional UI field controls for TB4 Theme Builder that match Divi's quality level.
Replace current basic HTML controls with custom-styled, feature-rich components.

**Files to create:**
1. `/public/assets/tb4/css/fields.css` (~1500 lines) - All field styling
2. `/public/assets/tb4/js/fields.js` (~2000 lines) - Field components

**Files to modify:**
3. `/public/assets/tb4/js/builder.js` - Update renderSettingsField() to use new components

---

## DESIGN SYSTEM (Use these values consistently)

### Colors
```css
/* Primary */
--tb4-primary: #2563eb;
--tb4-primary-hover: #1d4ed8;
--tb4-primary-light: #3b82f6;
--tb4-primary-bg: rgba(37, 99, 235, 0.1);

/* Secondary */
--tb4-secondary: #10b981;
--tb4-secondary-hover: #059669;

/* Accent */
--tb4-accent: #f59e0b;
--tb4-danger: #ef4444;
--tb4-danger-hover: #dc2626;

/* Neutrals (Dark Theme - Catppuccin inspired) */
--tb4-bg-base: #1e1e2e;
--tb4-bg-surface: #313244;
--tb4-bg-overlay: #45475a;
--tb4-bg-input: #181825;

--tb4-text-primary: #cdd6f4;
--tb4-text-secondary: #a6adc8;
--tb4-text-muted: #6c7086;
--tb4-text-subtle: #585b70;

--tb4-border: #45475a;
--tb4-border-hover: #585b70;
--tb4-border-focus: #2563eb;

/* Shadows */
--tb4-shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
--tb4-shadow-md: 0 4px 6px rgba(0,0,0,0.4);
--tb4-shadow-lg: 0 10px 15px rgba(0,0,0,0.5);
--tb4-shadow-focus: 0 0 0 3px rgba(37, 99, 235, 0.3);
```

### Typography
```css
--tb4-font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
--tb4-font-size-xs: 11px;
--tb4-font-size-sm: 12px;
--tb4-font-size-md: 13px;
--tb4-font-size-lg: 14px;
```

### Spacing & Sizing
```css
--tb4-radius-sm: 4px;
--tb4-radius-md: 6px;
--tb4-radius-lg: 8px;
--tb4-radius-xl: 12px;

--tb4-space-xs: 4px;
--tb4-space-sm: 8px;
--tb4-space-md: 12px;
--tb4-space-lg: 16px;
--tb4-space-xl: 24px;

--tb4-input-height: 36px;
--tb4-input-height-sm: 28px;
```

### Transitions
```css
--tb4-transition-fast: 0.15s ease;
--tb4-transition-normal: 0.2s ease;
--tb4-transition-slow: 0.3s ease;
```

---

## COMPONENT SPECIFICATIONS

### 1. TB4ColorPicker

**Features required:**
- Custom color swatch display (not native input)
- Click opens color panel dropdown
- Alpha/opacity slider (0-100%)
- HEX input with # prefix
- RGB inputs (R, G, B separate)
- Opacity input (0-100)
- Color swatches row (8 preset colors + recent colors)
- Eyedropper button (uses native if available)
- Clear/Reset button
- Live preview swatch

**HTML Structure:**
```html
<div class="tb4-color-picker" data-field="fieldName">
  <div class="tb4-color-trigger">
    <div class="tb4-color-swatch" style="background: rgba(r,g,b,a)"></div>
    <input type="text" class="tb4-color-value" value="#2563eb" readonly>
    <button type="button" class="tb4-color-clear" title="Clear">×</button>
  </div>
  <div class="tb4-color-dropdown">
    <div class="tb4-color-preview-large"></div>
    <div class="tb4-color-spectrum"></div>
    <div class="tb4-color-hue-slider"></div>
    <div class="tb4-color-alpha-slider"></div>
    <div class="tb4-color-inputs">
      <input type="text" class="tb4-color-hex" placeholder="#000000">
      <div class="tb4-color-rgb">
        <input type="number" class="tb4-color-r" min="0" max="255" placeholder="R">
        <input type="number" class="tb4-color-g" min="0" max="255" placeholder="G">
        <input type="number" class="tb4-color-b" min="0" max="255" placeholder="B">
      </div>
      <input type="number" class="tb4-color-opacity" min="0" max="100" placeholder="100%">
    </div>
    <div class="tb4-color-swatches">
      <!-- Preset colors -->
    </div>
  </div>
</div>
```

**JavaScript API:**
```javascript
class TB4ColorPicker {
  constructor(container, options = {}) {
    this.container = container;
    this.value = options.value || '';
    this.showAlpha = options.showAlpha !== false;
    this.swatches = options.swatches || [...defaultSwatches];
    this.onChange = options.onChange || (() => {});
  }
  
  getValue() { return this.value; } // Returns rgba() or hex
  setValue(color) { /* update UI */ }
  open() { /* show dropdown */ }
  close() { /* hide dropdown */ }
  destroy() { /* cleanup */ }
}
```

---

### 2. TB4RangeSlider

**Features required:**
- Custom styled track and thumb
- Value display next to slider (updates live)
- Optional units dropdown (px, %, em, rem, vw, vh)
- Min/max labels
- Optional step marks
- Keyboard support (arrow keys)
- Double-click to reset to default

**HTML Structure:**
```html
<div class="tb4-range-slider" data-field="fieldName">
  <div class="tb4-range-track">
    <div class="tb4-range-fill"></div>
    <div class="tb4-range-thumb" tabindex="0"></div>
  </div>
  <div class="tb4-range-value">
    <input type="number" class="tb4-range-input" value="16">
    <select class="tb4-range-unit">
      <option value="px">px</option>
      <option value="%">%</option>
      <option value="em">em</option>
      <option value="rem">rem</option>
      <option value="vw">vw</option>
      <option value="vh">vh</option>
    </select>
  </div>
</div>
```

**JavaScript API:**
```javascript
class TB4RangeSlider {
  constructor(container, options = {}) {
    this.min = options.min || 0;
    this.max = options.max || 100;
    this.step = options.step || 1;
    this.value = options.value || this.min;
    this.unit = options.unit || 'px';
    this.showUnit = options.showUnit !== false;
    this.units = options.units || ['px', '%', 'em', 'rem'];
    this.onChange = options.onChange || (() => {});
  }
  
  getValue() { return `${this.value}${this.unit}`; }
  setValue(value) { /* parse and update */ }
  setUnit(unit) { /* change unit */ }
}
```

---

### 3. TB4Toggle (Switch)

**Features required:**
- iOS-style toggle switch
- Smooth slide animation
- Optional labels (On/Off or custom)
- Keyboard accessible (Space/Enter)
- Disabled state

**HTML Structure:**
```html
<div class="tb4-toggle" data-field="fieldName">
  <button type="button" class="tb4-toggle-switch" role="switch" aria-checked="false">
    <span class="tb4-toggle-track"></span>
    <span class="tb4-toggle-thumb"></span>
  </button>
  <span class="tb4-toggle-label">Enable</span>
</div>
```

**CSS Sizing:**
- Track: 44px × 24px
- Thumb: 20px × 20px
- Smooth transition on toggle

---

### 4. TB4Select (Custom Dropdown)

**Features required:**
- Custom styled (no native select appearance)
- Search/filter for long lists (optional)
- Option groups support
- Icons in options (optional)
- Keyboard navigation (arrow keys, enter, escape)
- Max height with scroll

**HTML Structure:**
```html
<div class="tb4-select" data-field="fieldName">
  <button type="button" class="tb4-select-trigger">
    <span class="tb4-select-value">Selected Value</span>
    <svg class="tb4-select-arrow"><!-- chevron-down --></svg>
  </button>
  <div class="tb4-select-dropdown">
    <input type="text" class="tb4-select-search" placeholder="Search...">
    <div class="tb4-select-options">
      <div class="tb4-select-option" data-value="value1">Option 1</div>
      <div class="tb4-select-option selected" data-value="value2">Option 2</div>
      <div class="tb4-select-group">
        <div class="tb4-select-group-label">Group Name</div>
        <div class="tb4-select-option" data-value="value3">Option 3</div>
      </div>
    </div>
  </div>
</div>
```

---

### 5. TB4SpacingBox (Visual Box Model)

**Features required:**
- Visual representation like Chrome DevTools
- Margin (outer) and Padding (inner) sections
- Click on segment to edit
- Link/unlink toggle for all sides
- Responsive tabs (desktop/tablet/mobile)
- Color coding: margin = orange, padding = green

**HTML Structure:**
```html
<div class="tb4-spacing-box" data-field="fieldName">
  <div class="tb4-spacing-header">
    <span class="tb4-spacing-label">Spacing</span>
    <div class="tb4-responsive-tabs">
      <button class="tb4-resp-tab active" data-device="desktop">
        <svg><!-- monitor icon --></svg>
      </button>
      <button class="tb4-resp-tab" data-device="tablet">
        <svg><!-- tablet icon --></svg>
      </button>
      <button class="tb4-resp-tab" data-device="mobile">
        <svg><!-- smartphone icon --></svg>
      </button>
    </div>
  </div>
  <div class="tb4-spacing-visual">
    <div class="tb4-spacing-margin">
      <span class="tb4-spacing-margin-label">MARGIN</span>
      <input class="tb4-spacing-input tb4-margin-top" data-side="margin-top" value="0">
      <input class="tb4-spacing-input tb4-margin-right" data-side="margin-right" value="0">
      <input class="tb4-spacing-input tb4-margin-bottom" data-side="margin-bottom" value="0">
      <input class="tb4-spacing-input tb4-margin-left" data-side="margin-left" value="0">
      <div class="tb4-spacing-padding">
        <span class="tb4-spacing-padding-label">PADDING</span>
        <input class="tb4-spacing-input tb4-padding-top" data-side="padding-top" value="0">
        <input class="tb4-spacing-input tb4-padding-right" data-side="padding-right" value="0">
        <input class="tb4-spacing-input tb4-padding-bottom" data-side="padding-bottom" value="0">
        <input class="tb4-spacing-input tb4-padding-left" data-side="padding-left" value="0">
        <div class="tb4-spacing-content">
          <span>CONTENT</span>
        </div>
      </div>
    </div>
  </div>
  <div class="tb4-spacing-controls">
    <button class="tb4-spacing-link active" title="Link all values">
      <svg><!-- link icon --></svg>
    </button>
    <select class="tb4-spacing-unit">
      <option value="px">px</option>
      <option value="%">%</option>
      <option value="em">em</option>
    </select>
  </div>
</div>
```

---

### 6. TB4ResponsiveTabs

**Features required:**
- SVG icons for Desktop/Tablet/Mobile (NOT emoji!)
- Active state indicator
- Smooth transitions
- Shows which device has custom values (dot indicator)

**SVG Icons to use (Lucide):**
```html
<!-- Desktop (Monitor) -->
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect width="20" height="14" x="2" y="3" rx="2"/>
  <line x1="8" x2="16" y1="21" y2="21"/>
  <line x1="12" x2="12" y1="17" y2="21"/>
</svg>

<!-- Tablet -->
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
  <line x1="12" x2="12.01" y1="18" y2="18"/>
</svg>

<!-- Mobile (Smartphone) -->
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
  <line x1="12" x2="12.01" y1="18" y2="18"/>
</svg>
```

---

### 7. TB4CollapsibleSection

**Features required:**
- Smooth height animation (not display:none toggle)
- Rotate arrow icon on expand/collapse
- Optional icon before title
- Nested sections support
- Remember state (optional localStorage)

**HTML Structure:**
```html
<div class="tb4-collapsible" data-section="sectionId">
  <button type="button" class="tb4-collapsible-header">
    <svg class="tb4-collapsible-arrow"><!-- chevron-right, rotates 90deg when open --></svg>
    <span class="tb4-collapsible-title">Section Title</span>
  </button>
  <div class="tb4-collapsible-content">
    <!-- Content here -->
  </div>
</div>
```

**CSS Animation:**
```css
.tb4-collapsible-content {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s ease;
}
.tb4-collapsible.open .tb4-collapsible-content {
  grid-template-rows: 1fr;
}
.tb4-collapsible-content > div {
  overflow: hidden;
}
```

---

### 8. TB4Tooltip

**Features required:**
- Hover triggered
- Configurable position (top, bottom, left, right)
- Arrow pointer
- Max width with text wrap
- Delay before show (300ms)

**Usage:**
```html
<button data-tb4-tooltip="This is helpful information" data-tooltip-position="top">
  <svg><!-- help-circle icon --></svg>
</button>
```

---

### 9. TB4ButtonGroup (Multiple Buttons / Text Align)

**Features required:**
- Exclusive selection (radio-like)
- SVG icons for each option
- Active state styling
- Keyboard navigation

**Example for Text Align:**
```html
<div class="tb4-btn-group" data-field="textAlign">
  <button type="button" class="tb4-btn-group-item" data-value="left" title="Align Left">
    <svg><!-- align-left --></svg>
  </button>
  <button type="button" class="tb4-btn-group-item active" data-value="center" title="Align Center">
    <svg><!-- align-center --></svg>
  </button>
  <button type="button" class="tb4-btn-group-item" data-value="right" title="Align Right">
    <svg><!-- align-right --></svg>
  </button>
  <button type="button" class="tb4-btn-group-item" data-value="justify" title="Justify">
    <svg><!-- align-justify --></svg>
  </button>
</div>
```

---

### 10. TB4ResetButton

**Features required:**
- Small icon button next to fields
- Shows only when value differs from default
- Click resets to default value
- Tooltip "Reset to default"

**HTML:**
```html
<button type="button" class="tb4-reset-btn" title="Reset to default" style="display: none;">
  <svg><!-- rotate-ccw icon --></svg>
</button>
```

---

## LUCIDE ICONS TO INCLUDE

Create SVG sprite or inline functions for these icons:

```javascript
const TB4Icons = {
  chevronDown: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>',
  chevronRight: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
  monitor: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>',
  tablet: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>',
  smartphone: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" x2="12.01" y1="18" y2="18"/></svg>',
  link: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
  unlink: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18.84 12.25 1.72-1.71h-.02a5.004 5.004 0 0 0-.12-7.07 5.006 5.006 0 0 0-6.95 0l-1.72 1.71"/><path d="m5.17 11.75-1.71 1.71a5.004 5.004 0 0 0 .12 7.07 5.006 5.006 0 0 0 6.95 0l1.71-1.71"/><line x1="8" x2="8" y1="2" y2="5"/><line x1="2" x2="5" y1="8" y2="8"/><line x1="16" x2="16" y1="19" y2="22"/><line x1="19" x2="22" y1="16" y2="16"/></svg>',
  alignLeft: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="15" x2="3" y1="12" y2="12"/><line x1="17" x2="3" y1="18" y2="18"/></svg>',
  alignCenter: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="17" x2="7" y1="12" y2="12"/><line x1="19" x2="5" y1="18" y2="18"/></svg>',
  alignRight: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="21" x2="9" y1="12" y2="12"/><line x1="21" x2="7" y1="18" y2="18"/></svg>',
  alignJustify: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>',
  rotateCcw: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>',
  helpCircle: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>',
  x: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  check: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
  pipette: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 22 1-1h3l9-9"/><path d="M3 21v-3l9-9"/><path d="m15 6 3.4-3.4a2.1 2.1 0 1 1 3 3L18 9l.4.4a2.1 2.1 0 1 1-3 3l-3.8-3.8a2.1 2.1 0 1 1 3-3l.4.4Z"/></svg>',
  bold: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg>',
  italic: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>',
  underline: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/></svg>',
  type: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/></svg>',
};
```

---

## FILE STRUCTURE

```
/public/assets/tb4/
├── css/
│   ├── builder.css      (existing - keep)
│   └── fields.css       (NEW - create this)
├── js/
│   ├── builder.js       (existing - modify)
│   └── fields.js        (NEW - create this)
```

---

## INTEGRATION WITH builder.js

After creating fields.js and fields.css, update builder.js:

1. **Load new assets** in the HTML (edit.php or wherever builder loads):
```html
<link rel="stylesheet" href="/assets/tb4/css/fields.css">
<script src="/assets/tb4/js/fields.js"></script>
<script src="/assets/tb4/js/builder.js"></script>
```

2. **Update renderSettingsField()** to use new components:
```javascript
renderSettingsField(field, value) {
  switch (field.type) {
    case 'color':
      return TB4Fields.renderColorPicker(field.name, value, field);
    case 'range':
      return TB4Fields.renderRangeSlider(field.name, value, field);
    case 'toggle':
      return TB4Fields.renderToggle(field.name, value, field);
    case 'select':
      return TB4Fields.renderSelect(field.name, value, field);
    // ... etc
  }
}
```

3. **Initialize components** after rendering:
```javascript
// After settings panel HTML is inserted
TB4Fields.initAll(this.dom.settingsPanel);
```

---

## TESTING CHECKLIST

After implementation, verify:

- [ ] ColorPicker opens/closes correctly
- [ ] ColorPicker alpha slider works
- [ ] ColorPicker updates hidden input value
- [ ] RangeSlider thumb drags smoothly
- [ ] RangeSlider value updates in real-time
- [ ] Toggle animates on click
- [ ] Select dropdown opens with keyboard
- [ ] SpacingBox link/unlink works
- [ ] ResponsiveTabs switch content
- [ ] Collapsible sections animate smoothly
- [ ] Tooltips appear after delay
- [ ] All components work with keyboard
- [ ] Dark theme looks consistent
- [ ] No console errors

---

## PRIORITY ORDER

Implement in this order:

1. **fields.css** - CSS variables and base styles
2. **TB4Icons** - SVG icon functions
3. **TB4Toggle** - Simplest component
4. **TB4Select** - Common component
5. **TB4RangeSlider** - Important for design
6. **TB4ColorPicker** - Most complex
7. **TB4ButtonGroup** - For text-align etc
8. **TB4ResponsiveTabs** - Replace emoji tabs
9. **TB4CollapsibleSection** - Replace basic accordion
10. **TB4SpacingBox** - Visual box model
11. **TB4Tooltip** - Polish
12. **TB4ResetButton** - Polish
13. **Integration with builder.js**

---

## EXPECTED OUTPUT FILES

Create these exact files:

1. `/var/www/html/cms/public/assets/tb4/css/fields.css` (~1500 lines)
2. `/var/www/html/cms/public/assets/tb4/js/fields.js` (~2000 lines)

Modify:
3. `/var/www/html/cms/public/assets/tb4/js/builder.js` (update renderSettingsField and add init calls)

---

END OF PROMPT
