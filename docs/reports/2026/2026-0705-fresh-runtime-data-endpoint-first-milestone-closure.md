# Fresh Runtime Data Endpoint First Milestone Closure

Status: DONE
Date: 2026-07-05

## Summary

This report closes the first fresh runtime data endpoint milestone.

The milestone began with a boundary clarification: public current/alias runtime preview routes are artifact delivery paths, while fresh business data needs its own read-only data contract. It now has a working first slice: current/alias `runtime-data.json` routes return live seeded rows through generated DBAccess in the public web request path across sample28, sample29, and sample31.

## Accepted Capability

- Current and alias public runtime selections expose `runtime-data.json`.
- The route is authenticated, `GET` only, read-only, `no-store`, and versioned as `no-code-runtime-data-v0`.
- The data path uses generated DBAccess/read-model materialization with request-local runtime DB binding.
- Sample28 returns seeded first row key `1001`.
- Sample29 returns seeded first row key `2001`.
- Sample31 returns seeded first row key `3101`.
- Existing submit/enqueue and generated DBAccess outbox processing smokes remain passing.

## Boundary Kept

- Immutable artifact-key preview remains static artifact delivery.
- `runtime-preview.json` is not used as a fake live-data fallback.
- `runtime-data.json` does not mutate data, process outbox work, retry failed work, regenerate artifacts, or switch the current public revision.
- Submit/outbox processing remains a separate mutation path.

## Verification Baseline

- `php -l mtool/app/no_code_public_runtime_page.php`
- focused route/source contract coverage from #206
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`: 337 tests, 11079 assertions, skipped 1 from #208
- `git diff --check`

## Remaining Candidates

- UI consumption of `runtime-data.json` so current/alias previews can display fresh business data without regenerating artifacts.
- Post-submit fresh data reload behavior after outbox processing completes.
- Broader read-model shape for pagination, filters, detail selection, and form defaults.
- Error/cache wording polish for fail-closed data-route states.
- Additional sample/domain expansion only after the UI consumption boundary is explicit.

## Result

The fresh runtime data endpoint is now a proven first milestone, not just a planned boundary: it has a versioned route contract, web runtime DB binding, and three-profile smoke coverage.

Push was not performed.
