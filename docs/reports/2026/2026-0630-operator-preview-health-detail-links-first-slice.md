# Operator Preview Health Detail Links First Slice / operator preview health・detail link first slice

Date: 2026-06-30

Status: `FIRST_SLICE_DONE`

## Summary / 概要

Added compact operator-visible health and detail affordances for generated no-code runtime artifacts. The Source Outputs admin page now shows `ready` / `warning` / `missing` health for `NO-CODE-RUNTIME`, explains the health reasons, links to the definition detail page, exposes latest artifact download when the archive exists, and keeps preview file paths visible.

generated no-code runtime artifact 向けに、operator-visible な health と detail affordance を追加した。Source Outputs admin page で `NO-CODE-RUNTIME` の `ready` / `warning` / `missing` health、health reason、definition detail link、latest artifact archive がある場合の download link、preview file path を確認できる。

## Implementation / 実装

- Extended `mtool/app/no_code_operator_inspection.php` with `app_no_code_operator_health_summary()`.
- Health states are derived only from existing metadata:
  - definition availability;
  - latest artifact availability and archive existence;
  - `screen-definition.json`, `runtime-preview.json`, and `runtime-preview.html` availability;
  - generated screen count;
  - JSON read/decode errors.
- Updated `mtool/app/project_source_outputs_page.php` to show health state, health detail, definition detail link, latest artifact download link, and preview file paths.
- Extended `tests/Integration/NoCodeOperatorInspectionTest.php` with ready/missing/warning health coverage.

## Result / 結果

- OH1 Health model boundary: `DONE`
- OH2 Detail-link affordances: `DONE`
- OH3 Operator page integration: `DONE`
- OH4 Focused coverage and docs: `DONE`

The slice remains read-only and does not add a visual builder, metadata editing workflow, publish approval workflow, remote transport, conflict resolution, or new generated runtime behavior.

## Verification / 検証

- `php -l mtool/app/no_code_operator_inspection.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `php -l tests/Integration/NoCodeOperatorInspectionTest.php`
- `git diff --check`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample30-no-code-app-local-sync-demo/compose.yaml --run-script=sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/NoCodeOperatorInspectionTest.php`
- `make sample30-pack-runtime-test`
- `make test`

Full test result: `OK, but incomplete, skipped, or risky tests! Tests: 302, Assertions: 10045, Skipped: 1.`

## Next / 次

The next active work is a post-operator preview health no-code product-goal replan. Likely candidates are a small artifact detail follow-up, targeted runtime polish only if the health surface reveals a concrete issue, or narrow sync/error-state pressure.
