# Runtime-Data Active Query Result Count Token Closure

Date: 2026-07-07

Status: DONE

## Summary

#395 closes the active-query result count token lane after #394.

The accepted summary state is:

- Active Search / Filters / Sort / Page size / Rows state appears as compact tokens.
- The complete active query summary remains available through `aria-label`.
- Clear resets to token-free `No runtime data query applied.` text.

## Accepted Capability

Generated current/alias runtime controls now make a narrowed or paginated runtime-data result easier to interpret by showing both the active query shape and the matched row count in the same compact summary surface.

## Preserved Boundary

Unchanged:

- Runtime-data URL/query values
- Endpoint parsing
- `runtime-data.json` contract
- Sample data
- Mutation behavior
- Sync outbox behavior
- Artifact-key immutability

## Verification Baseline

The verification baseline remains #394:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The umbrella browser smoke completed through sample28, sample29, and sample31 public runtime browser smokes with `ok: true` outputs.

## Remaining Candidates

- Generated runtime mobile density review
- Local stack review before any push decision

## Notes

This closure keeps #394 as the latest code verification baseline. No code changes, tests, history rewrite, or push were performed for #395.
