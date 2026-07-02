# Rollback Workflow Polish First Slice / rollback workflow polish first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

The operator/admin `NO-CODE-RUNTIME` detail UI now names the existing rollback path: selecting an older approved candidate moves the `current` public runtime preview back to that revision. The UI also clarifies that artifact-key URLs and alias rows do not move automatically.

operator/admin の `NO-CODE-RUNTIME` detail UI で、既存の rollback path を明示した。古い approved candidate を選ぶと `current` public runtime preview をその revision に戻せる。artifact-key URL と alias row は自動では動かないことも明示した。

## Changes / 変更

- Added rollback wording to current/non-current approved candidate markers.
- Kept the existing `select-current-public-revision` action as the implementation path.
- Added alias follow-current warning to the public runtime aliases section.
- Added static Source Output contract coverage for the rollback wording.

## Boundary / 境界

- In scope: UI wording, rollback semantics clarity, static coverage, docs.
- Out of scope: new rollback storage, rollback event stream, alias automatic follow-current mode, new public routes.

## Verification / 検証

- `php -l mtool/app/project_source_output_detail_page.php` passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php` passed.
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php` passed: `22 tests, 1795 assertions`.
- `git diff --check` passed.
- `make test` passed: `325 tests, 10665 assertions, skipped 1`.

## Next / 次

Replan the next product-facing slice. Likely candidates are alias lifecycle audit trail, public delivery browser smoke, or a new no-code product continuation outside public delivery.
