# Documentation Templates

This directory will store templates for various documentation types to ensure consistency.

## Available Templates

- **API Endpoint Template**: A standard format for documenting individual API endpoints.
  ```markdown
  ### `[HTTP_METHOD] /path/to/endpoint`

  **Description**: Brief overview of the endpoint's purpose.

  **Parameters**:
  - `param_name` (type, required/optional): Description.
  - ...

  **Request Body Example** (if applicable):
  ```json
  {
    "key": "value"
  }
  ```

  **Response Success (200 OK)**:
  ```json
  {
    "data": "...",
    "message": "Success"
  }
  ```

  **Response Error (e.g., 400 Bad Request, 404 Not Found)**:
  ```json
  {
    "error": "Error message",
    "code": "ERROR_CODE"
  }
  ```
  ---
  ```

- **Database Table Template**: A structure for documenting database tables.
  ```markdown
  ### Table: `table_name`

  **Description**: Purpose of the table.

  | Column Name | Data Type | Constraints       | Description          |
  |-------------|-----------|-------------------|----------------------|
  | `id`        | `INT`     | `PRIMARY KEY, AI` | Unique identifier    |
  | `column_2`  | `VARCHAR` | `NOT NULL`        | Description of col 2 |
  | ...         | ...       | ...               | ...                  |

  **Indexes**:
  - `index_name` (column_list)

  **Relationships**:
  - Relates to `other_table` via `foreign_key_column`.
  ```

- **Feature Documentation Template**: A general template for documenting new features or modules.

*These templates will be refined and expanded as needed.*