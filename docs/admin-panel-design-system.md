# Admin Panel Design System

## Core Principles
- **Accessibility**: WCAG 2.1 AA compliant
- **Responsive**: Works on 320px to 4K screens
- **Performance**: Optimized assets, lazy loading
- **Consistency**: Unified design language

## Color Scheme
### Light Mode
- Primary: `#2563eb` (blue-600)
- Secondary: `#4f46e5` (indigo-600)
- Background: `#ffffff` (white)
- Surface: `#f9fafb` (gray-50)
- Text: `#111827` (gray-900)

### Dark Mode
- Primary: `#3b82f6` (blue-500)
- Secondary: `#6366f1` (indigo-500)
- Background: `#111827` (gray-900)
- Surface: `#1f2937` (gray-800)
- Text: `#f9fafb` (gray-50)

## Typography
- **Font Family**: Inter (system sans-serif fallback)
- **Base Size**: 16px
- **Scale**: 
  - H1: 2.5rem (40px)
  - H2: 2rem (32px)
  - H3: 1.5rem (24px)
  - Body: 1rem (16px)
  - Small: 0.875rem (14px)

## Spacing System
- **Base Unit**: 4px
- **Scale**: 
  - xs: 4px
  - sm: 8px
  - md: 16px
  - lg: 24px
  - xl: 32px
  - 2xl: 48px
  - 3xl: 64px

## Breakpoints
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

## Component Library
1. **Navigation**
   - Sidebar (collapsible)
   - Breadcrumbs
   - Tabs

2. **Cards**
   - Stats cards
   - Content cards
   - System status cards

3. **Forms**
   - Input fields
   - Select dropdowns
   - Toggles
   - Validation states

4. **Tables**
   - Sortable columns
   - Pagination
   - Row actions

5. **Visualization**
   - Charts (bar, line, pie)
   - Metrics display
   - A/B test comparison

## Implementation Notes
- Use Tailwind CSS utility classes
- Prefer Shadcn components
- Implement dark/light mode toggle
- Optimize images with WebP format
- Lazy load non-critical components