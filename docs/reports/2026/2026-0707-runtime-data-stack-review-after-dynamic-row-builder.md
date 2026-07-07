# Runtime Data Stack Review After Dynamic Row Builder

Date: 2026-07-07

Status: `DONE`

## Summary

#348 chooses local stack review after the dynamic row-builder closure. #349 records the current unpushed stack boundary before another behavior lane or push cleanup.

The local `develop` branch is `31` commits ahead of `origin/develop`. The stack remains reviewable as grouped runtime-data query-control lanes. No local squash, history rewrite, or push is recommended until the user chooses either final pre-push cleanup or another large behavior lane boundary.

## Current Commit Groups

From oldest to newest, the unpushed stack groups are:

1. URL multi-filter replay
   - `Replay runtime data multi-filter URLs`
   - `Close runtime data URL multi-filter replay lane`
2. Browser history replay
   - `Plan runtime data browser history replay`
   - `Replay runtime data browser history`
   - `Close runtime data browser history replay lane`
3. Typed filter operators
   - `Plan runtime data typed filter operators`
   - `Add runtime data typed filter operators`
   - `Close runtime data typed filter operator lane`
   - `Verify typed filter operators across no-code profiles`
4. Visible three-filter-row controls
   - `Plan runtime data visible filter row expansion`
   - `Add third runtime data filter row`
   - `Verify third runtime data filter row across profiles`
   - `Close runtime data visible filter row lane`
   - `Review runtime data filter row commit stack`
5. Multi-column sort endpoint and fixed visible sort controls
   - `Add runtime data multi-column sort endpoint`
   - `Add runtime data browser secondary sort controls`
   - `Close runtime data browser secondary sort lane`
   - `Review runtime data query expansion stack`
   - `Add runtime data browser third sort row`
   - `Close runtime data browser third sort lane`
   - `Review runtime data stack after third sort`
6. Sortable table headers and sorted-column state
   - `Add runtime data sortable table headers`
   - `Close runtime data sortable header lane`
   - `Add runtime data sortable header state`
   - `Close runtime data sortable header state lane`
   - `Review runtime data stack after sortable headers`
   - `Compact runtime data sortable header indicator`
   - `Close runtime data sortable header indicator lane`
7. Dynamic row-builder progressive disclosure
   - `Plan runtime data dynamic row builders`
   - `Add runtime data dynamic row builders`
   - `Close runtime data dynamic row builder lane`

## Recommendation

- Keep this stack as-is for continued local work.
- If the next user instruction is push, perform one final pre-push review and consider whether these groups should be squashed into broader lane commits.
- If the next user instruction is continue, prefer one of the remaining behavior candidates rather than rewriting history now.

## Verification Baseline

Latest behavior verification before this review:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample28-no-code-public-runtime-browser-smoke`
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`
- `make test` (`337 tests`, `11152 assertions`, `1 skipped`)

This review is documentation-only.

## Push

Push was not performed.
