# Runtime Data Browser History Replay Closure

Date: 2026-07-07

Status: `DONE`

## Summary

#294 replans after the browser history replay first slice and chooses closure before starting another runtime-data behavior lane. #295 closes the lane.

## Accepted Capability

Current/alias generated runtime previews can now treat read-only runtime-data exploration as browser history state:

- successful explicit query operations add a browser history entry with `pushState`;
- initial URL replay and Clear continue to normalize the current URL with `replaceState`;
- browser back/forward replays the current URL query through the existing read-only `runtime-data.json` refresh path;
- popstate replay does not create additional history entries;
- search plus two-filter query state is covered by the sample28 public runtime browser smoke.

This keeps the data boundary unchanged: navigation restores read-only query state and rendered runtime-data payloads, while submit/outbox mutation remains separate.

## Latest Verification Baseline

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make test`
  - `337 tests`, `11129 assertions`, `1 skipped`.

## Remaining Candidates

- Typed filter operators.
- More than two visible generated filter rows.
- Multi-column sort.
- Broader read-model shape and display policy.
- Multi-profile browser-history smoke promotion after the sample28 behavior proves stable.
- Push cleanup when the local ahead stack is ready to publish.

## Boundary

- In scope: closure, accepted capability, latest verification baseline, remaining candidates, and no-push status.
- Out of scope: new code, endpoint contract changes, typed operators, additional filter-row UI, multi-column sort, broader read-model shape changes, mutation behavior, artifact-key preview changes, and push.
