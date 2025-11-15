# Content Types Database Schema

## content_types Table
Stores content type definitions.

**Columns:**
- `id` (primary key) - Auto-incrementing ID
- `machine_name` (string) - Unique machine-readable identifier
- (additional columns inferred from SELECT * queries)

## content_fields Table
Stores field definitions for content types.

**Columns:**
- `id` (primary key) - Auto-incrementing ID
- `content_type_id` (foreign key) - References content_types.id
- `name` (string) - Human-readable field name
- `machine_name` (string) - Machine-readable identifier
- `field_type` (string) - Field type identifier
- `settings` (text/JSON) - Field configuration
- `is_required` (boolean) - Whether field is required
- `weight` (integer) - Display ordering

**Relationships:**
- One content_type has many content_fields
- Each content_field belongs to one content_type

**Indexes:**
- content_type_id (for getFieldsForType queries)
- weight (for ordering)