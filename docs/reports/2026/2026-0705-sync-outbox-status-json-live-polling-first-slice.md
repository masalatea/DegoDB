# Sync Outbox Status JSON Live Polling First Slice

Date: 2026-07-05

## Status

Done locally. Not pushed.

## Context

After the third-domain runtime submit/processing confidence lane and the local commit stack review, the next product-facing gap was live tracking after a generated runtime submit. The generated runtime already shows the accepted submit result, outbox detail path, and manual refresh guidance. Adding browser polling directly would have mixed UI timing, auth behavior, endpoint shape, and mutation boundaries in one step.

## Implemented Slice

- Added `project_sync_outbox_status_json` at `/projects/{project_key}/sync-outbox/{dedupe_key}.json`.
- Reused the existing admin/config role gate and audited `source_output.download` project permission boundary.
- Returned a compact read-only status payload with status, processing handoff, retry eligibility summary, attempts, last error, operation key/type, detail path, and updated timestamp.
- Kept stored sync intent payloads out of the JSON response.
- Registered the route in the dispatcher and auth-required route list.
- Added contract tests for routing, auth-required visibility, project authorization metadata, and the no-intent status payload.

## Boundary Notes

- This endpoint does not process sync outbox items inline.
- This endpoint does not retry failed work.
- This endpoint does not expose the stored intent body.
- Generated runtime auto-polling is intentionally left for the next slice, now that a narrow read-only status contract exists.

## Next Candidates

- Wire generated runtime submit results to poll the status JSON route until `done` / `failed` / timeout.
- Add browser smoke coverage for the polling transition from accepted submit to completed status.
- Decide whether public runtime users should see the admin detail link, a user-facing status summary, or both.

