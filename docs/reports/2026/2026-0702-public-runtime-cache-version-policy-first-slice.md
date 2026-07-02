# Public Runtime Cache/Version Policy First Slice / public runtime cache/version policy first slice

Date: 2026-07-02

Status: `FIRST_SLICE_DONE`

## Summary / 要約

Public no-code runtime preview responses now make route semantics explicit. Artifact-key preview URLs use an immutable public cache policy, while the project-level `current` alias keeps `no-store` so clients re-resolve the latest approved candidate.

public no-code runtime preview response で route semantics を明示した。artifact-key preview URL は immutable public cache policy を使い、project-level `current` alias は最新 approved candidate を再解決するため `no-store` を維持する。

## Changes / 変更

- Added cache policy helpers for artifact-key and current public runtime preview routes.
- Applied `public, max-age=31536000, immutable` to approved artifact-key runtime preview responses.
- Kept `Cache-Control: no-store` for `/runs/no-code/{project_key}/current/runtime-preview.html`.
- Added contract coverage for the cache helper values and route-specific response helper usage.

## Boundary / 境界

- In scope: response cache semantics for existing public runtime preview routes and contract coverage.
- Out of scope: explicit published revision selection, rollback, custom alias storage, new public URL shapes, package copy/static hosting.

## Verification / 検証

- `php -l mtool/app/no_code_public_runtime_page.php`: passed.
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`: passed.
- Focused Docker-backed PHPUnit for `OpenApiSourceOutputContractTest`: passed (`22 tests, 1770 assertions`).
- `git diff --check`: passed.
- Full `make test`: passed (`321 tests, 10555 assertions, skipped 1`).

## Next / 次

Replan the next public-delivery slice. Likely candidates are explicit revision selection / rollback boundary or custom public alias storage.
