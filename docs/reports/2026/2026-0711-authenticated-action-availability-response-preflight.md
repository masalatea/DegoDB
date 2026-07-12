# 2026-0711 Authenticated Action Availability Response Preflight

Status: `DONE`

## Purpose

Provide a server-owned, authenticated, read-only action availability response for public no-code runtime UI. It must expose candidate presentation decisions without sharing the mutating execution endpoint.

## Route Shape

Add GET-only companions for the existing approved-candidate selectors:

- artifact-bound action availability;
- current approved revision action availability;
- approved alias action availability.

The routes reuse existing project, artifact, revision, and alias validation. They require `app_auth_principal()` and never accept an action payload.

## Server Flow

1. Resolve the approved candidate using the same selector boundary as public runtime data/execution.
2. Load the candidate's artifact-bound screen definition.
3. Build a fresh authorization-aware definition from current project metadata and the authenticated principal.
4. Apply authorization policy to the artifact definition while retaining artifact readiness metadata.
5. Build the server readiness overlay using the explicit overlay flag and Transaction Full capability input.
6. Return flattened action availability diagnostics.

No dispatcher, outbox, DBAccess mutation, audit append, or idempotency write is called.

## Response Contract

Top-level fields:

- `ok`;
- `contract_version=server-action-availability-v1`;
- `project_key`;
- selection kind, alias, artifact key, and revision id;
- `overlay_source=server_readiness_v1`;
- `overlay_flag_enabled`;
- `transaction_full_gate`;
- `actions`;
- `error`.

Each action includes:

- action and operation keys;
- server availability;
- authorization evaluated/allowed state;
- failed checks;
- readiness state and candidate flag;
- `can_submit` and readiness failure reasons;
- submit route presence;
- guarded binding state;
- `mutation_enabled`, which must remain false in this lane.

## Failure Policy

- non-GET: 405;
- missing principal: 401/403 according to the existing auth boundary;
- invalid project/selector binding: 409;
- approved candidate not found: 422;
- malformed artifact/readiness/policy: 200 with affected actions disabled only if the candidate and definition remain readable; otherwise fail the response without mutation.

## Verification

- pure response helper matrix;
- artifact/current/alias selector parity;
- auth denied and flag-off cases;
- fully ready candidate enabled presentation;
- failure readiness and missing Transaction Full gate remain disabled;
- dispatcher spy proves zero calls;
- JSON response always reports `mutation_enabled=false` for promoted candidates.

## Next

Implement the response helper and one artifact-bound route first. Add current/alias routes only after the shared helper contract passes.
