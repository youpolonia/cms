# TB4 Module Integration Guide
## Complete Checklist for Adding New Modules

**Created:** 2026-01-10  
**Author:** Claude  
**Status:** AUTHORITATIVE REFERENCE  

---

## EXECUTIVE SUMMARY

Adding a new module to TB4 requires **4 mandatory steps** in **4 different files**.  
Missing ANY step causes the module to fail silently or show "Unknown module!" error.

---

## THE 4-STEP INTEGRATION FLOW

```
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 1: PHP Module Class                                           │
│  /core/tb4/modules/{category}/{slug}module.php                      │
│  └── Defines: slug, name, icon, fields, render()                    │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 2: Registration in init.php                                   │
│  /core/tb4/init.php                                                 │
│  └── require_once + $registry->register()                           │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 3: UI Panel Entry in edit.php                                 │
│  /app/views/admin/tb4/edit.php (lines 3262-3449)                    │
│  └── Hardcoded HTML: <div data-module-type="tb4_{slug}">            │
└─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 4: Render Case in builder.js                                  │
│  /public/assets/tb4/js/builder.js (switch at line 384)              │
│  └── case '{slug}': return `<div>...</div>`;                        │
└─────────────────────────────────────────────────────────────────────┘
```

---

## STEP 1: PHP Module Class

### File Location
```
/core/tb4/modules/{category}/{slug}module.php
```

Categories:
- `content/` - Text, Image, Button, Blurb, CTA, Hero, etc.
- `media/` - Gallery, Video, Audio, Slider
- `interactive/` - Accordion, Tabs, Toggle, Contact
- `structure/` - Section, Row, Column (special)
- `fullwidth/` - FW Header, FW Image, FW Slider, etc.

### Filename Convention
```
{slug}module.php    (ALL LOWERCASE, no underscores in filename)

Examples:
- textmodule.php        (slug: text)
- barcountermodule.php  (slug: bar_counter)
- videoslidermodule.php (slug: video_slider)
```

### Class Template
```php
<?php
namespace Core\TB4\Modules\Content;  // or Media, Interactive, Fullwidth

use Core\TB4\Modules\Module;

class BarCounterModule extends Module
{
    protected string $name = 'Bar Counter';
    protected string $slug = 'bar_counter';        // NO tb4_ prefix!
    protected string $icon = 'bar-chart-2';        // Lucide icon name
    protected string $category = 'content';        // content|media|interactive|fullwidth
    
    public function get_content_fields(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'type' => 'text',
                'default' => 'Progress'
            ],
            'percent' => [
                'label' => 'Percentage',
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'default' => 75
            ]
        ];
    }
    
    public function get_design_fields(): array
    {
        return $this->design_fields;  // Use base class defaults
    }
    
    public function get_advanced_fields(): array
    {
        return $this->advanced_fields;  // CRITICAL: Use base class defaults!
    }
    
    public function render(array $data = []): string
    {
        $title = $data['content']['title'] ?? 'Progress';
        $percent = $data['content']['percent'] ?? 75;
        
        return '<div class="tb4-bar-counter">
            <span>' . htmlspecialchars($title) . '</span>
            <div class="tb4-bar" style="width:' . intval($percent) . '%"></div>
        </div>';
    }
}
```

### CRITICAL RULES FOR STEP 1:
- ✅ Slug is **WITHOUT** `tb4_` prefix (e.g., `bar_counter` not `tb4_bar_counter`)
- ✅ Filename is ALL LOWERCASE
- ✅ Class extends `Module` (or `ChildModule` for child modules)
- ✅ No closing `?>`
- ✅ `get_advanced_fields()` returns `$this->advanced_fields` (NOT custom array!)

---

## STEP 2: Registration in init.php

### File Location
```
/core/tb4/init.php
```

### Two Changes Required:

#### A) Add require_once (around line 35-60)
```php
// Content modules
require_once __DIR__ . '/modules/content/textmodule.php';
require_once __DIR__ . '/modules/content/imagemodule.php';
// ... existing modules ...
require_once __DIR__ . '/modules/content/barcountermodule.php';  // ADD HERE
```

#### B) Add register() call (around line 100-150)
```php
// Content modules
$registry->register(\Core\TB4\Modules\Content\TextModule::class);
$registry->register(\Core\TB4\Modules\Content\ImageModule::class);
// ... existing modules ...
$registry->register(\Core\TB4\Modules\Content\BarCounterModule::class);  // ADD HERE
```

### CRITICAL RULES FOR STEP 2:
- ✅ require_once path must match actual file location
- ✅ Namespace must match file location (Content, Media, Interactive, Fullwidth)
- ✅ Class name must match exactly (case-sensitive)

---

## STEP 3: UI Panel Entry in edit.php

### File Location
```
/app/views/admin/tb4/edit.php
```

### Location in File
Lines 3262-3449 contain hardcoded HTML module list organized by category:
- Content Modules (lines ~3262-3390)
- Media Modules (lines ~3391-3410)
- Interactive Modules (lines ~3411-3430)
- Commerce Modules (lines ~3431-3448)
- Fullwidth Modules (lines ~3449-3480)

### HTML Template to Add
```html
<div class="tb4-module-item" draggable="true" data-module-type="tb4_bar_counter">
    <i data-lucide="bar-chart-2" class="tb4-module-icon"></i>
    <div class="tb4-module-name">Bar Counter</div>
</div>
```

### CRITICAL RULES FOR STEP 3:
- ✅ `data-module-type` uses `tb4_` prefix + slug (e.g., `tb4_bar_counter`)
- ✅ `data-lucide` must match the icon from PHP class
- ✅ Module name should match PHP class $name
- ✅ Place in correct category section

### Why Hardcoded HTML?
The builder.js checks for existing `[data-module-type]` elements on init.
If found, it uses them exclusively and skips dynamic generation.
This is by design for performance and layout control.

---

## STEP 4: Render Case in builder.js

### File Location
```
/public/assets/tb4/js/builder.js
```

### Location in File
The `renderModuleHTML()` method contains a switch statement at line 384:
```javascript
// Line 383: Normalize - REMOVES tb4_ prefix
const moduleType = (module.type || '').replace(/^tb4_/, '');

// Line 384: Switch uses slug WITHOUT prefix
switch (moduleType) {
    case 'text':
        return `<div>...</div>`;
    case 'image':
        return `<div>...</div>`;
    // ... more cases ...
    
    // ADD NEW CASES BEFORE default:
    case 'bar_counter':
        return `<div class="tb4-bar-counter-preview">...</div>`;
        
    default:
        return `<div class="tb4-unknown">Unknown module: ${moduleType}</div>`;
}
```

### Case Template
```javascript
case 'bar_counter':
    return `<div class="tb4-bar-counter-preview" style="padding:20px;">
        <div style="margin-bottom:12px;">
            <span style="font-size:14px;color:#374151;">${this.escapeHtml(data.title || 'Progress')}</span>
            <div style="height:8px;background:#e5e7eb;border-radius:4px;margin-top:4px;">
                <div style="width:${data.percent || 75}%;height:100%;background:#3b82f6;border-radius:4px;"></div>
            </div>
        </div>
    </div>`;
```

### CRITICAL RULES FOR STEP 4:
- ✅ Case uses slug **WITHOUT** `tb4_` prefix (e.g., `case 'bar_counter':`)
- ✅ Use `this.escapeHtml()` for user data
- ✅ Access data via `data.fieldname` (from content fields)
- ✅ Provide sensible defaults with `|| 'default'`
- ✅ Add case BEFORE the `default:` case

---

## SLUG NAMING CONVENTION

| Context | Format | Example |
|---------|--------|---------|
| PHP class $slug | `{slug}` | `bar_counter` |
| PHP filename | `{slug}module.php` | `barcountermodule.php` |
| init.php register | Class reference | `BarCounterModule::class` |
| edit.php data-module-type | `tb4_{slug}` | `tb4_bar_counter` |
| builder.js case | `{slug}` | `case 'bar_counter':` |
| CSS classes | `tb4-{slug}` | `.tb4-bar-counter` |

---

## PARENT-CHILD MODULES

For modules with children (Accordion, Tabs, Slider, Pricing):

### Parent Module
```php
class AccordionModule extends Module
{
    protected string $slug = 'accordion';
    protected string $type = 'parent';
    protected string $child_slug = 'accordion_item';
}
```

### Child Module
```php
class AccordionItemModule extends ChildModule
{
    protected string $slug = 'accordion_item';
    protected string $type = 'child';
    protected string $parent_slug = 'accordion';
}
```

### Both Need All 4 Steps
Parent AND child modules each need:
1. PHP class file
2. init.php registration
3. edit.php entry (child modules in same category as parent)
4. builder.js case

---

## TROUBLESHOOTING

### "Module not visible in panel"
→ Check STEP 3: Is HTML entry in edit.php?
→ Check: Is data-module-type="tb4_{slug}" correct?

### "Unknown module!" error
→ Check STEP 4: Is case in builder.js switch?
→ Check: Case uses slug WITHOUT tb4_ prefix?

### "Module visible but no settings"
→ Check STEP 1 & 2: Is PHP class registered?
→ Check: Class has get_content_fields() returning array?

### "Advanced fields missing"
→ Check STEP 1: Does get_advanced_fields() return $this->advanced_fields?
→ WRONG: Returning custom array loses base class fields!

---

## FILE QUICK REFERENCE

| Step | File | Line Reference |
|------|------|----------------|
| 1 | `/core/tb4/modules/{cat}/{slug}module.php` | New file |
| 2a | `/core/tb4/init.php` | ~35-60 (require_once) |
| 2b | `/core/tb4/init.php` | ~100-150 (register) |
| 3 | `/app/views/admin/tb4/edit.php` | 3262-3480 (HTML) |
| 4 | `/public/assets/tb4/js/builder.js` | 384+ (switch) |

---

**END OF GUIDE**
