# Related Settings Navigation Expansion

Date: 2026-07-08

## Status

Done as #429.

This slice strengthens operator/admin navigation from generated no-code interface profiles back to the settings that shape them.

## What Changed

- Operator inspection now builds `related_settings` links for each interface profile.
- Source Outputs no-code inspection renders those links under Interface Profiles.
- Links currently cover:
  - Shared Contracts
  - Data Class
  - Data Class Fields
  - DB Access
  - NO-CODE-RUNTIME Source Output
  - Source Outputs inspection

## Boundary

Public runtime previews still do not expose internal admin links.

Operation-specific settings remain a later slice because the current traceability payload has operation keys, but not enough source-name/function-route context to deep-link safely to every DB Access function detail page.

## Verification

Completed verification:

- `php -l mtool/app/no_code_operator_inspection.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `git diff --check`
- `make test`

Result:

- `Tests: 342, Assertions: 11195, Skipped: 1.`
