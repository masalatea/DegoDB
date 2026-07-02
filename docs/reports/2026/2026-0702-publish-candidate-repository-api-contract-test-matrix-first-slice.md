# 2026-0702 Publish Candidate Repository API Contract Test Matrix First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the focused repository/API contract test matrix for the future no-code publish candidate persistence implementation.

This kept the lane docs-only while Docker-backed verification was unavailable, but it removes ambiguity from the eventual code slice: the first implementation should be a repository-first create/list/find path with route/API surfaces still absent until the storage contract is proven.

## Repository Test Matrix

Create candidate from readiness snapshot:

- accepts `NO-CODE-RUNTIME` source output only;
- requires project key, source output key, artifact key, actor, and readiness snapshot;
- stores artifact archive path and checksum when present;
- stores screen/action counts and preview/archive readiness fields;
- stores blocking reasons and full readiness snapshot as JSON;
- initializes status as `draft_candidate`;
- rejects missing readiness snapshot;
- rejects expected artifact key mismatch;
- rejects expected readiness state mismatch;
- rejects non-operator actor;
- rejects non-`NO-CODE-RUNTIME` source output.

List candidates:

- lists newest first for `(project_key, source_output_key)`;
- returns only candidates for the requested project and source output;
- includes readiness state, artifact key, status, actor, and timestamps;
- does not include public URL, package path, approval decision, or rollback fields in the first slice.

Find candidate:

- finds by `(project_key, source_output_key, revision_id)`;
- returns readiness snapshot and blocking reasons as decoded arrays;
- rejects cross-project reads;
- rejects cross-source-output reads;
- returns a fail-closed not-found result without leaking other project/source-output data.

## Source/API Contract Matrix

Before repository tests pass:

- candidate create route is absent;
- approval action routes remain absent;
- Source Outputs inspection does not show candidate mutation controls.

After repository tests pass in a future implementation slice:

- Source Outputs inspection may show a read-only candidate list link;
- a create action may be introduced only with CSRF, project permission, expected artifact key, and expected readiness state checks;
- approval buttons remain absent until approval transition persistence is implemented.

## Fixtures

Minimum happy-path fixture:

- project key: `SAMPLE28`;
- source output key: `NO-CODE-RUNTIME`;
- artifact key from publish readiness;
- readiness state: `publishable`;
- screen/action counts copied from inspection summary;
- actor: operator/admin principal.

Minimum blocked fixture:

- same identity shape;
- readiness state: `blocked`;
- at least one blocking reason;
- create path should reject this until an explicit blocked-candidate policy is selected.

## Verification Gate

Before code implementation:

- close Docker-backed verification with `make sample28-no-code-schema-form-runtime-smoke`;
- add focused migration/bootstrap tests for the candidate table;
- add focused repository tests for create/list/find;
- add source-contract tests before exposing route/UI controls;
- run `make test` before committing code.

## Current Verification

- `make sample28-no-code-schema-form-runtime-smoke` passed after Docker restart on 2026-07-02.
- `make test` passed after Docker restart on 2026-07-02 (`311 tests, 10385 assertions, skipped 1`).
- Docs-only update; no code verification required.
