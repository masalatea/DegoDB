# Read-Only Readiness Lane Detailed Replan

Date: 2026-07-10

Status: `DONE`

## Summary

#702 replans the sample18 route/config readiness lane into smaller implementation steps. The previous single `read-only readiness metadata first slice` was too broad because it mixed metadata shape, helper logic, screen-definition carry-through, runtime rendering, browser smoke, and next execution decisions.

## Updated Plan

The new near-term sequence is:

1. `#702 Read-only readiness lane detailed replan`
2. `#703 Sample18 readiness metadata shape contract`
3. `#704 Sample18 readiness snapshot helper first slice`
4. `#705 Readiness metadata screen-definition carry-through`
5. `#706 Readiness runtime preview carry-through`
6. `#707 Readiness fast contract coverage`
7. `#708 Readiness browser smoke first slice`
8. `#709 Post readiness metadata lane closure`

Parked follow-ups:

- `#710 Server-generated availability overlay preflight`
- `#711 Real guarded execution smoke preflight`

## Decision

Keep the lane read-only until route/config readiness is visible in generated metadata, runtime preview JSON, stable HTML markers, fast tests, and a narrow browser smoke.

Do not promote real guarded execution smoke until readiness failure states are inspectable without sending a generated-submit request.

## Verification

Docs-only replan. Verification: `git diff --check`.
