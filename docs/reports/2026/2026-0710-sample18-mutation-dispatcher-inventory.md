# Sample18 Mutation Dispatcher Inventory

Date: 2026-07-10
Plan: #582
Status: DONE

## Scope

This inventory defines the boundary for future sample18 generated submit mutation dispatch. It does not enable DBAccess mutation.

Current route state:

- `POST /samples/sample18-task-board/no-code/generated-submit`
- auth: `web_lab_login`
- CSRF: `_csrf_token`
- valid payload result: HTTP 409, `failure_code=generated_submit_disabled`
- mutation: `mutation_enabled=false`
- browser coverage: public runtime guarded click reaches blocked feedback

## Operation Mapping

| Operation | Curated action | DBAccess function | Key fields | Client fields | Server/fixed fields |
| --- | --- | --- | --- | --- | --- |
| `create_task_card` | `create` | `InsertTaskCard` | none | `title`, `body`, `assigned_to`, `priority`, `due_date` | `status=todo`, `completed_at=null`, `updated_at=now` |
| `update_task_card` | `update` | `UpdateTaskCard` | `id` | `title`, `body`, `status`, `assigned_to`, `priority`, `due_date` | `completed_at` derived from status, `updated_at=now` |
| `complete_task_card` | `complete` | `CompleteTaskCard` | `id` | none | `status=done`, `completed_at=now`, `updated_at=now` |

`reopen_task_card` and `delete_task_card` remain out of generated dispatcher scope until a DBAccess function or custom adapter boundary exists.

## Dispatcher Boundary

The future dispatcher must be separate from request normalization:

- request wrapper owns HTTP method, auth, CSRF, and response status;
- normalizer owns operation lookup, field trimming, type normalization, ignored field reporting, and validation errors;
- dispatcher owns mapping the normalized payload to the DBAccess-bound shape and, only in a later slice, executing DBAccess;
- response builder owns accepted/blocked/error JSON and UI-facing failure codes.

The first implementation slice should add a helper that returns a DBAccess-bound payload summary without executing mutation.

## Gates Before Mutation

Before `mutation_enabled=true`, all of these must be explicit:

- auth and policy: generated route must require the same or stricter guard than the curated route;
- CSRF: missing and invalid CSRF remain 403 and never reach dispatcher execution;
- validation: unknown operation and invalid payload never reach dispatcher execution;
- idempotency: define duplicate handling for create/update/complete generated submits;
- audit: record accepted, duplicate, rejected, unauthorized, validation failure, and execution failure outcomes;
- stale data: decide whether update/complete require current row existence and optional version/updated-at checks;
- response shape: accepted, duplicate, validation, missing record, DB failure, and blocked responses must be stable JSON;
- rollback: generated mutation can be disabled by gate without removing route or UI metadata.

## Test Matrix

Focused tests before mutation:

- helper maps normalized create payload to `TaskCardData`/DBAccess-bound fields without execution;
- helper maps update payload including derived `CompletedAt`;
- helper maps complete payload including fixed `Status=done`;
- invalid and unknown requests do not call the dispatcher helper;
- CSRF failures do not call the dispatcher helper;
- route still returns `generated_submit_disabled` while helper is dry-run only;
- HTTP smoke proves no row is inserted/updated/completed while mutation is disabled.

Later mutation-enable tests:

- accepted create inserts one row and returns accepted JSON;
- duplicate create is duplicate-safe;
- update/complete missing id returns missing record;
- audit rows are appended for accepted and failed outcomes;
- public runtime guarded click reports accepted/duplicate/failure feedback without relying on browser-only state.

## Next

#583 should add the dry-run dispatcher helper and focused coverage. It should not execute DBAccess mutation or change generated route acceptance from blocked to accepted.
