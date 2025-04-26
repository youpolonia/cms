# Database Schema Documentation

## Core Content Tables

### contents
- id (integer, primary key)
- title (string)
- slug (string)
- content (text)
- status (string)
- created_at (timestamp)
- updated_at (timestamp)
- published_at (timestamp, nullable)
- created_by (integer, foreign key to users)
- current_version_id (integer, foreign key to content_versions)

### content_versions
- id (integer, primary key)
- content_id (integer, foreign key to contents)
- version_number (integer)
- title (string)
- content (text)
- created_at (timestamp)
- updated_at (timestamp)
- is_autosave (boolean)
- status (string)
- restore_count (integer)

### content_schedules
- id (integer, primary key)
- content_id (integer, foreign key to contents)
- version_id (integer, foreign key to content_versions)
- publish_at (datetime)
- unpublish_at (datetime, nullable)
- timezone (string)
- status (string)
- metadata (text, nullable)
- created_by (integer, foreign key to users)

## Category System

### categories
- id (integer, primary key)
- name (string)
- slug (string)
- description (text, nullable)
- parent_id (integer, nullable, self-referential)
- seo_title (string, nullable)
- seo_description (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)

### category_content
- id (integer, primary key)
- category_id (integer, foreign key to categories)
- content_id (integer, foreign key to contents)
- created_at (timestamp)
- updated_at (timestamp)

## Analytics Tables

### content_user_views
- id (integer, primary key)
- content_id (integer, foreign key to contents)
- user_id (integer, foreign key to users)
- view_count (integer)
- last_viewed_at (timestamp)

### analytics_exports
- id (integer, primary key)
- type (string)
- parameters (json)
- status (string)
- file_path (string, nullable)
- created_at (timestamp)
- updated_at (timestamp)

## Moderation System

### moderation_queue
- id (integer, primary key)
- content_version_id (integer, foreign key to content_versions)
- status (string)
- notes (text, nullable)
- moderated_by (integer, foreign key to users)
- created_at (timestamp)
- updated_at (timestamp)

## Relationships
- contents hasMany content_versions
- content_versions belongsTo contents
- contents belongsToMany categories through category_content
- categories belongsToMany contents through category_content
- content_schedules belongsTo contents
- content_schedules belongsTo content_versions
- content_user_views belongsTo contents
- content_user_views belongsTo users
- moderation_queue belongsTo content_versions
- moderation_queue belongsTo users (as moderator)