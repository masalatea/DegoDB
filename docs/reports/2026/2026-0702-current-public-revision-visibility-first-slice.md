# Current Public Revision Visibility First Slice / current public revision visibility first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

The `NO-CODE-RUNTIME` Source Output detail page now marks which approved publish candidate backs the project-level `current` public runtime preview. Older approved candidates are shown as approved but non-current, with rollback selection still explicitly deferred.

`NO-CODE-RUNTIME` Source Output detail page で、project-level `current` public runtime preview がどの approved publish candidate を指しているかを表示するようにした。古い approved candidate は approved だが non-current として表示し、rollback selection はまだ deferred と明示する。

## Changes / 変更

- Reused `app_pdo_find_current_approved_no_code_publish_candidate(...)` on the existing `NO-CODE-RUNTIME` detail page.
- Added `Current public revision` display for the approved candidate currently resolved by `/runs/no-code/{project_key}/current/runtime-preview.html`.
- Added `Approved non-current revision` display for older approved candidates.
- Kept explicit revision selection, rollback, custom alias storage, and new published-revision storage out of scope.

## Boundary / 境界

- In scope: current alias visibility on the existing operator/admin candidate history surface and static contract coverage.
- Out of scope: rollback action, explicit current selection storage, custom alias storage, new public URL shapes, package copy/static hosting.

## Verification / 検証

- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1774 assertions`).
- `git diff --check`: passed.
- Full `make test`: passed (`321 tests, 10559 assertions, skipped 1`).

## Next / 次

Replan the next public-delivery slice. Likely candidates are explicit revision selection / rollback boundary or custom public alias storage.
