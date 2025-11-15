# Mode Management Documentation

## Overview
This document defines the mode management system architecture, configuration, and operational guidelines.

## Mode Assignment Rules
The system supports the following modes (defined in [`config/modes.json`](./config/modes.json)):
- architect
- code
- debug
- documents
- orchestrator
- db-support
- pattern-reader
- ask

Modes follow a hierarchical structure where:
1. Core modes (code, architect, documents) have highest priority
2. Specialized modes (db-support, pattern-reader) handle specific tasks
3. Fallback modes provide default behavior

## Configuration File Structure
The primary configuration file is [`config/modes.json`](./config/modes.json) with this structure:
```json
{
    "modes": [
        "mode1",
        "mode2"
    ]
}
```

## Provider Mapping Guidelines
Providers are configured in [`config/providers.json`](./config/providers.json) with these requirements:
- Each provider must specify:
  - `type`: Provider technology (e.g., "api")
  - `baseUrl`: Endpoint URL
  - `model`: Model identifier
  - `assignedModes`: Array of modes this provider handles

Example:
```json
{
  "api-provider": {
    "type": "api",
    "baseUrl": "http://api.example.com",
    "model": "default",
    "assignedModes": ["code", "architect", "documents"]
  }
}
```

## Fallback Behavior
When no provider is assigned to a mode:
1. System attempts to use default provider
2. If unavailable, falls back to most similar mode:
   - debug → code
   - db-support → code
   - pattern-reader → debug
3. Final fallback is "ask" mode

## Version Control Requirements
1. All mode configuration changes must be:
   - Committed with descriptive messages
   - Version tagged following semver
   - Documented in changelog
2. Required version metadata:
   - `config/versions.php` must be updated
   - Migration files must be created for schema changes

## Examples

### Basic Mode Configuration
```json
{
    "modes": ["code", "debug"]
}
```

### Provider Assignment
```json
{
  "provider1": {
    "type": "api",
    "assignedModes": ["code"],
    "model": "default"
  }
}
```

### Fallback Configuration
```json
{
  "fallbacks": {
    "debug": "code",
    "db-support": "code"
  }
}