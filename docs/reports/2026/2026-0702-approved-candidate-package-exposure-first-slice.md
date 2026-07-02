# Approved Candidate Package Exposure First Slice / approved candidate package exposure first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Implemented the first approved-only package exposure slice for no-code publish candidates. The existing `NO-CODE-RUNTIME` Source Output detail page now shows artifact detail/download handoff links only after a candidate reaches `approved`, while draft/review/rejected candidates show guarded package-exposure text.

no-code publish candidate の最初の approved-only package exposure slice を実装した。既存の `NO-CODE-RUNTIME` Source Output detail page は、candidate が `approved` になった後だけ artifact detail / download handoff link を表示し、draft / review / rejected candidate には package exposure が approval まで guard されることを表示する。

## Changes / 変更

- Added approved-only `Approved package exposure` affordance to candidate history.
- Reused existing admin artifact detail and download routes.
- Added non-approved guard copy for package exposure.
- Kept public runtime URL, public alias route, package copying, rollback, and dedicated candidate routes out of scope.
- Extended static source-output contract coverage for approved package exposure and public URL deferral text.

## Boundary / 境界

- In scope: existing Source Output detail route, `NO-CODE-RUNTIME` only, approved candidate artifact detail/download links, non-approved guard text.
- Out of scope: public runtime URL, public alias key route, package copying, new storage table, rollback, dedicated candidate route set.

## Verification / 検証

- `php -l mtool/app/project_source_output_detail_page.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- `php -l mtool/app/no_code_publish_candidate_repository_pdo.php`: passed.
- `git diff --check`: passed.
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1748 assertions`).
- Focused Docker-backed PHPUnit for `NoCodePublishCandidateRepositorySqliteTest`: passed (`7 tests, 92 assertions`).
- Full `make test`: passed (`318 tests, 10487 assertions, skipped 1`).

## Next / 次

Replan the next product slice after approved candidate package exposure. The likely next candidate is public runtime URL / public alias route planning, but it should remain a separate first slice because public delivery semantics are broader than admin package handoff links.
