# CMS Content Model Architecture

This document outlines the initial architecture for managing content within the CMS.

## Core Concepts

The content model is designed to be flexible and extensible, revolving around a few key entities:

1.  **`ContentTypes`**: Defines the blueprint for different kinds of content (e.g., "Page", "Blog Post", "Product"). Each content type can have its own set of characteristics and custom fields.
    *   **Database Table**: `content_types`
    *   **Model**: `Models\ContentType`
    *   **Key Attributes**:
        *   `name`: Human-readable name (e.g., "Blog Post").
        *   `slug`: URL-friendly identifier (e.g., "blog-post").
        *   `description`: Optional description.
        *   `is_hierarchical`: Boolean, indicates if items of this type can have parent/child relationships (e.g., pages).
        *   `has_tags`: Boolean, indicates if items can be tagged.
        *   `has_categories`: Boolean, indicates if items can be categorized.

2.  **`ContentItems`**: Represents individual pieces of content (e.g., a specific blog post, an "About Us" page). Each content item belongs to a `ContentType`.
    *   **Database Table**: `content_items`
    *   **Model**: `Models\ContentItem`
    *   **Key Attributes**:
        *   `content_type_id`: Foreign key to `content_types`.
        *   `author_id`: Foreign key to `users` (who created it).
        *   `parent_id`: For hierarchical content, foreign key to another `content_items` record.
        *   `title`: The main title of the content.
        *   `slug`: URL-friendly version of the title, unique within its content type.
        *   `content_body`: The main body of the content (e.g., HTML, Markdown).
        *   `excerpt`: A short summary.
        *   `status`: Publication status (e.g., "draft", "published", "archived").
        *   `visibility`: Access control (e.g., "public", "private", "password_protected").
        *   `password`: If visibility is password protected.
        *   `published_at`: Timestamp when the content was/is to be published.

3.  **`Categories`**: Hierarchical terms to classify content items.
    *   **Database Table**: `categories`
    *   **Model**: (To be created, e.g., `Models\Category`)
    *   **Relationship Table**: `content_item_categories`

4.  **`Tags`**: Non-hierarchical keywords to classify content items.
    *   **Database Table**: `tags`
    *   **Model**: (To be created, e.g., `Models\Tag`)
    *   **Relationship Table**: `content_item_tags`

5.  **`CustomFields`**: Allows defining additional data fields specific to a `ContentType` (e.g., a "Price" field for a "Product" content type).
    *   **Database Table**: `custom_fields`
    *   **Model**: (To be created, e.g., `Models\CustomField`)
    *   **Key Attributes**:
        *   `content_type_id`: Which content type this field belongs to.
        *   `field_name`: Machine-readable name (e.g., "product_price").
        *   `field_label`: Human-readable label (e.g., "Product Price").
        *   `field_type`: Type of input (e.g., "text", "textarea", "number", "image_id").
        *   `options`: For types like select/radio (e.g., JSON array).
        *   `is_required`: Boolean.

6.  **`CustomFieldValues`**: Stores the actual values for custom fields for each `ContentItem`.
    *   **Database Table**: `custom_field_values`
    *   **Model**: (To be created, e.g., `Models\CustomFieldValue`)

## Relationships

*   A `ContentItem` belongs to one `ContentType`.
*   A `ContentItem` belongs to one `User` (author).
*   `ContentItems` can have many `Categories` (via `content_item_categories`).
*   `ContentItems` can have many `Tags` (via `content_item_tags`).
*   `CustomFields` belong to one `ContentType`.
*   `CustomFieldValues` belong to one `ContentItem` and one `CustomField`.

## Model Implementation

Basic PHP classes (`Models\ContentType`, `Models\ContentItem`, `Models\User`) have been created to represent these entities. These models currently provide:
*   Public properties corresponding to table columns.
*   A constructor accepting a `PDO` instance.
*   Static `findById` and `findBySlug` (or `findByUsername`) methods for retrieval.
*   A private `populate` method to map database data to object properties.

Future enhancements will include:
*   Models for `Category`, `Tag`, `CustomField`, `CustomFieldValue`.
*   Methods for saving (create/update) and deleting records.
*   Methods for retrieving related data (e.g., getting all tags for a content item).
*   More sophisticated query methods.

This architecture provides a solid, extensible foundation for managing diverse content within the CMS without relying on external frameworks.