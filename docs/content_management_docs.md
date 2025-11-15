# Content Management System Documentation

## Security

### CSRF Protection
The system implements CSRF protection using session-based tokens:

1. **Token Generation**:
   - Generated when session starts
   - Stored in `$_SESSION['csrf_token']`
   - Valid for 1 hour by default

2. **Form Protection**:
   - All state-changing forms must include:
     ```html
     <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
     ```

3. **Verification Process**:
   - Controllers verify tokens before processing requests:
     ```php
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
         return $this->forbidden();
     }
     ```
   - Returns 403 Forbidden on mismatch

4. **Framework-Free Verification**:
   - Manual verification in each controller action
   - No middleware dependencies
   - Simple session-based comparison
   - No external library requirements

## Implementation Results
- Successfully prevents CSRF attacks
- Minimal performance impact
- Easy to implement in new controllers
- No framework dependencies