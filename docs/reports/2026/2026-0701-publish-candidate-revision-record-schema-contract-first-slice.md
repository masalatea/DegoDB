# 2026-0701 Publish Candidate Revision Record Schema Contract First Slice

Status: `FIRST_SLICE_DONE`.

## Summary

Defined the first durable publish candidate revision record contract as a docs-only slice.

The intended first implementation object is a stored candidate snapshot, not an approval action and not a public runtime URL. It captures the current publish readiness signal for one generated no-code runtime artifact so later approval/revision work has a stable object to reference.

## Proposed Record

Working name: `no_code_publish_candidate_revisions`.

Required identity fields:

- `revision_id`: durable generated identifier.
- `project_key`: project boundary.
- `source_output_key`: expected first value is `NO-CODE-RUNTIME`.
- `artifact_key`: generated artifact/archive identifier or relative artifact path key.
- `created_at`: creation timestamp.
- `created_by`: operator/admin principal identifier when available.

Required readiness snapshot fields:

- `readiness_state`: `publishable` or `blocked`.
- `readiness_blockers_json`: list of blockers captured at candidate creation time.
- `preview_runtime_version`: expected first value is `no-code-runtime-v0`.
- `screen_count`: generated runtime screen count.
- `action_count`: generated runtime action count.
- `archive_available`: whether an artifact archive was available.
- `preview_available`: whether runtime preview files were available.

Initial lifecycle fields:

- `candidate_status`: first states are `draft_candidate` and `blocked_candidate`.
- `supersedes_revision_id`: nullable; reserved for later revision chain work.
- `snapshot_json`: full read-only readiness snapshot for audit/debug.

## Repository Contract

First implementation should expose a narrow repository/helper boundary:

- create candidate from current publish readiness snapshot;
- reject creation when project/source output/artifact identity is missing;
- mark blocked candidates as stored records, not failed writes;
- list latest candidate revisions for a project/source output;
- read one revision by `revision_id` within a project.

The helper should not approve, publish, copy artifacts, expose public URLs, or mutate generated runtime files.

## UI Contract

First UI slice should be operator/admin only:

- add a "Create candidate revision" action only from a `publishable` or `blocked` readiness surface;
- show created revision identity, status, artifact key, readiness state, and blockers;
- avoid approve/reject buttons until the approval transition is separately designed;
- link back to existing Source Outputs / publish readiness inspection.

## Verification Boundary

Before code implementation:

- rerun Docker-backed gaps if available;
- at minimum, add focused repository tests for create/list/read behavior;
- add Source Outputs/operator inspection coverage for the create action only if UI is included;
- run `make test` before committing any code slice.

Docker-backed verification is currently blocked by unavailable Docker daemon, so this slice intentionally avoids code changes.
