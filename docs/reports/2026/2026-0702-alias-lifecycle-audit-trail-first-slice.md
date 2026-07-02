# Alias Lifecycle Audit Trail First Slice / alias lifecycle audit trail first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Added append-only public runtime alias lifecycle events for create, update, and delete operations. The operator/admin `NO-CODE-RUNTIME` detail UI now shows recent alias lifecycle events so alias movement remains visible after rollback decisions or alias deletion.

public runtime alias の create / update / delete 操作に append-only lifecycle event を追加した。operator/admin の `NO-CODE-RUNTIME` detail UI で最近の alias lifecycle event を表示し、rollback 判断や alias 削除後も alias の移動履歴を確認できる。

## Changes / 変更

- Added `no_code_public_runtime_alias_events`.
- Added repository listing for alias lifecycle events.
- Recorded `alias_created`, `alias_updated`, and `alias_deleted` events from alias mutation helpers.
- Added recent alias lifecycle event display to the `Public Runtime Aliases` section.
- Added focused repository and static contract coverage.

## Boundary / 境界

- In scope: append-only alias lifecycle storage, create/update/delete event recording, recent UI display, focused coverage.
- Out of scope: broad audit search/export, automatic alias follow-current behavior, new public routes, push.

## Verification / 検証

- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`
- `php -l mtool/app/project_source_output_detail_page.php`
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`
  - `15 tests, 254 assertions`
- `docker compose exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/OpenApiSourceOutputContractTest.php`
  - `22 tests, 1798 assertions`
- `make sample28-no-code-public-runtime-browser-smoke`
  - Verified artifact-key, current, and `stable` alias public runtime preview URLs.
- `git diff --check`
- `make test`
  - `326 tests, 10699 assertions, skipped 1`

## Next / 次

Replan the next product-facing slice. Public delivery now has route capability, cache policy, rollback copy, browser smoke, and alias lifecycle audit visibility.
