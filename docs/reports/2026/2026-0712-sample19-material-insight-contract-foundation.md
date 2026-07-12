# Sample19 material insight contract foundation

Date: 2026-07-12

## Summary

#808 adds the fixture-backed Sample19 `material_insight_v0` builder and validator.

The new foundation derives a normalized material insight artifact from the existing Sample19 schema proposal source/proposal/canonical inputs. It does not call an AI provider, does not add an admin route, and does not mutate DB/config metadata.

## Implementation

Added `mtool/app/material_insight.php` with:

- `APP_MATERIAL_INSIGHT_VERSION = material_insight_v0`;
- `app_material_insight_from_schema_proposal(...)`;
- `app_material_insight_validate(...)`.

The generated artifact includes:

- source identity and SHA-256;
- Sample19 schema proposal review basis;
- normalized entity summaries;
- bounded Q&A cards;
- read-only UI outline screens;
- explicit prohibited actions;
- validation metadata with `mutation_performed=false`.

Added `tests/Integration/MaterialInsightTest.php` covering:

- successful Sample19 artifact construction;
- source/canonical hash binding;
- expected entities and Q&A cards;
- read-only UI outline with empty actions;
- fail-closed behavior for broken entity/Q&A references;
- fail-closed behavior for unsafe UI actions, missing prohibitions, mutation marker, and hash mismatch.

## Verification

- `php -l mtool/app/material_insight.php`
- `php -l tests/Integration/MaterialInsightTest.php`
- `git diff --check`
- `make test`
  - 471 tests
  - 14,247 assertions
  - 1 skipped

## Scope boundary

This slice intentionally does not add:

- AI provider or Ollama calls;
- task execution;
- admin route or browser smoke;
- DB/config metadata mutation;
- import, apply, build, publish, or generated route execution.

## Next lane

#809: close the material insight contract foundation and decide whether to promote a read-only fixture preview route, additional validator hardening, or documentation of the Q&A/UI outline boundary.
