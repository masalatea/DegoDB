# Reusable Partial-Update Server Merge Policy First Slice

Status: `FIRST_SLICE_DONE`

Date: 2026-06-30

## Scope

Completed the first reusable partial-update server merge policy slice after the sample30 server-side sync processing proof.

The goal was to replace sample30's sample-specific full-row payload completion with a reusable read/merge/write policy for generated server DBAccess update operations.

## Implementation

- Added update-only merge behavior inside `app_managed_operation_server_dbaccess_execute_intent`.
- Detects when key + input do not cover the generated DataClass public properties.
- Derives a generated read method from the binding or update method name.
- Reads the existing server row before update when the update payload is partial.
- Merges existing row values with key fields and partial input, with partial input taking precedence.
- Keeps full-row payload updates on the existing path.
- Removes sample30's sample-specific `title` payload completion.
- Adds direct server DBAccess real coverage for partial update merge.
- Extends sample30 checker coverage so the generated server DBAccess handler preserves the existing title while updating status/note.

## Boundary Notes

This is not conflict resolution. The first slice only handles deterministic one-row update merge for generated server DBAccess when the sync intent carries key fields plus partial input.

Out of scope:

- Remote transport.
- Conflict resolution.
- Retry scheduling.
- Multi-row merge.
- Create/delete semantics.
- Visual builder.
- Native / Flutter output targets.

## Verification

- `php -l mtool/app/managed_operation_server_dbaccess_executor.php`
- `php -l mtool/scripts/lib/sample30_no_code_app_local_sync_demo_check.php`
- `php -l tests/Integration/ManagedOperationServerDbAccessRealCoverageTest.php`
- `make sample30-pack-runtime-test`
- `make test`

## Result

The first slice is complete. Generated server DBAccess update execution can now accept partial no-code update input, read the existing row, merge the partial input over it, and execute the generated full-row update method.

Sample30 no longer needs a sample-specific full-row payload completion for server-side processing.

## Next

Replan before selecting the next product-facing no-code slice. Candidate follow-ups include sync handoff visibility polish, an operator/admin workflow, or another small sync behavior pressure test.
