# Review Workflow Repository Decoded Payload Fallback Lane Closure

Date: 2026-07-09

Status: `DONE`

## Summary

#524 closes the review workflow repository decoded payload fallback lane.

The accepted capability is focused coverage that malformed stored JSON payloads fall back to empty arrays in the read model.

## Accepted Capability

- Malformed stored `audit_event` JSON decodes to an empty array.
- Malformed stored `metadata_json` decodes to an empty array.
- Identity fields remain readable when payload JSON is malformed.

## Still Parked

- Push.
- Changing `review_source_output_artifact` availability to `available`.
- Generated button execution.
- Approval, rejection, cancellation, supersede, rollback, publish request, or adapter execution.

## Verification

#524 is docs-only.

- `git diff --check`

## Next Candidate

#525 should checkpoint the no-push local stack after decoded payload fallback and decide whether to pause local commits or continue only with another named non-executable hardening lane.
