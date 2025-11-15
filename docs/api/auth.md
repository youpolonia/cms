# Authentication API Documentation

This document outlines the API endpoints available for user authentication and session management within the CMS.

## Base URL

All authentication-related API endpoints are typically prefixed. Assuming the API is hosted at `/api.php`, the endpoints would be structured like `/api.php?action=auth_login`. The exact routing mechanism might vary based on the CMS setup.

## Security

- All API endpoints that modify state or return sensitive information should be accessed over **HTTPS**.
- Session cookies are configured with `HttpOnly`, `Secure` (if HTTPS is enabled), and `SameSite=Lax` attributes for enhanced security.
- Session IDs are regenerated upon login to prevent session fixation.
- Basic session hijacking prevention is in place by checking `User-Agent` and session expiry.

## Error Codes

Standard HTTP status codes are used. Common ones include:

- `200 OK`: Request successful.
- `400 Bad Request`: Missing or invalid parameters.
- `401 Unauthorized`: Authentication failed or user not logged in.
- `403 Forbidden`: User is authenticated but does not have permission.
- `404 Not Found`: Endpoint or resource not found.
- `500 Internal Server Error`: Server-side error.

Specific error messages or codes might be included in the JSON response body, e.g., `{"success": false, "error": "Invalid credentials"}`.

---

## Endpoints

### 1. Login

- **URL:** `/api.php?action=auth_login` (Example, actual URL might differ based on routing)
- **Method:** `POST`
- **Description:** Authenticates a user and establishes a session.
- **Parameters:**
    - `usernameOrEmail` (string, required): The user's username or email address.
    - `password` (string, required): The user's plain-text password.
- **Responses:**
    - **Success (200 OK):**
        ```json
        {
            "success": true,
            "message": "Login successful.",
            "data": {
                "user_id": 123,
                "username": "john_doe"
            }
        }
        ```
    - **Failure (401 Unauthorized):**
        ```json
        {
            "success": false,
            "error": "Invalid credentials."
        }
        ```
    - **Failure (400 Bad Request):**
        ```json
        {
            "success": false,
            "error": "Username and password are required."
        }
        ```
- **Example Request (form-data or x-www-form-urlencoded):**
    ```
    usernameOrEmail=john_doe
    password=securepassword123
    ```
- **Security:**
    - Must be called over HTTPS.
    - Input should be sanitized.
    - Rate limiting should be implemented to prevent brute-force attacks.

### 2. Logout

- **URL:** `/api.php?action=auth_logout` (Example, actual URL might differ based on routing)
- **Method:** `POST` (or `GET`, depending on implementation preference, `POST` is safer against CSRF if not using tokens)
- **Description:** Logs out the currently authenticated user by destroying their session.
- **Parameters:** None
- **Responses:**
    - **Success (200 OK):**
        ```json
        {
            "success": true,
            "message": "Logout successful."
        }
        ```
    - **Failure (401 Unauthorized - if trying to logout without active session, though usually it just clears):**
        ```json
        {
            "success": false,
            "error": "Not logged in."
        }
        ```
- **Example Request:**
    A simple POST request to the endpoint.
- **Security:**
    - CSRF protection should be in place if using GET or if session cookies are the only auth mechanism.
    - Should invalidate the server-side session and clear session cookies.

### 3. Check Login Status

- **URL:** `/api.php?action=auth_status` (Example, actual URL might differ based on routing)
- **Method:** `GET`
- **Description:** Checks if the current user is logged in and their session is valid.
- **Parameters:** None
- **Responses:**
    - **Success (200 OK - Logged In):**
        ```json
        {
            "success": true,
            "loggedIn": true,
            "data": {
                "user_id": 123,
                "username": "john_doe"
            }
        }
        ```
    - **Success (200 OK - Not Logged In):**
        ```json
        {
            "success": true,
            "loggedIn": false
        }
        ```
    - **Failure (500 Internal Server Error - if session check fails unexpectedly):**
        ```json
        {
            "success": false,
            "error": "Session check failed."
        }
        ```
- **Example Request:**
    A simple GET request to the endpoint.
- **Security:**
    - This endpoint reveals authentication status, ensure it's used appropriately.

---

*This documentation assumes that API requests are routed through a central `api.php` file with an `action` parameter. The actual implementation might use a different routing mechanism (e.g., URL rewriting with `.htaccess`). The `AuthController` methods (`login`, `logout`, `isLoggedIn`, `getCurrentUserId`, `getCurrentUsername`) provide the core logic for these operations.*