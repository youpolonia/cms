# Project Brief

This project is a custom-built CMS in clean PHP without any frameworks. It must be modular, portable, and fully deployable via FTP without CLI. 

## Core Requirements:
- Remove all Laravel traces
- Replace framework dependencies with native, independent PHP logic
- No Composer, migrations, or Artisan tools allowed
- All functionality must be built from scratch
- Maintain strict FTP-deployable architecture
- Follow PHP 8.1+ standards
- Implement modular architecture using require_once

## Technical Constraints:
- No framework-based syntax (Laravel, Symfony, etc.)
- No CLI dependencies
- No vendor/autoload.php
- No database migrations
- No Artisan commands
- No Blade templates
- No Eloquent ORM

## Implementation Guidelines:
- Use plain PHP for all components
- Implement custom routing if needed
- Develop standalone database layer
- Create modular component architecture
- Ensure all code is framework-agnostic