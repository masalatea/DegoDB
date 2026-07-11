# Sample18 Generated Submit Idempotency Audit Dry-Run Helper

Date: 2026-07-10
Plan: #586
Status: DONE

## Summary

Added dry-run idempotency and audit preview helpers for sample18 generated submit.

The route still returns blocked responses and does not append audit rows, persist idempotency state, enqueue outbox items, or execute DBAccess mutation.

## Changes

- Added canonical payload array handling for stable fingerprints.
- Added payload fingerprint generation from operation key and DBAccess-bound fields.
- Added operation-scoped dedupe key preview: `sample18.generated_submit.{operation_key}.{hash}`.
- Added audit event preview payloads with dispatcher, failure, normalized payload, ignored field, and bound field metadata.
- Added preview metadata to valid blocked generated-submit responses:
  - `dedupe_key_preview`
  - `payload_fingerprint`
  - `audit_event_preview`
- Extended focused PHPUnit checks for stable fingerprints, operation-scoped dedupe keys, invalid request behavior, and route response metadata.
- Extended HTTP smoke checks for dedupe/audit preview metadata.

## Guarantees

- No audit row is appended.
- No idempotency row is persisted.
- No outbox item is enqueued.
- No DBAccess mutation is executed.
- Valid generated submit remains HTTP 409 with `generated_submit_disabled`.
- `mutation_enabled=false` and dispatcher `executed=false` remain explicit.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-pack-runtime-test` (`4 tests`, `361 assertions`)
- `make sample18-http-runtime-smoke`
- `make sample18-no-code-public-runtime-disabled-action-smoke`
- `make test` (`382 tests`, `12117 assertions`, `Skipped: 1`)
- `git diff --check`

## Next

Promote #587 to close this helper lane and decide whether audit append persistence or mutation enablement gate coverage should come next.
