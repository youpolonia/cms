# Contributing to the CMS Project

Thank you for your interest in contributing! To ensure consistency and maintainability, please adhere to the following development standards.

## 1. PHP Coding Standards

*   **PSR-12**: All PHP code MUST adhere to the [PSR-12 Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/).
*   **Strict Types**: Use `declare(strict_types=1);` at the beginning of all PHP files.
*   **Type Hinting**: Use type hinting for function arguments, return types, and class properties where appropriate (PHP 7.4+ features are available, project targets PHP 8.1+).
*   **Comments**: Write clear and concise comments for complex logic, public methods, and classes. Use PHPDoc blocks for classes, methods, and functions.

## 2. File Naming Conventions

*   **PHP Classes**: PascalCase (e.g., `MyClassName.php`).
*   **Other PHP Files (scripts, templates)**: snake_case (e.g., `my_script.php`, `user_profile_template.php`).
*   **Directories**: snake_case or kebab-case (e.g., `auth_services`, `api-handlers`). Prefer consistency within a module/area.
*   **Configuration Files**: snake_case (e.g., `database.php`, `core.json`).
*   **Migration Files**: `YYYYMMDDHHMMSS_descriptive_name.php` or a sequential prefix like `0001_descriptive_name.php`. The project currently uses the latter.

## 3. Basic Git Workflow

*   **Branching**:
    *   Create a new feature branch from the `main` (or `develop` if used) branch for each new feature or bugfix (e.g., `feature/user-authentication`, `fix/login-csrf-issue`).
    *   Do not commit directly to `main`.
*   **Commits**:
    *   Write clear, concise commit messages. Start with a capital letter and use the imperative mood (e.g., "Add user login functionality", "Fix CSRF token validation").
    *   Reference issue numbers if applicable (e.g., "Fix #123: Resolve login CSRF issue").
    *   Commit small, logical changes frequently.
*   **Pull Requests (if applicable)**:
    *   If working in a team environment with code reviews, submit pull requests for your feature branches to be merged into `main`.
    *   Ensure your branch is up-to-date with `main` before submitting a PR.

## 4. Error Reporting and Debugging

*   **Development Environment**:
    *   Ensure `display_errors` is enabled in your local PHP configuration or set via `core/bootstrap.php` for development.
    *   Utilize the `Core\Logger` for logging application events, warnings, and errors. Example:
        ```php
        // Assuming $logger is an instance of Core\Logger
        $logger->info('User logged in.', ['user_id' => $userId]);
        $logger->error('Database connection failed.', ['dsn' => $dsn]);
        ```
*   **Production Environment**:
    *   `display_errors` MUST be disabled.
    *   `log_errors` MUST be enabled, and errors should be written to a file (handled by `Core\Logger` and `core/bootstrap.php`).

## 5. Dependencies and Tooling

*   **No External CLI Dependencies**: This project is designed for shared hosting environments and AVOIDS tools requiring CLI installation like Composer, Node.js, npm, Yarn, etc.
*   **Framework-Free**: All PHP code should be vanilla PHP, following modular design principles. Do not introduce external frameworks.
*   **Database Migrations**: Use the provided pure PHP migration system. Create new migration files in the `database/migrations/` subdirectories.
*   **Autoloading**: The project uses a custom `Core\ModuleAutoloader` and a basic PSR-4 like structure for namespaces like `Models`, `Auth\Services`, etc. Ensure your new classes follow this.

## 6. Documentation

*   **Code Comments**: As mentioned, PHPDoc blocks are essential.
*   **Markdown Files**: For architectural decisions, guides (like this one), or complex feature explanations, create or update Markdown files in the `docs/` directory.

By following these standards, we can maintain a clean, understandable, and maintainable codebase.