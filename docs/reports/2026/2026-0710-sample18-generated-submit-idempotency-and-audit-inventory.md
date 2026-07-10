# Sample18 Generated Submit Idempotency And Audit Inventory

Date: 2026-07-10
Plan: #585
Status: DONE

## Scope

This inventory defines idempotency and audit boundaries for sample18 generated submit. It does not add persistence, append audit rows, enqueue outbox items, or enable DBAccess mutation.

Current execution state:

- generated submit route: `POST /samples/sample18-task-board/no-code/generated-submit`
- valid generated submit: HTTP 409 `generated_submit_disabled`
- dispatcher: dry-run only, `executed=false`
- mutation: `mutation_enabled=false`

## Dedupe Boundary

The generated submit dedupe key should be deterministic and operation-scoped:

`sample18.generated_submit.{operation_key}.{payload_fingerprint}`

Fingerprint input:

- normalized operation key;
- normalized DBAccess-bound fields from `dispatcher_result.bound_fields`;
- actor/session identity when available;
- route path/version marker.

Rules:

- validation, unknown operation, method, and CSRF failures do not receive accepted dedupe keys;
- blocked valid requests may expose `dedupe_key_preview` but must not persist it as accepted;
- create/update/complete each use the same algorithm but cannot collide across operation keys;
- payload field order must be canonicalized before hashing;
- the key must remain stable across repeated identical generated submits.

## Audit Event Shape

Future audit event type:

`sample18.generated_submit.requested`

Target:

- `target_type`: `sample18_task_card`
- `target_key`: generated submit dedupe key or `operation_key` when no key is available

Required metadata:

- `operation_key`
- `curated_route_action`
- `db_access_function`
- `dispatch_state`
- `mutation_enabled`
- `executed`
- `failure_code`
- `dedupe_key`
- `payload_fingerprint`
- `ignored_input_fields`
- `normalized_payload`
- `dispatcher_bound_fields`

Result values:

- `blocked`: valid request reached dry-run dispatcher but mutation stayed disabled;
- `accepted`: future mutation accepted;
- `duplicate`: future accepted duplicate reused prior accepted result;
- `invalid`: validation or unknown operation;
- `unauthorized`: future policy/auth failure;
- `csrf_failed`: missing or invalid CSRF;
- `failed`: persistence or DBAccess failure.

## Persistence Boundary

Do not reuse the managed operation sync outbox directly for sample18 generated submit in this slice. It is useful as a dedupe pattern, but generated submit mutation needs its own route-local decision first because:

- sample18 generated submit is synchronous route work, not operator sync queue work;
- accepted/duplicate semantics must be stable before outbox reuse is considered;
- audit append can reuse the existing `app_audit_log_append()` shape after the event builder is covered.

First implementation should only build:

- dedupe key preview;
- payload fingerprint;
- audit event payload;
- response metadata.

It should not write anything.

## Response Boundary

Current blocked responses may add preview metadata:

- `dedupe_key_preview`
- `payload_fingerprint`
- `audit_event_preview`

But they must keep:

- HTTP 409;
- `failure_code=generated_submit_disabled`;
- `accepted=false`;
- `mutation_enabled=false`;
- `dispatcher_result.executed=false`.

## Test Matrix

Next focused tests should cover:

- identical create payloads produce identical dedupe key previews;
- operation key changes prevent collisions for otherwise similar payloads;
- field order changes do not change the fingerprint;
- validation/unknown/CSRF failures do not produce accepted dedupe keys;
- audit event preview includes operation, dispatcher, failure, ignored field, and payload metadata;
- route response remains blocked while exposing preview metadata.

## Next

#586 should add dry-run helper coverage for dedupe key and audit event preview. It should not append audit rows, persist idempotency rows, enqueue outbox items, or execute DBAccess mutation.
