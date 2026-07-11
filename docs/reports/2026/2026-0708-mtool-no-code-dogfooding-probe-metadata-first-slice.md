# 2026-07-08 Mtool no-code dogfooding probe metadata first slice

Status: `FIRST_SLICE_DONE`

## Summary

#432 adds the first concrete Mtool no-code dogfooding metadata slice.

Instead of adding broad persistent rows to MTOOL immediately, this slice adds a small probe helper:

- `mtool/app/no_code_mtool_dogfooding_probe.php`
- `app_no_code_mtool_dogfooding_probe_screen_definition()`
- focused coverage in `NoCodeScreenDefinitionTest`

The helper turns the selected Mtool Source Output review surface into a no-code screen-definition fixture that can be used by the next artifact-generation / inspection slice.

## Accepted Capability

The probe now fixes a concrete `MTOOL` contract:

- contract key: `mtool_source_output_review`
- backing entity: `project_source_outputs`
- explicit usage intent: `internal`
- explicit view preference: `review_list`
- no-code role: `managed-screen`
- operation: `review_mtool_source_output_profile`
- operation type: `read`
- permission: `project.read`

The first field set is deliberately review-oriented:

- `source_output_key`
- `name`
- `class_type`
- `artifact_strategy`
- `target_binding_type`
- `spec_visibility`
- `source_output_dir`

This keeps the first dogfooding probe focused on read/review behavior and avoids making generated no-code UI the canonical Mtool edit surface.

## Boundary

- No push was performed for this slice.
- No Mtool admin page was replaced.
- No broad MTOOL persistent seed was added.
- No public preview boundary was changed.
- The database-tool foundation remains the base; this slice only adds a small no-code layer probe on top of it.

## Verification

- `php -l mtool/app/no_code_mtool_dogfooding_probe.php`
- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- `docker exec mtool-rebuild-web-admin-1 phpunit /var/www/tests/Integration/NoCodeScreenDefinitionTest.php`
  - Result: `OK (6 tests, 57 assertions)`
  - Note: PHPUnit emitted a cache write warning for `.phpunit.result.cache` because the container filesystem is read-only for that path; test execution still passed.

An attempted `make test TESTS=tests/Integration/NoCodeScreenDefinitionTest.php` ran the full Integration suite in this Makefile and exposed the first draft fixture's missing `storage_role=business`. The helper was fixed and the focused test passed after that correction.

## Next Step

#433 should generate or inspect the no-code runtime / screen-definition artifact path for the selected Mtool Source Output review surface. The useful question is no longer "which Mtool surface?" but "how does this concrete probe move through the same artifact path as normal no-code runtime output?"
