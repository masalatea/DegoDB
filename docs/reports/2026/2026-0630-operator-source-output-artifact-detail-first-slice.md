# Operator Source-Output Artifact Detail First Slice / operator source-output artifact detail first slice

Date: 2026-06-30

Status: `FIRST_SLICE_DONE`

## Summary / 概要

Added a read-only Source Output artifact detail route/page for operator/admin inspection. Operators can now move from Source Outputs summary or definition artifact history into a detail page that shows artifact manifest identity, archive state, runtime source, file counts, bundle paths, and download affordance.

operator/admin inspection 用に read-only Source Output artifact detail route/page を追加した。Source Outputs summary や definition artifact history から、artifact manifest identity、archive state、runtime source、file count、bundle path、download affordance を確認できる detail page へ進める。

## Implementation / 実装

- Added `app_project_source_output_artifact_detail_path()`.
- Added route `/projects/{project_key}/source-outputs/artifacts/{artifact_key}` as `project_source_output_artifact_detail`.
- Added `mtool/app/project_source_output_artifact_detail_page.php`.
- Wired the page into `mtool/app/http.php`.
- Added route/auth contract metadata using the same `source_output.download` audited boundary as archive download.
- Linked artifact keys from:
  - the `NO-CODE-RUNTIME` inspection summary;
  - the Source Outputs artifact table;
  - the Source Output detail artifact history table.
- Updated route/auth focused tests.

## Result / 結果

- OD1 Route and auth boundary: `DONE`
- OD2 Artifact detail page: `DONE`
- OD3 Operator links: `DONE`
- OD4 Focused coverage and docs: `DONE`

This slice remains read-only. It does not add artifact editing, publish approval workflow, visual builder, generated runtime behavior changes, remote transport, or conflict resolution.

## Verification / 検証

- `php -l mtool/app/project_source_output_artifact_detail_page.php`
- `php -l mtool/app/router.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/project_route_authorization.php`
- `php -l mtool/app/project_source_output_route_common.php`
- `php -l mtool/app/project_source_outputs_page.php`
- `php -l mtool/app/project_source_output_detail_page.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample30-no-code-app-local-sync-demo/compose.yaml --run-script=sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/SecurityFoundationContractTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample30-no-code-app-local-sync-demo/compose.yaml --run-script=sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/ProjectRouteAuthorizationContractTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample30-no-code-app-local-sync-demo/compose.yaml --run-script=sample/tutorials/sample30-no-code-app-local-sync-demo/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
- `git diff --check`
- `make sample30-pack-runtime-test`
- `make test`

Full test result: `OK, but incomplete, skipped, or risky tests! Tests: 303, Assertions: 10060, Skipped: 1.`

## Next / 次

The next active work is a post-operator artifact detail no-code product-goal replan. Likely candidates are a small artifact detail follow-up if the new page exposes a concrete missing field, targeted runtime polish only if a concrete runtime issue appears, or narrow sync/error-state pressure.
