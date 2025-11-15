# Pattern Reader Quality Checklist

## Recurring Error Detection
- [ ] Check for repeated error messages in logs (PHP, JS console)
- [ ] Identify duplicate error handling code across files
- [ ] Detect uncaught exceptions in async operations
- [ ] Find missing null checks before property/method access
- [ ] Locate undefined variable/function references
- [ ] Spot unhandled promise rejections in JavaScript
- [ ] Identify SQL injection vulnerabilities in raw queries
- [ ] Find CSRF protection missing on form submissions
- [ ] Detect XSS vulnerabilities in unescaped output
- [ ] Check for race conditions in concurrent operations

## Misused Patterns
- [ ] Identify incorrect use of design patterns (e.g., Singleton abuse)
- [ ] Detect overuse/underuse of dependency injection
- [ ] Find improper component composition in Vue/React
- [ ] Spot incorrect state management patterns
- [ ] Check for anti-patterns in API response handling
- [ ] Identify promise/callback hell in async code
- [ ] Detect over-fetching/under-fetching in API calls
- [ ] Find improper caching implementations
- [ ] Spot incorrect event handling patterns
- [ ] Check for inefficient DOM manipulation in JS

## Anti-Pattern Identification
- [ ] Detect God objects/classes with too many responsibilities
- [ ] Find spaghetti code with unclear control flow
- [ ] Identify shotgun surgery (changes require many small edits)
- [ ] Spot feature envy (methods using other objects' data excessively)
- [ ] Check for inappropriate intimacy (classes knowing too much about each other)
- [ ] Find lazy class (classes not doing enough to justify existence)
- [ ] Detect duplicate code (DRY violations)
- [ ] Identify magic numbers/strings without constants
- [ ] Spot premature optimization
- [ ] Find over-engineering (YAGNI violations)

## CMS-Specific Patterns
- [ ] Check for inefficient content version handling
- [ ] Identify improper personalization implementations
- [ ] Detect suboptimal media processing patterns
- [ ] Find inefficient database queries in migrations
- [ ] Spot improper caching in content delivery
- [ ] Check for race conditions in collaborative editing
- [ ] Identify security issues in authentication flows
- [ ] Detect performance bottlenecks in analytics
- [ ] Find improper error handling in scheduled jobs
- [ ] Spot inefficient DOM updates in page builder

## Action Items
- [ ] Document found patterns in project wiki
- [ ] Create tickets for critical issues
- [ ] Suggest refactoring approaches
- [ ] Provide code examples for fixes
- [ ] Collaborate with code/db-support on implementations