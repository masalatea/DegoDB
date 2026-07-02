# Guarded Publish Candidate Detail UI First Slice / guarded publish candidate detail UI first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first guarded UI surface for no-code publish candidates on the existing `NO-CODE-RUNTIME` Source Output detail page. Operators/admins can create draft candidates from publishable readiness, see saved candidate revisions, and move candidates through request-review / approve / reject using the repository transition helper.

既存の `NO-CODE-RUNTIME` Source Output detail page に、no-code publish candidate の最初の guarded UI surface を実装した。operator / admin は publishable readiness から draft candidate を作成し、保存済み candidate revision を確認し、repository transition helper 経由で request-review / approve / reject に進められる。

## Changes / 変更

- Added `Publish Candidates` to `project_source_output_detail_page.php` for `NO-CODE-RUNTIME`.
- Added create draft candidate action from current publish readiness.
- Added candidate history table with artifact, readiness, status, and creator context.
- Added guarded request-review / approve / reject forms.
- Kept public runtime URL and package exposure explicitly out of scope.
- Added static contract assertions that the detail page uses the candidate repository helpers and keeps public exposure deferred.

## Boundary / 境界

- In scope: existing Source Output detail route, `NO-CODE-RUNTIME` only, candidate create/list/history display, guarded transition actions, CSRF-protected POSTs, repository guard reuse.
- Out of scope: public runtime URL, artifact packaging, rollback, dedicated candidate route set, new approval workflow tables.

## Verification / 検証

- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `php -l tests/Integration/NoCodePublishCandidateRepositorySqliteTest.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`7 tests, 92 assertions`).
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1744 assertions`).
- Full `make test`: passed (`318 tests, 10483 assertions, skipped 1`).

## Next / 次

Replan the next product slice after guarded candidate UI. The likely next candidate is public runtime URL/package exposure for approved candidates, but rollback and published revision selection should remain separate.
