# AI THEME BUILDER 4.0 - FULL REBUILD TASK

## CONTEXT

You are rebuilding the AI Theme Builder module for Jessie AI-CMS. The current implementation is broken and fragmented across multiple files.

## MANDATORY CONSTRAINTS

- NO CLI (system/exec/shell_exec FORBIDDEN)
- Pure PHP 8.1+
- FTP-only deployment
- require_once only (no include)
- No closing ?> tag
- All filenames lowercase

## CURRENT STATE

### Files to analyze:
1. `/var/www/html/cms/admin/ai-theme-builder.php` - LEGACY file (1588 lines) - DELETE after migration
2. `/var/www/html/cms/app/controllers/admin/aithemebuildercontroller.php` - MVC controller (1904 lines)
3. `/var/www/html/cms/app/controllers/admin/ailayoutcomposercontroller.php` - Layout generation (1416 lines)
4. `/var/www/html/cms/app/views/admin/ai-theme-builder/` - Views directory
5. `/var/www/html/cms/app/views/admin/ai-layout-composer/` - Layout composer views
6. `/var/www/html/cms/core/theme-builder/init.php` - TB 3.0 module definitions (1569 lines)
7. `/var/www/html/cms/core/theme-builder/renderer.php` - TB renderer

### Routes (in /var/www/html/cms/config/routes.php):
The MVC route exists but legacy file may be loaded instead:
- GET /admin/ai-theme-builder => Admin\AiThemeBuilderController::index
- GET /admin/ai-layout-composer => Admin\AILayoutComposerController::index

## REQUIREMENTS

### 1. MERGE FUNCTIONALITY

Combine AI Theme Builder and AI Layout Composer into single unified module:
- URL: /admin/ai-theme-builder 
- Controller: AiThemeBuilderController

### 2. MULTI-STEP WIZARD UI

Step 1: Business Brief
- Business type dropdown (restaurant, business, portfolio, ecommerce, agency, medical, education, real-estate)
- Style preference (modern, minimal, corporate, creative, elegant, bold)
- Color scheme (dark/light + custom palette picker)
- Industry description textarea
- Number of pages slider (1-10)

Step 2: AI Generation
- Display "Generating..." with progress
- Generate SEPARATE layouts for:
  - HEADER - Navigation, logo, CTAs
  - PAGES - Each page as complete TB layout
  - FOOTER - Links, contact, social, copyright

Step 3: Preview & Customize
- Live preview using TB renderer
- Tabbed interface: Header | Pages | Footer
- Color palette editor
- Font selector
- Spacing controls

Step 4: Save & Deploy
- Save to Layout Library (tb_layout_library table)
- Import as TB templates
- Set active header/footer
- Create pages in pages table

### 3. AI PROMPT ENGINEERING

The AI prompts must generate PROFESSIONAL, MODERN designs comparable to Divi/Elementor templates.

Key principles:
- Section backgrounds should ALTERNATE (dark/light/accent)
- Typography must be LARGE and impactful (H1: 48-64px, H2: 36-42px)
- Buttons need proper shadows and hover states
- Cards need border-radius (12-20px) and subtle shadows
- Icons should use FontAwesome classes (fas fa-*)
- Images should have descriptive keywords for later Pexels/Unsplash fill

### 4. TB 3.0 COMPATIBILITY

Every generated module MUST match TB 3.0 module definitions exactly.

Available modules (from init.php):
- text, heading, image, button, divider, spacer
- video, audio, code, quote, list, icon
- map, accordion, toggle, tabs
- gallery, testimonial, cta, pricing
- blurb, hero, slider, team
- counter, progress-bar, form, contact-form
- newsletter, social-icons

JSON structure for TB pages:
{
  "sections": [
    {
      "id": "section_001",
      "name": "Hero",
      "design": {
        "background_color": "#1a1a2e",
        "padding_top": "120px",
        "padding_bottom": "120px"
      },
      "rows": [
        {
          "id": "row_001",
          "columns": [
            {
              "id": "col_001",
              "width": "100%",
              "modules": [
                {
                  "id": "mod_001",
                  "type": "heading",
                  "content": {"text": "Welcome", "level": "h1"},
                  "design": {"text_color": "#ffffff", "font_size": "56px"}
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}

### 5. HEADER/FOOTER TEMPLATES

Header and footer should be saved to tb_templates table with type='header' or type='footer'.

### 6. FILES TO CREATE/MODIFY

1. MODIFY: /app/controllers/admin/aithemebuildercontroller.php
   - Merge functionality from ailayoutcomposercontroller.php
   - Add header/footer generation
   - Improve AI prompts

2. MODIFY: /app/views/admin/ai-theme-builder/index.php
   - New 4-step wizard UI
   - Professional Catppuccin dark theme styling

3. DELETE: /admin/ai-theme-builder.php (legacy file)
   - After confirming MVC works

### 7. UI DESIGN REQUIREMENTS

Use Catppuccin dark theme with professional wizard, progress steps indicator, smooth animations, loading states, toast notifications, responsive design.

## SUCCESS CRITERIA

- Single unified AI Theme Builder module
- 4-step wizard UI working
- AI generates professional-quality designs
- Header layout generated separately
- Footer layout generated separately  
- Page layouts generated for TB 3.0
- All modules 100% TB 3.0 compatible
- Preview renders correctly via TB renderer
- Save to Layout Library works
- Import as TB templates works
- Legacy file removed
- No PHP errors
- No JS console errors

## START

