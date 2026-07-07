# Local Commit Stack Review After Three-Sort

Date: 2026-07-07

Status: `DONE`

## Summary

#328 chooses a local commit stack review after the visible three-sort-row lane closure. #329 records the current stack shape before another implementation lane or push cleanup.

## Local Stack

Baseline: `origin/develop` at `5b23e8de Record post cleanup verification`.

Current local head: `0c305116 Close runtime data browser third sort lane`.

Ahead count: 20 commits.

## Grouping

The current local stack remains reviewable as these meaning groups:

1. URL multi-filter replay:
   - `e3a6d51b Replay runtime data multi-filter URLs`
   - `1a965a11 Close runtime data URL multi-filter replay lane`

2. Browser history replay:
   - `78d099a1 Plan runtime data browser history replay`
   - `5622c3ef Replay runtime data browser history`
   - `17258363 Close runtime data browser history replay lane`

3. Typed filter operators:
   - `ce4c2031 Plan runtime data typed filter operators`
   - `589def2a Add runtime data typed filter operators`
   - `e4f64316 Close runtime data typed filter operator lane`
   - `ad0c06be Verify typed filter operators across no-code profiles`

4. Visible three-filter-row controls:
   - `c7ae44ab Plan runtime data visible filter row expansion`
   - `a3e6a695 Add third runtime data filter row`
   - `e4833d02 Verify third runtime data filter row across profiles`
   - `b2a88c41 Close runtime data visible filter row lane`
   - `708b941d Review runtime data filter row commit stack`

5. Multi-column sort endpoint and browser controls:
   - `b178fa49 Add runtime data multi-column sort endpoint`
   - `15b15884 Add runtime data browser secondary sort controls`
   - `b01ae2af Close runtime data browser secondary sort lane`
   - `43b4a37d Review runtime data query expansion stack`
   - `b854147c Add runtime data browser third sort row`
   - `0c305116 Close runtime data browser third sort lane`

## Review Judgment

No local history rewrite is recommended yet.

Reason: the stack is still readable by lane, and the latest third-sort work closes the fixed visible query-control capacity against the existing endpoint contract. If the next action is final pre-push cleanup, the stack can be consolidated into fewer review commits. If the next action is another implementation lane, keeping this shape preserves useful verification and closure boundaries.

## Latest Verification Baseline

Latest full verification from #325:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (337 tests, 11138 assertions, 1 skipped)

This review is docs-only, so no additional runtime test was required.

## Next Candidates

- Dynamic add/remove filter and sort rows.
- Sortable table headers that drive the same read-only query contract.
- Numeric/date-aware comparison and explicit null placement.
- Richer read-model field typing so filter/sort semantics can move beyond display-value matching.
- Grouped or mobile-specific query-control layout.
- Final pre-push consolidation if the next instruction is to prepare for push.

## Push Boundary

No squash, history rewrite, or push was performed for #328/#329.
