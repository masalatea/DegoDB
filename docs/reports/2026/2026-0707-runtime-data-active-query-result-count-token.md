# Runtime-Data Active Query Result Count Token

Date: 2026-07-07

Status: DONE

## Summary

#394 adds a `Rows: N` token to active generated runtime-data query summaries.

When query or pagination state is active and `runtime-data.json` returns pagination metadata, the generated runtime summary now shows the total matched row count as another compact token. Clear still returns to the token-free `No runtime data query applied.` state.

## Accepted Capability

- Active runtime-data query summaries include result count context.
- Pagination-only active state also shows the total row count.
- No-query reset state remains unchanged.
- Public browser smoke coverage asserts the active `Rows:` summary token and token count.

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

The umbrella browser smoke completed through sample28, sample29, and sample31 public runtime browser smokes with `ok: true` outputs.

Full `make test` was not rerun because this slice only changes generated runtime UI summary rendering and smoke assertions; the cross-profile public runtime browser matrix was the higher-signal coverage for this change.

## Notes

This is a narrow continuation after the #392/#393 token summary polish. It makes empty or narrowed results easier to interpret without changing query behavior or endpoint contracts.

Push was not performed.
