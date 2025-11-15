# Project Artifacts Archive

This directory contains historical project artifacts and documentation.

## Directory Structure
- `/decisions` - Key architectural and design decisions
- `/meetings` - Meeting notes and minutes
- `/diagrams` - System architecture diagrams
- `/proposals` - Feature proposals and RFCs
- `/reports` - Project status reports
- `/historical` - Deprecated documentation

## Artifact Retention Policy
- All artifacts are preserved indefinitely
- New versions supersede old ones but don't delete them
- Mark deprecated documents with `[DEPRECATED]` prefix

## Key Artifacts
1. [Initial Architecture Proposal](/project-artifacts/proposals/architecture-v1.md)
2. [Content Versioning RFC](/project-artifacts/proposals/content-versioning-rfc.md)
3. [Q2 2025 Roadmap](/project-artifacts/reports/roadmap-q2-2025.md)

## Adding New Artifacts
1. Place in appropriate category directory
2. Include date in filename (YYYY-MM-DD)
3. Add entry to this README if significant

## Accessing Artifacts
```bash
# Find all decision records
find docs/project-artifacts/decisions -type f -name "*.md"

# Search historical meeting notes
grep -r "version control" docs/project-artifacts/meetings/
```

## Version Control
All artifacts are versioned alongside code in Git. Use git history to track changes:
```bash
git log -- docs/project-artifacts/