# Reusable Generated UI Authority Policy Foundation

Status: `FIRST_SLICE_DONE`

## Result

Generated UI authority is no longer hardcoded to Sample18 at the execution-binding boundary.

The reusable policy requires:

- `MTOOL_NO_CODE_GENERATED_UI_EXECUTION_ENABLED` to be explicitly true;
- one or more normalized `PROJECT_KEY:action_key` entries in `MTOOL_NO_CODE_GENERATED_UI_EXECUTION_ALLOWLIST`.

The resolver returns a project-specific action list. Current/alias execution bindings inject only that list and its enabled state. Static artifact previews continue to omit execution URL, UI authority state, and allowlist.

## Normalization and fail-closed behavior

- project keys use the existing project-key normalization;
- action keys normalize to lowercase and must match `[a-z][a-z0-9_]*`;
- malformed entries and entries for another project are ignored;
- duplicate action entries collapse to one value;
- global switch off means the reusable allowlist grants nothing;
- an empty resolved action list means UI execution is disabled.

## Sample18 compatibility

`MTOOL_SAMPLE18_GENERATED_UI_EXECUTION_ENABLED` remains a temporary compatibility input. When true for Sample18 it resolves to `create_task_card` through the same normalized policy result consumed by binding code.

This preserves the existing Sample18 integration smoke while removing the binding's fixed `['create_task_card']` value. New projects/actions use the reusable global switch and project/action allowlist.

## Sample29 boundary

The policy foundation can resolve `SAMPLE29:update_support_case`, but no Sample29 stack enables it yet. Live availability still lacks the managed-outbox execution model, so browser execution remains fail-closed until #752 and #753.

## Configuration wiring

The root admin Compose service now passes through the reusable switch and allowlist variables. Sample-specific stacks may opt in explicitly; absence remains the default.

## Verification

- PHP syntax checks passed for the runtime page and updated contract test.
- Policy coverage verifies default-off, Sample18 legacy compatibility, Sample18/Sample29 reusable entries, normalization, deduplication, malformed-entry rejection, and global-switch-off behavior.
- Binding coverage verifies Sample29 receives only `update_support_case` when explicitly configured.
- Static artifact binding coverage verifies no UI execution authority fields are injected.
- Full suite: 424 tests, 13,861 assertions, 1 skipped.

## Next

#752 adds execution-model and capability-aware availability evaluation. It must keep availability GET-only and zero-dispatch, preserve Sample18 Transaction Full behavior, and model Sample29 durable outbox enqueue/recovery without pretending it is a direct transaction.
