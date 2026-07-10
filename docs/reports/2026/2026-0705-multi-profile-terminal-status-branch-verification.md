# Multi-Profile Terminal Status Branch Verification

Status: `DONE`.

Date: 2026-07-05.

## Context

#193 and #194 added deterministic terminal `done` and `failed` / `needs_review` status branch checks to the shared public runtime browser smoke path. The sample29 and sample31 public runtime smoke scripts delegate to the sample28 script with profile-specific environment variables, so the branch checks should apply to all three current no-code runtime domains.

## Verified

- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`

Both commands passed.

## Meaning

The live outbox status polling proof is now multi-profile:

- sample28: direct implementation and verification path.
- sample29: support-case profile inherits current/alias submit, pending timeout, terminal `done`, and terminal `failed` / `needs_review` checks.
- sample31: inventory-request profile inherits the same current/alias and terminal branch checks.

No additional sample-specific code was needed.

## Boundary

This is a verification/reporting step only. It does not add new runtime behavior and does not push.
