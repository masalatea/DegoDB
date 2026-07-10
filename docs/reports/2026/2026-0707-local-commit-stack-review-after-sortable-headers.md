# Local Commit Stack Review After Sortable Headers

Date: 2026-07-07

Status: `DONE`

## Summary

#338 chooses a local commit stack review after the sortable header and sortable header state lanes closed. #339 records the current stack shape before compact icon treatment, dynamic row builders, richer sort semantics, broader read-model field typing, push cleanup, or another implementation lane.

## Local Stack

Baseline: `origin/develop` at `5b23e8de Record post cleanup verification`.

Current local head: `cfdcde5b Close runtime data sortable header state lane`.

Ahead count: 25 commits.

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

5. Multi-column sort endpoint and fixed visible sort controls:
   - `b178fa49 Add runtime data multi-column sort endpoint`
   - `15b15884 Add runtime data browser secondary sort controls`
   - `b01ae2af Close runtime data browser secondary sort lane`
   - `43b4a37d Review runtime data query expansion stack`
   - `b854147c Add runtime data browser third sort row`
   - `0c305116 Close runtime data browser third sort lane`
   - `caf2e79f Review runtime data stack after third sort`

6. Sortable table headers and sorted-column state:
   - `61b55a52 Add runtime data sortable table headers`
   - `d9cbecae Close runtime data sortable header lane`
   - `b338bedd Add runtime data sortable header state`
   - `cfdcde5b Close runtime data sortable header state lane`

## Review Judgment

No local history rewrite is recommended yet.

Reason: the stack is long, but it is still coherent by lane. The recent sortable header work cleanly layers on top of the fixed three-sort-row contract: first the table headers became a friendlier entry point, then the active primary sort became visible and accessible through synchronized header state. If the next action is push preparation, the stack can be consolidated into fewer review commits. If the next action is more implementation, keeping the current lane boundaries preserves the verification history.

## Latest Verification Baseline

Latest full verification from #335:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11142 assertions`, `1 skipped`)

This review is docs-only, so no additional runtime test was required.

## Next Candidates

- Compact icon treatment for active sort state, if the current text suffix feels visually noisy.
- Dynamic add/remove filter and sort rows.
- Numeric/date-aware comparison and explicit null placement after read-model field typing is explicit.
- Richer read-model field typing for stronger filter/sort semantics.
- Grouped or mobile-specific query-control layout.
- Final pre-push consolidation if the next instruction is to prepare for push.

## Push Boundary

No squash, history rewrite, or push was performed for #338/#339.
