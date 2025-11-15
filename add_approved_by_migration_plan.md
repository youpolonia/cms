# Plan: Add approved_by column to content_versions table

## Problem
The `2025_04_23_030000_add_moderation_fields_to_content_versions_table.php` migration fails because the `approved_by` column does not exist in the `content_versions` table.

## Solution
Create a new migration to add the `approved_by` column to the `content_versions` table.

## Steps
1.  **Create a new migration file:** `2025_04_23_020000_add_approved_by_to_content_versions_table.php` in the `database/migrations` directory.
2.  **Add the `approved_by` column:** In the `up()` method of the migration, add a `foreignId('approved_by')->nullable()->constrained('users')` to the `content_versions` table.
3.  **Remove the `approved_by` column:** In the `down()` method of the migration, drop the `approved_by` column from the `content_versions` table.
4.  **Switch back to code mode:** After creating the migration, switch back to code mode to run the migrations.
5.  **Run the migrations:** Execute the `php artisan migrate` command.
6.  **Attempt completion:** If the migrations run successfully, attempt completion with a success message.

## Mermaid Diagram
```mermaid
graph TD
    A[Start] --> B{Create migration file: 2025_04_23_020000_add_approved_by_to_content_versions_table.php};
    B --> C{Add approved_by column to content_versions table in up() method};
    C --> D{Remove approved_by column from content_versions table in down() method};
    D --> E{Switch back to code mode};
    E --> F{Run php artisan migrate};
    F --> G{Attempt completion with success message};
    G --> H[End];