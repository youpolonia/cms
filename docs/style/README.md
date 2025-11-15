# Code Style Guidelines

This document outlines the coding standards and conventions to be followed for the CMS project. Adhering to these guidelines ensures consistency, readability, and maintainability of the codebase.

## PHP Coding Standards

- **PSR Compliance**: Primarily follow [PSR-12 (Extended Coding Style)](https://www.php-fig.org/psr/psr-12/) and [PSR-1 (Basic Coding Standard)](https://www.php-fig.org/psr/psr-1/).
- **Naming Conventions**:
    - Classes: `PascalCase`
    - Methods: `camelCase`
    - Functions: `snake_case` (for global helper functions if any)
    - Variables: `camelCase`
    - Constants: `UPPER_CASE_SNAKE_CASE`
- **Comments**:
    - PHPDoc blocks for all classes, methods, and functions.
    - Inline comments for complex logic.
- **Error Handling**:
    - Use exceptions for error handling where appropriate.
    - Consistent error reporting and logging.

## File and Folder Naming

- **Directories**: `kebab-case` or `snake_case` (lowercase).
- **PHP Files**: `PascalCase.php` for classes, `snake_case.php` for procedural scripts or templates if not class-based.
- **Other Assets (JS, CSS, Images)**: `kebab-case` (e.g., `main-style.css`, `user-profile.js`).

## Architectural Patterns

- Document key architectural decisions and patterns used (e.g., MVC-like structure, service layers, helper classes).
- Guidelines for modularity and separation of concerns.

## Linting

- Details on any linting tools or configurations (if applicable, though CLI tools are restricted). Manual review against these guidelines is key.

*This document will be expanded as the project evolves.*