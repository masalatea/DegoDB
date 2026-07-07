# Runtime-Data Query Summary Token Style First Slice

Date: 2026-07-07

Status: DONE

## Summary

#392 adds compact token/chip rendering for generated current/alias runtime-data query summaries.

Active query parts now render as individual tokens:

- Search
- Filters
- Sort
- Page size

The full pipe-separated summary remains available as `aria-label`, so the readable accessibility fallback preserves the previous complete wording while the visible UI becomes easier to scan.

## Accepted Capability

- Generated current/alias runtime controls render active runtime-data query summary parts as compact tokens.
- The no-query state remains plain text: `No runtime data query applied.`
- Clear resets the visible summary and removes query tokens.
- Browser smoke coverage asserts both tokenized active state and reset state.

## Preserved Boundary

Unchanged:

- Runtime-data URL/query values
- Endpoint parsing
- `runtime-data.json` contract
- Sample data
- Mutation behavior
- Sync outbox behavior
- Artifact-key immutability

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The umbrella smoke completed through sample28, sample29, and sample31 public runtime browser smokes with `ok: true` outputs.

Full `make test` was not rerun because this slice only changes generated runtime UI rendering and smoke assertions; the cross-profile public runtime browser matrix was the higher-signal coverage for this change.

## Notes

This slice came after the #387-#390 query-summary readability work and the #391 local stack review. The new visual style keeps the existing textual summary contract available to assistive technology while making the on-screen active query state more compact for ordinary operator scanning.

Push was not performed.
