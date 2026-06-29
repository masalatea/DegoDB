# No-Code Runtime Sample07 Artifact Connection

Status: `IMPLEMENTED`

Date: 2026-06-29

## Scope

Connected the `no-code-runtime-json` Source Output artifact to `sample07-dbaccess-crud-basic`.

This slice uses the existing sample07 spine because it already proves:

- live table import for `todo_item`
- DataClass sync for the same physical table
- generated PHP DataClass / DBAccess output
- managed operation metadata for `update_todo_item`
- generated DBAccess binding for `TodoItemDBAccess::UpdateTodoItem()`

## Implementation

- Added sample07 shared contract metadata for `todo_item`.
- Marked `todo_item` as a no-code `managed-screen` contract.
- Marked `title`, `status`, and `body` as editable fields while keeping `id` read-only/key-shaped.
- Added sample07 `NO-CODE-RUNTIME` Source Output definition using `NoCodeRuntime` / `no-code-runtime-json`.
- Extended the sample07 pack check to create and publish `NO-CODE-RUNTIME`.
- Verified the generated artifact contains:
  - `screen-definition.json`
  - `runtime-preview.json`
  - `runtime-preview.html`
  - `README.md`
- Verified the generated screen definition includes one `todo_item` contract, list/detail/form screens, the expected fields, and the `update_todo_item` action.
- Verified the runtime preview renders three screens with `no-code-runtime-v0`.

## Design Note

This sample connection reinforces the current artifact split:

- Generated DataClass and DBAccess remain implementation-facing runtime outputs.
- Shared contract metadata carries no-code semantics such as `managed-screen` and editable field roles.
- Managed operation metadata carries the operation boundary used by generated no-code actions.
- The no-code runtime artifact is generated from shared contract + managed operation metadata, not from generated PHP classes.

## Next

The next no-code runtime slice is one persisted operation flow: route one enabled runtime action through the managed operation executor to App-local or server DBAccess persistence.
