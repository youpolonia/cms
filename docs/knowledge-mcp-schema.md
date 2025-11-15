# Knowledge MCP Database Schema Documentation

## Overview
This schema supports the Knowledge MCP integration with:
- Hierarchical documentation system
- Help center content management
- Full-text search capabilities
- Version control and revision history
- Role-based permissions

## Core Tables

### Knowledge Documentation
- `knowledge_docs`: Main documentation articles with parent-child relationships
- `knowledge_doc_versions`: Version history for documentation changes

### Help Center
- `help_categories`: Hierarchical organization of help content
- `help_articles`: Individual help articles with categorization
- `help_article_permissions`: Role-based access control for articles

### Search Functionality
- `search_index`: Unified search index across content types
- `search_analytics`: Search term tracking and analytics
- `user_search_history`: Personalized search history
- `popular_content`: Content popularity metrics

## Index Optimization Recommendations

1. **Primary Indexes**:
   - All primary keys are automatically indexed
   - Foreign keys are indexed for join performance

2. **Full-text Search**:
   - `help_articles`: Full-text index on title+content
   - Consider adding similar index to `knowledge_docs`

3. **Composite Indexes**:
   - Add composite index on `search_index(searchable_type, searchable_id)`
   - Add composite index on `popular_content(content_type, content_id)`

4. **Query Optimization**:
   - For hierarchical queries, consider using a closure table pattern if depth > 3 levels
   - Add index on `knowledge_docs(parent_id)` for child lookups
   - Add index on `help_categories(parent_id)` for child lookups

5. **Analytics Optimization**:
   - Add index on `search_analytics(term)` for term frequency analysis
   - Add index on `user_search_history(user_id, created_at)` for user history

## Performance Considerations

1. **Search Performance**:
   - Monitor search query performance
   - Consider dedicated search engine (Elasticsearch) if search volume is high

2. **Version Storage**:
   - Consider periodic archiving of old versions
   - Implement differential storage for versions to reduce space

3. **Caching Strategy**:
   - Cache popular content and search results
   - Implement read replicas for analytics queries