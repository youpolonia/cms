# CMS Database Documentation

This directory contains information about the CMS database schema and related components.

## Schema
A detailed description of all database tables, columns, relationships, and indexes can be found in the [Database Schema Document](./schema.md). This document is generated based on an analysis of the database migration files.

## Migrations
Database migrations are located in the `database/migrations` directory. These scripts are responsible for creating and modifying the database schema over time. They are organized into phases and timestamped files.

## Key Considerations
- The schema has evolved, and some older migrations or alternative table definitions might exist. The `schema.md` attempts to represent the most current, consolidated view.
- Some inconsistencies, such as data type mismatches in foreign key relationships, have been noted within the `schema.md` document. These should be reviewed for potential database integrity issues.
- The system uses a mix of raw SQL, a custom `Migration` base class, and a schema builder pattern for defining migrations.