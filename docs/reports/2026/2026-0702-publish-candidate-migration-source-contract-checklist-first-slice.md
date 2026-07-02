# 2026-0702 Publish Candidate Migration Source-Contract Checklist First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the file-level migration and source-contract checklist for the future no-code publish candidate persistence implementation.

This keeps the work docs-only while Docker-backed verification is unavailable. The next code slice should implement this checklist only after verification can run or the verification gap is explicitly accepted.

## Migration Checklist

Candidate storage table:

- Working table name: `no_code_publish_candidate_revisions`.
- Primary identity:
  - `revision_id`
  - `project_key`
  - `source_output_key`
- Artifact identity:
  - `artifact_key`
  - `artifact_archive_path`
  - `artifact_checksum`
- Readiness snapshot columns:
  - `readiness_state`
  - `readiness_label`
  - `screen_count`
  - `action_count`
  - `preview_files_ready`
  - `artifact_archive_exists`
  - `blocking_reasons_json`
  - `snapshot_json`
- Lifecycle columns:
  - `status`
  - `created_by`
  - `created_at`
  - `updated_at`

Initial indexes:

- project/source-output newest-first lookup: `(project_key, source_output_key, created_at)`
- project/source-output/revision lookup: `(project_key, source_output_key, revision_id)`
- artifact identity lookup: `(project_key, source_output_key, artifact_key)`

Initial status scope:

- Only `draft_candidate`.
- Approval transition states and transition event tables are reserved for a later slice.

## Source-Contract Checklist

Repository/helper contracts:

- `app_no_code_publish_candidate_create_from_readiness_snapshot(...)`
- `app_no_code_publish_candidate_list_for_source_output(...)`
- `app_no_code_publish_candidate_find(...)`

Route/source contract checks:

- Candidate create route is absent until repository tests pass.
- Approval action controls remain absent in the first persistence slice.
- Source Outputs inspection may show candidate list/detail links only after list/read helpers exist.
- No public URL, packaging, rollback, or artifact copy controls are introduced.

Fail-closed assertions:

- Reject non-`NO-CODE-RUNTIME` source output.
- Reject missing readiness snapshot.
- Reject expected artifact key mismatch.
- Reject expected readiness state mismatch.
- Reject non-operator actor.
- Reject cross-project or cross-source-output candidate reads.

## Verification Gate

Before implementation:

- Close Docker-backed verification with `make sample28-no-code-schema-form-runtime-smoke`, or record explicit verification-gap acceptance.
- Run focused migration/bootstrap tests for the new table.
- Run focused repository tests for create/list/find.
- Run route/source-contract tests only if routes are added.
- Run `make test` before local commit when code is added.

## Current Verification

- `make sample28-no-code-schema-form-runtime-smoke` attempted on 2026-07-02 and blocked by unavailable Docker daemon.
- Docs-only update; no code verification required.
