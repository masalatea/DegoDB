# Server-Backed Runtime Execution Boundary Inventory

Date: 2026-07-03
Status: FIRST_SLICE_DONE

## Summary

Server-backed no-code action execution is not starting from zero. The backend already has dispatch helpers that can turn a generated no-code runtime action into a managed operation sync intent and execute it through generated DBAccess.

The generated runtime preview, however, is still intentionally browser-local. Its buttons prepare and display an action intent draft; they do not submit a mutation request from the generated preview UI.

## Existing Capability

- `app_no_code_runtime_dispatch_action()` validates enabled actions and required key/input fields before calling a dispatcher.
- `app_no_code_managed_operation_dispatcher()` converts a runtime action intent into a managed operation sync intent and passes it to an executor.
- `app_managed_operation_server_dbaccess_execute_intent()` can execute the mapped generated DBAccess method.
- `NoCodeRuntimeTest` covers dispatch, disabled-action fail-closed behavior, missing/blank required-input fail-closed behavior, and managed-operation bridge execution.
- `ManagedOperationServerDbAccessRealCoverageTest` covers a real generated SQLite row update through the no-code runtime dispatch path.

## User-Facing Boundary Still Needed

Before generated runtime preview can execute real mutations, these boundaries should be explicit:

- Auth principal: which operator/user identity is allowed to execute a generated runtime action.
- Policy check: how the existing fail-closed managed-operation policy is applied to runtime execution.
- CSRF / request method: how browser form submission is protected.
- Target binding: which project, source output, approved runtime candidate, and operation map the request uses.
- Result refresh: whether a successful mutation reloads the current list/detail data or only reports execution status.
- Error surface: how validation, policy, executor, and DBAccess errors are shown without exposing unsafe internals.
- Audit trail: whether runtime execution should append an event beside publish candidate / alias lifecycle events.

## Recommended First Implementation Slice

The smallest safe implementation slice is a guarded sample/runtime execution endpoint that:

- Accepts a POST only.
- Requires an explicit runtime artifact / project binding.
- Reuses `app_no_code_runtime_dispatch_action()`.
- Starts with one update action shape already covered by sample28-style metadata.
- Returns structured JSON plus a conservative UI status message.
- Keeps disabled actions and missing required inputs fail-closed.

This should be implemented separately from this inventory. Push was not performed.
