# Security Standards Configuration

## File Permissions
- Directories should not be writable (755)
- Configuration files should not be world-readable (640)
- Executable files should be owner-only (700)

## Sensitive Files
- .env files should never be committed
- backup_*.sql files should not exist in webroot
- composer.json should not contain dev dependencies in production

## Database Security
- All tables should have prefix
- Password fields must be encrypted
- API keys should not be stored in plaintext

## Authentication
- Minimum password length: 12 characters
- Password complexity requirements
- Session timeout: 30 minutes

## API Security
- Rate limiting required
- Input validation mandatory
- Error messages should not reveal system details