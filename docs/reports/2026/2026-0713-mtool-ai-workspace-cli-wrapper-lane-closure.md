# Mtool AI Workspace CLI Wrapper Lane Closure

Date: 2026-07-13

## Summary

The `mtool/scripts/init_ai_workspace.php` CLI wrapper lane is closed.

The command is now a usable first entry point for AI/Mtool workspace initialization. It provides usage output, JSON preflight output, and explicitly approved apply behavior while preserving the existing safety boundary.

## Supported command boundary

Command:

```text
php mtool/scripts/init_ai_workspace.php [options]
```

Supported behavior:

- default `dry-run` mode;
- JSON output with `--json`;
- explicit apply with `--mode=apply --approve`;
- resolver warning acceptance with `--accept-warnings`;
- role mapping with `--role`, `--external-role`, and `--disable-role`;
- no-overwrite apply behavior;
- no scan/import/generation/validation/copy workflow execution.

## Decision

Do not hold yet.

Promote AI-facing onboarding documentation next. The command exists, but Codex/Claude-style users and agents still need a stable document that explains:

- when to run the command;
- the safe first dry-run command;
- how to read the JSON preflight;
- when it is appropriate to ask the user for approval;
- the exact approved apply command shape;
- what the command intentionally does not do.

This is more useful than an admin UI right now because the current product direction is AI-assisted onboarding. The CLI is the durable command boundary, while the next doc is the discoverable AI/user entrance.

## Next selected slice

Promote:

`Mtool AI workspace onboarding command guide`

Scope:

- Create a stable, non-report doc for AI/user onboarding.
- Link the command boundary from `docs/README.md` if appropriate.
- Keep the guide command-first and safety-first.
- Do not add new runtime behavior in that slice.

