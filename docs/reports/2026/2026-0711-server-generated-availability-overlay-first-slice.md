# 2026-0711 Server-Generated Availability Overlay First Slice

Status: `FIRST_SLICE_DONE`

## Implemented

- Added default-off environment flag parsing for `MTOOL_NO_CODE_SERVER_AVAILABILITY_OVERLAY`.
- Added a side-effect-free server readiness-to-policy builder.
- Preserved the existing generic overlay application function.
- Produced stable diagnostics for every failed gate.
- Kept `can_submit=false` and required `mutation_enabled=false`.

## Fail-Closed Matrix

An action remains disabled when any of these conditions apply:

- overlay flag off;
- authorization unevaluated or denied;
- route incompatible;
- readiness not `candidate_ready`;
- availability candidate false;
- readiness failure reasons present;
- guarded submit binding missing;
- mutation gate not disabled;
- Transaction Full capability gate missing.

## Coverage

Fast integration coverage verifies:

- the fully ready and authorized action becomes an enabled candidate;
- flag-off, auth-denied, readiness-failed, and transaction-gate-missing cases remain disabled;
- the generated policy applies to contract and screen actions;
- rendered HTML carries `availability=enabled` and `enabled=true` only for the passing server policy;

## Authenticated artifact response slice

The first server-consumable surface is now an authenticated, GET-only artifact-bound JSON response at `action-availability.json`.

- It resolves only an approved publish candidate for the requested artifact.
- It overlays current principal authorization onto the stored artifact definition, preserving artifact readiness metadata.
- It requires both `MTOOL_NO_CODE_SERVER_AVAILABILITY_OVERLAY` and `MTOOL_NO_CODE_TRANSACTION_FULL_GATE=transaction_full_v1` before reporting an action as enabled.
- It always reports `mutation_enabled=false` and does not accept a dispatcher, execute an operation, write an outbox item, or mutate a database.
- Non-GET, invalid binding, missing candidate, and missing principal outcomes fail closed.
- The same response contract is available through approved current and alias selectors; each response records its selector kind and resolved immutable artifact identity.
- `can_submit` remains false.

Final gate: 423 tests, 13,801 assertions, 1 skipped.

## Remaining

The builder is not yet wired into the normal Sample18 runtime-preview generation path. The next slice should add explicit flag/config plumbing and replace the browser smoke's manual policy mutation with the server-generated policy, without enabling real mutation.
