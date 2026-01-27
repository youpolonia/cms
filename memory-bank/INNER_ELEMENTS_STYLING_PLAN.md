# THEME BUILDER 3.0 - INNER ELEMENTS STYLING SYSTEM
## Complete Implementation Plan for Claude Code

**Author:** Claude (Opus)  
**Date:** 2026-01-03  
**Priority:** HIGH - Core UX Feature  
**Estimated Time:** 2-3 weeks

---

## 1. PROBLEM STATEMENT

### Current State
- Design panel styles ONLY the module wrapper/container
- Inner elements (toggle items, accordion headers, buttons, etc.) are NOT editable
- Users have ZERO control over the most important visual elements
- This makes Theme Builder significantly inferior to Divi/Elementor

### Example: Toggle Module
```
┌─────────────────────────────────────┐ ← Module Wrapper (EDITABLE)
│  ┌───────────────────────────────┐  │
│  │ Toggle Item 1              +  │  │ ← Toggle Header (NOT EDITABLE)
│  ├───────────────────────────────┤  │
│  │ Content here...               │  │ ← Toggle Content (NOT EDITABLE)
│  └───────────────────────────────┘  │
└─────────────────────────────────────┘
```

### Target State (Divi-like)
- Every module has defined "inner elements"
- Each inner element has its own styling options
- Modal editor for comprehensive editing
- CSS variables system for theming

---

## 2. ARCHITECTURE OVERVIEW

### 2.1 Three-Level Styling System

```
MODULE STYLING LEVELS:
├── Level 1: Module Wrapper (existing)
│   ├── Background, Border, Shadow, Padding, Margin
│   └── Hover Effects for wrapper
│
├── Level 2: Inner Elements (NEW)
│   ├── Element-specific backgrounds
│   ├── Element-specific colors
│   ├── Element-specific borders
│   ├── Element-specific typography
│   └── Element-specific hover states
│
└── Level 3: States (NEW)
    ├── Normal state
    ├── Hover state
    ├── Active state (for toggles, tabs, etc.)
    └── Focus state (for forms)
```

### 2.2 New JSON Structure

**Current:**
```json
{
  "id": "mod_123",
  "type": "toggle",
  "content": { "items": [...] },
  "settings": {},
  "design": {}
}
```

**New:**
```json
{
  "id": "mod_123",
  "type": "toggle",
  "content": { "items": [...] },
  "settings": {},
  "design": {
    "wrapper": {
      "background": "#ffffff",
      "padding": "20px",
      "border_radius": "8px"
    },
    "elements": {
      "header": {
        "normal": {
          "background": "#f5f5f5",
          "color": "#333333",
          "font_size": "16px",
          "font_weight": "600",
          "padding": "16px 20px",
          "border_radius": "4px"
        },
        "hover": {
          "background": "#e0e0e0",
          "color": "#000000"
        },
        "active": {
          "background": "#007bff",
          "color": "#ffffff"
        }
      },
      "content": {
        "normal": {
          "background": "#ffffff",
          "color": "#666666",
          "padding": "20px"
        }
      },
      "icon": {
        "normal": { "color": "#999", "size": "12px" },
        "active": { "color": "#007bff", "transform": "rotate(180deg)" }
      }
    }
  }
}
```

---

## 3. INNER ELEMENTS PER MODULE

### TOGGLE MODULE
- header: background, color, font_size, font_weight, padding, border_radius + hover/active states
- content: background, color, font_size, padding
- icon: color, size + active state
- item: margin_bottom, border, border_radius

### ACCORDION MODULE
- header, content, icon, item, divider

### TABS MODULE
- nav: background, border_bottom, padding
- tab_button: normal/hover/active states
- content: background, color, padding
- indicator: color, height

### BUTTON MODULE
- button: full styling with normal/hover/active
- icon: color, size, margin
- text: font_family, letter_spacing, text_transform

### TEXT MODULE
- paragraph: color, font_size, font_family, line_height
- link: normal/hover states
- highlight: background, color

### HEADING MODULE
- heading: full typography
- underline: color, width, height
- subtitle: color, font_size

### IMAGE MODULE
- image: border_radius, border, shadow + hover
- overlay: background, opacity
- caption: color, font_size, background
- lightbox_icon: color, size

### LIST MODULE
- item: color, font_size, padding
- bullet: color, size, type
- icon: color, size

### FORM MODULE
- label: color, font_size, font_weight
- input: normal/focus/error states
- submit_button: full button styling
- error_message, success_message

### GALLERY MODULE
- image: border_radius + hover
- overlay: background, opacity
- caption: styling
- grid: gap, columns

### HERO MODULE
- title: color, font_size, font_weight, text_shadow
- subtitle: color, font_size
- button: full button styling
- overlay: color, opacity, gradient
- content_box: background, padding

### SOCIAL MODULE
- icon: color, size + hover
- background: color, border_radius + hover
- container: gap, alignment

---

## 4. UI IMPLEMENTATION

### 4.1 Design Panel (Right Sidebar)

```
DESIGN TAB
├── 📦 MODULE WRAPPER
│   └── (existing controls)
│
├── 🎨 ELEMENT STYLES (NEW)
│   ├── ▼ Header Styles
│   │   ├── [Normal] [Hover] [Active] tabs
│   │   ├── Background, Text Color, Typography, Padding
│   ├── ▼ Content Styles
│   ├── ▼ Icon Styles
│   └── ▼ Item Styles
│
└── ⚙️ ADVANCED
```

### 4.2 Modal Editor (NEW - onclick module)

```
┌────────────────────────────────────────────────┐
│  Toggle Module Settings                    ✕   │
├────────────────────────────────────────────────┤
│  [Content]  [Design]  [Advanced]               │
├────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────┐  │
│  │           LIVE PREVIEW                   │  │
│  │  ┌────────────────────────────────────┐  │  │
│  │  │ Toggle Item 1                   +  │  │  │
│  │  └────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────┘  │
│                                                │
│  ELEMENT: [Wrapper▼] [Header▼] [Content▼]      │
│  STATE:   ○ Normal  ○ Hover  ○ Active          │
│                                                │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐          │
│  │Background│ │Typography│ │ Spacing │          │
│  └─────────┘ └─────────┘ └─────────┘          │
├────────────────────────────────────────────────┤
│                    [Cancel] [Save]             │
└────────────────────────────────────────────────┘
```

---

## 5. CSS VARIABLES SYSTEM

### Per-Module CSS Generation:
```css
#mod_123 {
  --mod-header-bg: #f5f5f5;
  --mod-header-bg-hover: #e0e0e0;
  --mod-header-color: #333;
}

#mod_123 .tb-toggle-header {
  background: var(--mod-header-bg);
  color: var(--mod-header-color);
}

#mod_123 .tb-toggle-header:hover {
  background: var(--mod-header-bg-hover);
}
```

---

## 6. RENDERER CHANGES

```php
function tb_generate_module_element_css(array $module): string
{
    $moduleId = $module['id'] ?? '';
    $type = $module['type'] ?? '';
    $elements = $module['design']['elements'] ?? [];
    
    if (empty($elements)) return '';
    
    $elementMap = tb_get_element_map($type);
    $css = "";
    
    foreach ($elements as $elementName => $states) {
        $selector = $elementMap[$elementName] ?? null;
        if (!$selector) continue;
        
        foreach ($states as $state => $styles) {
            $stateSelector = match($state) {
                'hover' => ':hover',
                'active' => '.active',
                default => ''
            };
            
            $css .= "#$moduleId $selector$stateSelector {\n";
            foreach ($styles as $prop => $val) {
                $css .= "  " . tb_to_css_property($prop) . ": $val;\n";
            }
            $css .= "}\n";
        }
    }
    
    return $css;
}

function tb_get_element_map(string $type): array
{
    return match($type) {
        'toggle' => [
            'header' => '.tb-toggle-header',
            'content' => '.tb-toggle-content',
            'icon' => '.tb-toggle-icon',
            'item' => '.tb-toggle-item'
        ],
        'accordion' => [
            'header' => '.tb-accordion-header',
            'content' => '.tb-accordion-content',
            'icon' => '.tb-accordion-icon'
        ],
        'tabs' => [
            'nav' => '.tb-tabs-nav',
            'tab_button' => '.tb-tab-btn',
            'content' => '.tb-tab-panel'
        ],
        'button' => [
            'button' => '.tb-button'
        ],
        default => []
    };
}
```

---

## 7. NEW JS FILES

```
/core/theme-builder/js/
├── tb-element-editor.js    - Element definitions & panel rendering
├── tb-modal-editor.js      - Modal editor logic
├── tb-presets.js           - Style presets per module
└── tb-css-generator.js     - CSS generation from element styles
```

---

## 8. IMPLEMENTATION PHASES

### Phase 1: Foundation (3-4 days)
- Update JSON structure
- Create element definitions
- Create CSS generator
- Update migrateContent()

### Phase 2: Renderer (3-4 days)
- Add element CSS generation
- Add element maps for all modules
- Update module render functions

### Phase 3: Sidebar UI (2-3 days)
- Add Element Styles section
- Collapsible sections per element
- State tabs (Normal/Hover/Active)

### Phase 4: Modal Editor (3-4 days)
- Create modal HTML/CSS
- Implement modal JS
- Live preview
- Element/state selectors

### Phase 5: Presets (2 days)
- Define presets per module
- Preset selector UI
- Apply preset logic

### Phase 6: Testing (2-3 days)
- Test all 52 modules
- Fix edge cases
- Performance optimization

---

## 9. PRIORITY ORDER

1. **HIGH:** Toggle, Accordion, Tabs (interactive modules)
2. **HIGH:** Button (most used)
3. **MEDIUM:** Heading, Text (typography)
4. **MEDIUM:** Image, Gallery (hover effects)
5. **LOWER:** Other modules

---

## 10. BACKWARD COMPATIBILITY

- Old modules without `design.elements` continue working
- Default styles from `tb-frontend.css`
- Custom element styles override via CSS specificity
- migrateContent() preserves existing (no auto-convert)

---

## 11. NOTES FOR CLAUDE CODE

1. Follow existing patterns in tb-modules-design.js
2. Use consistent CSS class naming
3. Test incrementally - don't do all 52 at once
4. Preserve existing functionality
5. Generate CSS once on save, not every render

**END OF PLAN**
