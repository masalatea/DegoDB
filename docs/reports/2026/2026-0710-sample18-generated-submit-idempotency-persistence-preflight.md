# Sample18 Generated Submit Idempotency Persistence Preflight

Date: 2026-07-10
Plan: #592
Status: DONE

## Purpose

Define the storage and duplicate-response boundary for sample18 generated submit idempotency before any DBAccess mutation enablement.

This is a preflight only. It does not add a table, repository, helper, route persistence, or mutation.

## Storage Boundary

Use config DB storage owned by the sample18 generated submit lane.

Initial table candidate: `sample18_generated_submit_idempotency_records`.

Required logical fields:

- `dedupe_key`: deterministic key from `sample18.generated_submit.<operation_key>.<fingerprint-prefix>`.
- `project_key`: `SAMPLE18`.
- `operation_key`: `create_task_card`, `update_task_card`, or `complete_task_card`.
- `payload_fingerprint`: canonical payload fingerprint.
- `result`: first recorded route result, initially `blocked`.
- `failure_code`: initially `generated_submit_disabled`.
- `first_audit_event_key`: audit event key from the first recorded request when append succeeds.
- `duplicate_count`: count of later matching requests observed by the route.
- `metadata_json`: sanitized route metadata, normalized payload, dispatcher bound fields, and append state.
- `created_at` / `updated_at`.

The first implementation should expose repository-level create-or-reuse behavior and avoid route mutation until the storage contract is tested.

## Duplicate Response Boundary

Valid blocked duplicate requests should remain HTTP 409.

Response shape extension candidate:

- `idempotency.status`: `recorded` for first request, `duplicate` for reused request, `failed` when persistence cannot be reached, `skipped` when no valid dedupe key exists.
- `idempotency.dedupe_key`: same as `dedupe_key_preview`.
- `idempotency.record`: normalized persisted record when available.
- top-level `result`: remains `blocked`.
- top-level `failure_code`: remains `generated_submit_disabled`.
- `mutation_enabled`: remains `false`.
- dispatcher `executed`: remains `false`.

Method, CSRF, validation, and unknown-operation failures should not create idempotency records in the first implementation slice.

## Audit Interaction

For the first implementation slice:

- first valid blocked request appends the existing blocked audit event and records the `event_key` when available;
- duplicate valid blocked request may append a separate audit event only after the duplicate response shape is covered;
- audit append failure must not turn a blocked request into an accepted request;
- idempotency persistence failure must not enable DBAccess mutation.

If audit append succeeds but idempotency persistence fails, the route should report `audit_append.status=appended` and `idempotency.status=failed`.

If idempotency persistence succeeds but audit append fails, the route should report `idempotency.status=recorded` or `duplicate` and `audit_append.status=failed`.

## Next

#593 should add storage-backed repository/helper coverage for create-or-reuse behavior while keeping the route blocked and mutation disabled.
