# CLAUDE CODE BRIEF - INNER ELEMENT DESIGN SYSTEM FIX

## PROBLEM
Modal Inner Element Design NOT working. User changes opacity/filters → NO effect on Live Preview.

## OBJECTIVE
Fix so changes in modal → immediately update Live Preview → save to canvas.

## KEY FILES
1. /var/www/html/cms/core/theme-builder/js/tb-modal-editor.js (2,195 lines)
2. /var/www/html/cms/core/theme-builder/js/tb-modal-element-design-*.js (4 files)  
3. /var/www/html/cms/core/theme-builder/js/tb-modules-preview.js (699 lines)

## SUSPECTS
- Modal saves to mod.design.element_styles BUT preview doesn't read from there
- TB.updateModalPreview() doesn't call TB.renderModulePreview()
- TB.renderModulePreview() ignores element_styles

## CONSTRAINTS
- Pure PHP 8.1+, NO frameworks, NO CLI
- sudo password: jaskolki
- After changes: chmod 644, chown www-data:www-data
- Cache bust: v=20260104f → v=20260104g in template-edit.php and edit.php

## SUCCESS CRITERIA
User changes Opacity 100→50 in modal → Live Preview shows 50% opacity IMMEDIATELY → Save → canvas persists 50%

GOOD LUCK!
