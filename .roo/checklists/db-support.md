# Database Support Quality Checklist

## Proper Index Use
- [ ] Indexes exist for all foreign key columns
- [ ] Composite indexes are used for queries filtering on multiple columns
- [ ] Indexes are reviewed for unused/duplicate indexes (check query execution plans)
- [ ] Indexes are properly named (e.g., `idx_table_column` or `table_column_idx`)
- [ ] Partial indexes are considered for large tables with filtered queries
- [ ] Indexes are reviewed for proper column order (most selective columns first)
- [ ] Unique constraints are enforced via unique indexes where appropriate
- [ ] Index maintenance is scheduled (rebuild/reorganize based on fragmentation)

## SQL Injection Prevention
- [ ] All queries use prepared statements/parameterized queries
- [ ] No raw SQL concatenation with user input exists
- [ ] ORM/query builder methods are used properly (no raw where clauses)
- [ ] Input validation is performed before database operations
- [ ] Database user has least privilege necessary
- [ ] Stored procedures use parameters rather than dynamic SQL
- [ ] Error messages don't expose database schema details
- [ ] Regular security audits are performed for SQL injection vulnerabilities

## Normalized Schema Design
- [ ] Tables follow 3NF (Third Normal Form) where appropriate
- [ ] Proper primary keys exist for all tables (natural or surrogate)
- [ ] Foreign key relationships are properly defined
- [ ] Redundant data is minimized (except where denormalization is justified)
- [ ] Data types are appropriate for each column (not over/under-sized)
- [ ] Default values are set where appropriate
- [ ] Nullability is properly specified for each column
- [ ] Check constraints exist for domain integrity
- [ ] Proper naming conventions are followed (singular table names, etc.)
- [ ] Documentation exists for any denormalized structures and the rationale

## General Database Best Practices
- [ ] Database schema is version controlled (migrations)
- [ ] Backup and recovery procedures are documented and tested
- [ ] Connection pooling is properly configured
- [ ] Transaction isolation levels are appropriate for the use case
- [ ] Long-running transactions are avoided
- [ ] Database statistics are up-to-date
- [ ] Query performance is monitored and optimized
- [ ] Schema changes are reviewed for backward compatibility