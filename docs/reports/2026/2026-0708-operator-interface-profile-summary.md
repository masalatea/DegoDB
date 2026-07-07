# Operator Interface Profile Summary

Date: 2026-07-08

## Status

Done as #426.

This slice adds an operator/admin summary for the no-code interface profile before building a broader edit UI.

## What changed

- `screen-definition.json` inspection now keeps per-contract interface profile data in addition to aggregate counts.
- Source Outputs no-code inspection shows contract key, usage intent, explicit/derived source, current view variants, and traceability target count.
- Public runtime preview output remains unchanged; internal profile/detail information is kept on the admin/operator side.

## Boundary

This is a display/readability slice, not the edit UI itself.

The next lane should add the smallest admin path for editing contract-level `usage_intent`, while preserving the existing role fields:

- `no_code_role`
- `sync_role`
- `app_persistence_role`

View variant selection should remain a separate metadata layer so the same interface can be used for different presentations without changing the data-flow intent.

## Verification

Completed verification for this slice:

- `php -l mtool/app/no_code_operator_inspection.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `git diff --check`
- `make test`

Result:

- `Tests: 340, Assertions: 11179, Skipped: 1.`

Note:

- Direct local `./vendor/bin/phpunit tests/Integration/NoCodeOperatorInspectionTest.php` was not available because this repository uses the Docker-backed test entrypoint in this workspace.
