# ADR-0001: Monorepo Structure

## Status

Accepted

## Context

The Baoson Platform Core needs to support multiple applications and shared packages while maintaining clean separation of concerns and efficient development workflows.

## Decision

We will use a monorepo structure with the following organization:

```
baoson-platform-core/
├── apps/              # Applications
│   └── control-plane/ # Laravel 12 main application
├── packages/          # Shared packages
│   ├── shared-types/  # TypeScript type definitions
│   └── ui-admin/      # Reusable admin UI components
├── infra/             # Infrastructure as code
│   └── scripts/       # Development and deployment scripts
└── docs/              # Documentation
```

## Rationale

1. **Code Sharing**: Shared types and UI components can be reused across applications
2. **Dependency Management**: Single source of truth for dependencies via pnpm workspaces
3. **Development Efficiency**: Changes to shared code are immediately available to all apps
4. **Versioning**: Shared packages can be versioned independently within the monorepo
5. **Tooling**: Unified tooling (linting, testing, building) across the codebase

## Consequences

### Positive

- Easier code sharing and reuse
- Consistent tooling and dependencies
- Simplified CI/CD pipelines
- Better developer experience

### Negative

- Slightly more complex initial setup
- Need to manage workspace dependencies
- Larger repository size

## Alternatives Considered

1. **Multi-repo**: Rejected due to complexity in managing dependencies and versioning
2. **Single app**: Rejected as it doesn't scale for multiple applications
3. **Nx/Lerna**: Considered but pnpm workspaces provide sufficient functionality

## Implementation Notes

- Using pnpm workspaces for package management
- Turbo.js for build orchestration (optional but recommended)
- Each package/app has its own package.json
- Shared types are TypeScript-only for now

