# DIVI-STYLE MIGRATION PLAN
## Data: 2025-01-02
## Status: W TRAKCIE - Etap 1 zakończony

---

## KONTEKST DECYZJI

User (Piotr) i Claude zdecydowali o migracji do architektury Divi-style:
- **Blank Canvas** = jedyna theme używająca TB Site Templates (header/footer z Theme Builder)
- **Default/Jessie** = tradycyjne PHP themes z własnym header/footer (fallback)
- **Layout Library full-site** = musi zawierać header + footer + pages (nie tylko pages)
- **Import full-site** = musi tworzyć tb_site_templates z właściwymi conditions

---

## PROBLEM KTÓRY ROZWIĄZUJEMY

Przed migracją default theme używała TB templates co powodowało mix różnych header/footer.
Po migracji: Default theme = fallback only, Blank Canvas = pełna kontrola TB.

---

## STAN ETAPÓW

### ✅ ETAP 1: Separacja Themes (DONE)
- Zmodyfikowano /themes/default/layout.php
- Usunięto wywołania tb_render_site_template()
- Default theme używa TYLKO fallback header/footer

### 🔲 ETAP 2: Rozszerzenie Layout Library (TODO)
Dodać header/footer do struktury full-site w Layout Library

### 🔲 ETAP 3: Modyfikacja Import (TODO)
LayoutLibraryController::import() - tworzyć tb_site_templates przy imporcie

### 🔲 ETAP 4: Migracja istniejących presetów (TODO)
Dodać header/footer do Edis Paving i Golden Plate

### 🔲 ETAP 5: Przełączenie na Blank Canvas (TODO)
Ustawić active_theme = blank i przetestować

---

## KLUCZOWE PLIKI

- /themes/default/layout.php - DONE (nie używa TB templates)
- /themes/blank/layout.php - używa TB templates (bez zmian)
- /core/theme-builder/database.php - tb_get_active_template()
- /app/controllers/admin/layoutlibrarycontroller.php - TODO: import z header/footer

---

## STAN BAZY

tb_site_templates:
- ID 6: Edis Paving Header (priority 1, conditions: specific pages)
- ID 2: AURORA Header (priority 0, conditions: all)
- ID 5: Edis Paving Footer (priority 12, conditions: all)
- ID 4: Golden Plate Footer (priority 10)

system_settings.active_theme = default
