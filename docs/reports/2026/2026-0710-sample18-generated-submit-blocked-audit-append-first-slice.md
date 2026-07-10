# Sample18 Generated Submit Blocked Audit Append First Slice

Date: 2026-07-10
Plan: #588
Status: FIRST_SLICE_DONE

## Summary

#588 adds audit append persistence for valid blocked sample18 generated submit requests.

The generated submit route still returns HTTP 409 `generated_submit_disabled`, keeps `mutation_enabled=false`, and keeps dispatcher `executed=false`. This slice does not enable DBAccess mutation, idempotency persistence, outbox enqueue, or generated action success.

## Implemented Behavior

- Valid blocked generated submit requests now append `sample18.generated_submit.requested` audit rows through the existing config DB audit repository.
- The route response exposes `audit_append.status=appended` with the appended audit item when audit append succeeds.
- `actor_login_id` is filled from the authenticated principal before append.
- The existing `audit_event_preview`, dedupe key preview, payload fingerprint, dispatcher metadata, ignored fields, normalized payload, and bound DBAccess field metadata are carried into the persisted audit metadata.
- Method, CSRF, validation, and unknown-operation failures remain fail-closed and do not append audit rows in this slice.

## Boundaries Kept

- DBAccess mutation remains disabled.
- Generated submit acceptance remains blocked.
- Runtime guarded clicks still render blocked feedback only.
- No duplicate/idempotency persistence row is added yet.
- Audit append failure response behavior is not expanded beyond returning the existing append result shape.

## Verification

- `php -l mtool/app/lab_sample18_task_board_page.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `php -l mtool/scripts/check_sample18_task_board_http_smoke.php`
- `make sample18-pack-runtime-test`: `OK (5 tests, 389 assertions)`
- `make sample18-http-runtime-smoke`: `OK`
- `make sample18-no-code-public-runtime-disabled-action-smoke`: `OK`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 383, Assertions: 12145, Skipped: 1.`
- `git diff --check`

## Next

#589 should close the blocked audit append lane and decide the next promoted slice:

- audit append failure visibility coverage;
- duplicate/idempotency persistence for generated submit;
- or mutation enablement gate coverage while preserving explicit disabled defaults.
