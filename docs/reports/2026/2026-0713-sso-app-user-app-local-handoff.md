# SSO App User App-local Handoff

## Outcome

Plan #864 connects the generated server resolver result to the existing App-local identity and sync actor contract without turning cached client data into authority.

An App-local identity may bind `app_user_id` only from a successful server resolver result whose top-level and canonical actor IDs agree. The saved identity records `app_user_id_source=server-resolved-v1` and a binding timestamp. Existing safe-snapshot filtering continues to remove tokens, credentials, secrets, raw claims, and nested forbidden fields.

## Sync boundary

The actor snapshot includes the cached ID only when server provenance is present, and always marks it `correlation-only` with `server_revalidation_required=true`.

Server revalidation compares the client correlation ID with the `app_user_id` freshly established by the server resolver for that request. Missing resolution, missing server actor ID, stale ID, or mismatch fails closed. The accepted result returns the server ID, not a client-selected ID.

## Evidence

- server-resolved ID survives App-local SQLite save and restore;
- token material in the surrounding server response is not persisted;
- a successful sync actor revalidates against the server result;
- a client-only claim is omitted from the actor snapshot;
- inconsistent resolver evidence and stale actor IDs are rejected.

The full integration suite passed with 546 tests and 14898 assertions; one existing test is skipped.

## Next boundary

Plan #865 records driver qualification precisely. SQLite currently has the complete generated resolver execution and Source Output evidence. MySQL/MariaDB has live constraint import evidence and shared transaction runtime support, but the SSO resolver path still needs an explicit driver-level qualification decision before being advertised as fully supported. PostgreSQL/SQLite live constraint import scope remains explicit.
