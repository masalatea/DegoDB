# Sample18 Generated Submit Idempotency Route Integration Preflight

Date: 2026-07-10
Plan: #595
Status: DONE

## Purpose

Define how the blocked sample18 generated submit route should call idempotency create-or-reuse before implementing route persistence.

This is a preflight only. It does not wire the route, persist idempotency rows from HTTP requests, or enable DBAccess mutation.

## Call Ordering

For valid generated submit requests only:

1. Normalize request payload.
2. Build dispatcher dry-run metadata.
3. Build dedupe key, payload fingerprint, and audit event.
4. Append audit event using the existing audit append helper.
5. Call idempotency create-or-reuse with:
   - `dedupe_key` from `dedupe_key_preview`;
   - `payload_fingerprint`;
   - `operation_key`;
   - `result=blocked`;
   - `failure_code=generated_submit_disabled`;
   - `first_audit_event_key` from `audit_append.item.event_key` when append succeeds, otherwise empty;
   - metadata containing audit append status, normalized payload, ignored fields, dispatcher bound fields, and mutation flags.
6. Return HTTP 409 `generated_submit_disabled`.

Audit append runs first so the first persisted idempotency record can reference the first audit event key when available.

## Response Metadata

Add an `idempotency` object only for valid blocked generated submit responses.

Expected shape:

- `ok`: boolean repository/helper result.
- `status`: `recorded`, `duplicate`, `failed`, or `skipped`.
- `created`: boolean.
- `dedupe_key`: same value as `dedupe_key_preview`.
- `item`: persisted record when available.
- `error`: repository/helper error when failed.
- `reason`: skip reason when skipped.

Top-level route result remains:

- HTTP status: `409`.
- `result`: `blocked`.
- `failure_code`: `generated_submit_disabled`.
- `mutation_enabled`: `false`.
- dispatcher `executed`: `false`.

## Skip Matrix

The first route integration slice must not persist idempotency rows for:

- non-POST method guard result;
- missing CSRF;
- invalid CSRF;
- validation failure;
- unknown operation;
- valid request without a dedupe key.

Those outcomes should either omit `idempotency` or return `idempotency.status=skipped` only if the response already carries generated-submit metadata.

## Failure Boundary

If audit append succeeds but idempotency persistence fails:

- route still returns HTTP 409;
- `audit_append.status=appended`;
- `idempotency.status=failed`;
- DBAccess mutation remains disabled.

If audit append fails but idempotency persistence succeeds:

- route still returns HTTP 409;
- `audit_append.status=failed`;
- `idempotency.status=recorded` or `duplicate`;
- `first_audit_event_key` is empty;
- DBAccess mutation remains disabled.

## Next

#596 should wire the valid blocked route path to idempotency create-or-reuse and add focused coverage for recorded, duplicate, failed, and skipped outcomes.
