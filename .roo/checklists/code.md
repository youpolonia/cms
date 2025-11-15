# Code Quality Checklist

## PSR-12 Compliance
- [ ] Opening `<?php` tag is on its own line
- [ ] Classes have one blank line before and after
- [ ] Methods have one blank line between them  
- [ ] All keywords are lowercase (true, false, null)
- [ ] Lines are <= 120 characters
- [ ] Indentation uses 4 spaces (no tabs)
- [ ] Opening braces for classes/methods on same line
- [ ] Closing braces for classes/methods on new line
- [ ] No space after opening parenthesis `(` and before closing parenthesis `)`
- [ ] One space after control structure keywords (if, for, foreach, etc.)
- [ ] Type hints used for all method parameters and return types
- [ ] Strict types declaration `declare(strict_types=1);` present
- [ ] Namespaces and use statements follow PSR-12
- [ ] Class constants in uppercase with underscores

## Security Best Practices
- [ ] All user input is validated/sanitized
- [ ] No raw SQL queries - uses prepared statements
- [ ] CSRF protection enabled for forms
- [ ] Password hashing uses current algorithms (bcrypt, argon2)
- [ ] Sensitive data not logged (passwords, tokens)
- [ ] Authentication checks on all protected routes
- [ ] Authorization checks for all sensitive actions
- [ ] No direct file inclusion from user input
- [ ] Headers set properly (XSS, CSP, HSTS)
- [ ] Session security settings configured (httponly, secure)
- [ ] API endpoints have rate limiting
- [ ] Error messages don't reveal system details
- [ ] Dependencies are up-to-date with no known vulnerabilities

## No Dead Code
- [ ] No unused imports/use statements
- [ ] No unused private methods/properties
- [ ] No unreachable code (after return/throw)
- [ ] No commented-out code blocks
- [ ] No unused variables
- [ ] No duplicate code that could be extracted
- [ ] No deprecated functions/methods
- [ ] All defined functions/methods are called
- [ ] No empty catch blocks swallowing exceptions

## Proper Naming Conventions
### PHP
- [ ] Classes use PascalCase (e.g. `UserController`)
- [ ] Methods use camelCase (e.g. `getUserData`)
- [ ] Variables use camelCase (e.g. `$userCount`)
- [ ] Constants use UPPER_SNAKE_CASE (e.g. `MAX_USERS`)
- [ ] Interfaces end with `Interface` (e.g. `LoggerInterface`)
- [ ] Traits end with `Trait` (e.g. `LoggableTrait`)
- [ ] Abstract classes start with `Abstract` (e.g. `AbstractModel`)
- [ ] Boolean variables/methods indicate state (e.g. `isValid`, `hasPermission`)

### JavaScript
- [ ] Classes use PascalCase
- [ ] Functions/variables use camelCase  
- [ ] Constants use UPPER_SNAKE_CASE
- [ ] Private members prefixed with `_` (e.g. `_internalMethod`)
- [ ] Boolean variables start with `is`, `has`, `can` (e.g. `isLoading`)
- [ ] Event handlers start with `handle` (e.g. `handleClick`)
- [ ] Async functions end with `Async` (e.g. `fetchDataAsync`)

### General
- [ ] Names are descriptive but concise
- [ ] No abbreviations unless widely known
- [ ] No single-letter variables except in loops
- [ ] Names reflect purpose/functionality
- [ ] No misleading names
- [ ] Consistent naming across codebase