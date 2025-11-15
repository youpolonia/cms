# TypeScript Best Practices

## Strict TypeScript Configuration
Our project uses strict TypeScript configuration with all strict flags enabled in `tsconfig.json`:
- `strict`: true (enables all strict type checking options)
- `noImplicitAny`: true
- `strictNullChecks`: true
- `strictFunctionTypes`: true
- `strictBindCallApply`: true
- `strictPropertyInitialization`: true
- `noImplicitThis`: true
- `alwaysStrict`: true

## Error Prevention Measures

### Custom Utility Types
```typescript
// Example utility types
type Nullable<T> = T | null;
type Maybe<T> = T | undefined;
type Dictionary<T> = Record<string, T>;
```

### Type Guards for API Responses
```typescript
function isApiSuccess(response: unknown): response is { data: unknown } {
  return typeof response === 'object' && response !== null && 'data' in response;
}
```

### JSDoc Type Annotations
```typescript
/**
 * Calculates the sum of two numbers
 * @param a - First number
 * @param b - Second number
 * @returns Sum of a and b
 */
function sum(a: number, b: number): number {
  return a + b;
}
```

## Developer Tooling

### Pre-commit Hooks
We use Husky to run type checking before commits:
```sh
npm run type-check
```

### IDE Configuration
Configure your IDE to:
1. Show TypeScript errors prominently
2. Enable automatic type checking
3. Display type information on hover

### Type Cheat Sheet
| Pattern | Example |
|---------|---------|
| Optional properties | `interface User { name?: string }` |
| Readonly properties | `interface Config { readonly apiUrl: string }` |
| Union types | `type Status = 'active' | 'inactive'` |
| Type guards | `if (typeof x === 'string') { ... }` |

## Migration Guide

### Converting JavaScript to TypeScript
1. Rename `.js` files to `.ts`
2. Add basic type annotations
3. Gradually enable stricter rules
4. Fix type errors incrementally

## Common Error Patterns
| Error | Solution |
|-------|----------|
| Object is possibly 'null' | Add null checks or non-null assertions (`!`) |
| Type 'string' is not assignable to type 'number' | Ensure proper type conversion |
| Missing return type | Explicitly declare return types |
| Implicit 'any' type | Add proper type annotations |