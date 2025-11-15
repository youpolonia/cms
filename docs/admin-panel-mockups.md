# Admin Panel Mockups

## Dashboard Layout
### Desktop
```mermaid
graph TD
    A[Sidebar Navigation] --> B[Main Content]
    B --> C[Stats Overview]
    B --> D[Recent Activity]
    B --> E[System Status]
    C --> F[Total Content: 1,234]
    C --> G[Active Users: 567]
    C --> H[Storage Used: 45%]
```

### Mobile
```mermaid
graph TD
    A[Collapsed Menu] --> B[Stats Stack]
    B --> C[Content Card]
    B --> D[Users Card]
    B --> E[Storage Card]
```

## Content Management
### Content List
```mermaid
graph TD
    A[Search Bar] --> B[Filter Controls]
    B --> C[Content Table]
    C --> D[Title Column]
    C --> E[Status Column]
    C --> F[Actions Column]
```

### Content Editor
```mermaid
graph TD
    A[Title Input] --> B[Content Area]
    B --> C[Formatting Toolbar]
    C --> D[Save Button]
    C --> E[Preview Button]
```

## System Configuration
### Settings Page
```mermaid
graph TD
    A[Tab Navigation] --> B[General Settings]
    A --> C[Performance]
    A --> D[Modules]
    B --> E[Site Title]
    B --> F[Timezone]
    C --> G[Cache Settings]
```

## User Administration
### User List
```mermaid
graph TD
    A[Add User Button] --> B[User Table]
    B --> C[Name Column]
    B --> D[Email Column]
    B --> E[Role Column]
    B --> F[Actions Column]
```

### User Form
```mermaid
graph TD
    A[Name Input] --> B[Email Input]
    B --> C[Role Select]
    C --> D[Permissions Toggles]
    D --> E[Save Button]
```

## A/B Testing
### Test Overview
```mermaid
graph TD
    A[Test List] --> B[Create Test Button]
    A --> C[Test Cards]
    C --> D[Test Name]
    C --> E[Status Badge]
    C --> F[Results Chart]
```

### Test Details
```mermaid
graph TD
    A[Test Name] --> B[Variants Table]
    A --> C[Conversion Chart]
    B --> D[Variant A]
    B --> E[Variant B]
    B --> F[Winner Highlight]
```

## Responsive Behavior
- Sidebar collapses to hamburger menu on mobile
- Tables switch to card views on small screens
- Forms stack vertically on mobile
- Charts resize proportionally