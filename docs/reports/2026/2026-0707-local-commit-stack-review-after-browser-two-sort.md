# Local Commit Stack Review After Browser Two-Sort / browser two-sort 後の local commit stack review

Status: DONE

#322 chooses a local commit stack review after closing the browser-visible two-sort lane. #323 records the current stack shape before another implementation lane or push.

## Local Stack / local stack

Baseline: `origin/develop` at `5b23e8de Record post cleanup verification`.

Current local head: `b01ae2af Close runtime data browser secondary sort lane`.

Ahead count: 17 commits.

## Grouping / grouping

The current local stack is reviewable as these meaning groups:

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

## Review Judgment / review 判断

No local history rewrite is recommended yet.

Reason: the stack is still readable by lane, and the newest sort work naturally extends the existing runtime-data query-control expansion. If the next action is a final pre-push cleanup, the stack can be consolidated into fewer review commits. If the next action is another implementation lane, keeping this shape preserves recent verification boundaries.

## Latest Verification Baseline / 最新検証 baseline

Latest full verification from #319:

- `git diff --check`
- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `php -l tests/Integration/NoCodeRuntimeTest.php`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test`

Full `make test` result: 337 tests, 11136 assertions, 1 skipped.

## Next Candidates / 次候補

- Add a third visible sort row to match the endpoint max-3 contract.
- Add dynamic sort-row builders for filter/sort controls.
- Add sortable table headers as a friendlier entry point for current/alias runtime-data sorting.
- Add numeric/date-aware sort semantics after read-model field typing is explicit.
- Run final pre-push consolidation if the next user instruction is to prepare for push.

## Push Boundary / push 境界

No squash, history rewrite, or push was performed for #322/#323.
