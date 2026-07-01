# No-Code Runtime Persisted Operation Flow

Status: `FIRST_SLICE_DONE`

Date: 2026-06-29

## Scope

Added the first persisted operation flow from no-code runtime action dispatch into the existing managed operation execution layer.

This is intentionally a bridge slice, not a UI renderer slice.

## Implementation

- Added `mtool/app/no_code_managed_operation_bridge.php`.
- Converted `no-code-runtime-action-intent-v0` into `managed-operation-sync-intent-v0`.
- Kept the no-code runtime dispatcher generic: it still accepts a callable dispatcher.
- Added a bridge dispatcher helper that delegates the generated sync intent to an injected executor.
- Verified no-code runtime dispatch can call the existing server DBAccess executor.
- Verified a real persisted flow against sample07 generated PHP DBAccess:
  - no-code runtime action intent
  - bridge to managed operation sync intent
  - server DBAccess executor
  - generated `TodoItemDBAccess::UpdateTodoItem()`
  - SQLite row update

## Boundary

This slice does not add a visual no-code app renderer yet. It proves the runtime action boundary can reach persistence without hand-coded screen logic.

## Next

The next no-code runtime slice is the minimal HTML/runtime renderer for generated list/detail/form screens.
