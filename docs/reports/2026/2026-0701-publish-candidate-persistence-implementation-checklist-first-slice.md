# 2026-0701 Publish Candidate Persistence Implementation Checklist First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the implementation checklist for the first no-code publish candidate persistence code slice without adding code.

The future implementation should create stored candidate revision snapshots from the existing read-only publish readiness model. It should not implement approval mutations, public URLs, artifact copying, packaging, or rollback in the same slice.

## Helper Checklist

Candidate repository/helper functions:

- `app_no_code_publish_candidate_create_from_readiness_snapshot(...)`
  - Inputs: project key, source output key, expected artifact key, expected readiness state, actor principal, readiness snapshot.
  - Output: `{ok, candidate, error}`.
  - Stores publishable and blocked readiness snapshots as first-class candidate records.
  - Fails closed on artifact mismatch, readiness state mismatch, missing source output, non-`NO-CODE-RUNTIME`, or non-operator actor.

- `app_no_code_publish_candidate_list_for_source_output(...)`
  - Inputs: project key, source output key, limit.
  - Output: latest candidates for the source output, newest first.
  - Does not cross project/source-output boundaries.

- `app_no_code_publish_candidate_find(...)`
  - Inputs: project key, source output key, candidate id.
  - Output: one candidate or a fail-closed not-found result.
  - Does not expose artifact contents or public URLs.

## Storage Checklist

Candidate record fields:

- identity: candidate id, project key, source output key;
- artifact identity: artifact key, artifact archive path or identifier, artifact checksum if available;
- readiness snapshot: readiness state, blockers, screen count, action count, preview readiness;
- lifecycle: status `draft_candidate`, created by, created at, updated at;
- payload: `snapshot_json` as the canonical stored readiness snapshot.

First implementation constraints:

- Use a single candidate table/record contract before adding transition events.
- Store blocked candidates so operators can inspect why publish is not ready.
- Keep approval state as `draft_candidate` only; transition events come later.
- Do not copy artifacts or create public URLs.

## Focused Test Checklist

Repository tests before route wiring:

- creates a publishable candidate from a publishable readiness snapshot;
- creates a blocked candidate from a blocked readiness snapshot;
- rejects missing readiness snapshot;
- rejects artifact key mismatch;
- rejects expected readiness state mismatch;
- rejects non-`NO-CODE-RUNTIME` source output;
- rejects non-operator actor;
- lists latest candidates inside project/source-output boundary;
- reads a candidate inside project/source-output boundary;
- rejects read across project/source-output boundary.

Route/source-contract tests after repository is stable:

- candidate create route requires operator/admin permission;
- candidate create route requires CSRF;
- candidate create route passes expected artifact key and readiness state;
- Source Outputs no-code inspection links to candidate list;
- candidate detail shows stored readiness snapshot and blockers;
- approval action controls remain absent in this first persistence slice.

## Verification Gate

Before code implementation:

- Close Docker-backed verification gaps with `make sample28-no-code-schema-form-runtime-smoke`.
- Run focused candidate repository tests.
- Run route/source-contract tests only if routes are added.
- Run `make test` before local commit.

If Docker remains unavailable and the user explicitly accepts the verification gap, record that acceptance in the implementation report before adding code.
