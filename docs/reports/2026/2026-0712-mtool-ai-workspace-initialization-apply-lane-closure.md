# Mtool AI Workspace Initialization Apply Lane Closure

Date: 2026-07-12

## Summary

The workspace initialization apply helper lane is closed.

The implemented boundary is intentionally narrow: `app_ai_workspace_initialization_apply()` can write to the filesystem only when a successful `apply`-mode preflight has already recorded explicit approval. It creates missing directories and writes missing manifests, but it does not overwrite existing manifests and it does not start scanning, import, generation, validation, or copy/adaptation workflows.

## Supported boundary after #841

The current supported initialization pipeline is:

1. Resolve the workspace contract without filesystem writes.
2. Build the onboarding prompt artifact without filesystem writes.
3. Build an initialization preflight in `dry-run` or `apply` mode.
4. Apply only when:
   - `mode=apply`,
   - explicit approval is present,
   - warnings have been resolved or explicitly accepted,
   - `can_apply=true`, and
   - `no_overwrite=true`.

## Product decision

The next implementation should not be an admin UI first.

The safer next product slice is a CLI/command entry point that composes the existing resolver, onboarding artifact, preflight, and apply helper. This gives Codex/Claude-style AI workflows a concrete command boundary before adding a browser/admin surface.

Admin UI can come later if there is a concrete operator-facing need. Prompt-only documentation is useful, but without a command boundary the AI/user still lacks a clear validation/apply pipeline.

## Next selected slice

Promote:

`Mtool AI workspace initialization CLI entry preflight`

Scope:

- Define the command name, options, input precedence, approval flags, warning acceptance, dry-run/apply behavior, output shape, and tests.
- Prefer a no-write dry-run default.
- Require explicit approval for apply.
- Keep scan/import/generation/validation out of scope.
- Preserve no-overwrite and external/disabled role behavior.

This should be planned before adding the actual command wrapper.

