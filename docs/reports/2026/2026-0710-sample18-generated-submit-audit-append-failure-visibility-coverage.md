# Sample18 Generated Submit Audit Append Failure Visibility Coverage

Date: 2026-07-10
Plan: #590
Status: FIRST_SLICE_DONE

## Summary

#590 adds focused coverage for sample18 generated submit audit append failure visibility.

This slice does not change production route behavior. It locks the existing failure response contract: a valid generated submit remains blocked with HTTP 409 `generated_submit_disabled`, dispatcher dry-run metadata remains non-mutating, and audit append failure is returned through `audit_append.status=failed`.

## Covered Behavior

- A valid blocked generated submit with an unavailable config DB returns HTTP 409.
- The response keeps `ok=false`, `accepted=false`, `result=blocked`, and `failure_code=generated_submit_disabled`.
- Dispatcher metadata keeps `dispatch_state=dry_run`, `executed=false`, and `mutation_enabled=false`.
- Top-level `mutation_enabled` remains `false`.
- `audit_append.status` is `failed`, `skipped=false`, `item=[]`, and `error` is non-empty.

## Boundaries Kept

- No DBAccess mutation is enabled.
- No generated submit is accepted.
- No idempotency persistence row is added.
- HTTP smoke behavior for successful audit append remains covered by #588.

## Verification

- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`: `OK (6 tests, 406 assertions)`
- Full `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 384, Assertions: 12162, Skipped: 1.`
- `git diff --check`

## Next

#591 should close audit failure visibility and decide whether the next promoted slice is duplicate/idempotency persistence or mutation enablement gate coverage.
