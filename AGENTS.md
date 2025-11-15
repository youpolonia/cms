# Agent Specifications

## Code Agent
- Must remove all Laravel-related code including:
  - Schema
  - Eloquent
  - Blade
  - Artisan
  - route()
  - env()
  - helpers
  - config()
  - migrations
  - autoload
  - Any framework usage
- Must refactor code into modular, standalone PHP using only `require_once`
- Must avoid all Composer/vendor dependencies
- Code must be:
  - FTP-ready
  - Framework-free
  - CLI-free
  - No Laravel components whatsoever

## Debug Agent
- Responsible for detecting all remaining traces of Laravel usage
- Verifies post-refactor safety, syntax, and Laravel-free compliance

## Architect Agent
- Designs modular PHP structure
- Recommends clean file architecture to replace removed Laravel components
- Ensures code structure remains extensible without frameworks