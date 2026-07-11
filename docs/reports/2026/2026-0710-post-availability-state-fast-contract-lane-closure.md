# Post Availability-State Fast Contract Lane Closure

Date: 2026-07-10

Status: `DONE`

## Summary

#697 closes the availability-state fast contract lane after #696. The generated runtime now exposes stable action availability markers, and fast tests can distinguish default disabled state from an in-memory enabled-candidate policy overlay.

## Accepted Capability

- Generated runtime action buttons expose `data-action-availability`.
- Policy failed checks can be exposed through `data-action-policy-failed-checks`.
- Sample18 fast tests prove default generated preview actions remain disabled.
- Sample18 fast tests prove only `create_task_card`, `update_task_card`, and `complete_task_card` become enabled candidates under the policy overlay.
- Selected-key and required-input fail-closed behavior remains covered.

## Decision

Promote #698: `Sample18 enabled-candidate browser smoke preflight`.

The next step should define browser smoke coverage for enabled-candidate UI state before changing generated defaults. It should still avoid broad default changes, surprise real mutation, and `reopen_task_card` / `delete_task_card` availability.

## Verification

Docs-only lane closure. #696 already ran:

- `php -l mtool/app/no_code_runtime.php`
- `php -l tests/Integration/Sample18MiniTaskBoardDemoTest.php`
- `make sample18-pack-runtime-test`
- `make test`
