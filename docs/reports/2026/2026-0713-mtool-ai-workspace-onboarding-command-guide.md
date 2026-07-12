# Mtool AI Workspace Onboarding Command Guide

Date: 2026-07-13

## Summary

Added a stable AI/user-facing command guide for the AI workspace onboarding CLI:

```text
docs/ai-workspace-onboarding-command-guide.md
```

The guide explains the safe `dry-run -> user approval -> apply` workflow for:

```text
php mtool/scripts/init_ai_workspace.php
```

## What changed

- Added the permanent onboarding command guide.
- Linked it from `docs/README.md`.
- Added a goal-based row to `docs/choose-your-path.md`.
- Updated `docs/current-plans.md` so #846 is done and #847 is the next closure decision.

## Boundary

This was docs-only.

No runtime behavior, CLI behavior, tests, or workspace initialization logic changed in this slice.

## Verification

Docs-only checks:

```text
git diff --check
```

## Next

Close the onboarding guide lane and decide whether to:

1. hold because the CLI + guide are enough until concrete adoption,
2. push/open PR,
3. promote a concrete adoption workflow, or
4. add a small polish/doc cross-link if a missing entrance is found.

