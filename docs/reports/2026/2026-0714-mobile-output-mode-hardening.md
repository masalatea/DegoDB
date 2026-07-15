# Mobile output mode hardening / mobile output mode hardening

Date: 2026-07-14

## Summary / summary

MW-12 defines mobile output modes based on the first-round feasibility studies and common requirement extraction.

Stable specification:

- `docs/mobile-output-modes.md`

Modes:

- `mtool_no_code`;
- `external_no_code`;
- `hybrid`.

## Decision / 判断

Mtool should expose output mode as an explicit product/config concept before deeper implementation.

The mode must not silently imply:

- native project creation;
- dependency installation;
- signing;
- store submission;
- offline sync;
- provider-specific AI execution behavior.

## Mode meanings / modeの意味

| Mode | Meaning |
| --- | --- |
| `mtool_no_code` | Use Mtool's own generated web/no-code/runtime output as the primary app surface. |
| `external_no_code` | Emit handoff/input artifacts for an external no-code/app framework/code-builder. |
| `hybrid` | Keep Mtool output and also emit external handoff artifacts for selected targets. |

## Required outputs / required outputs

`mtool_no_code` requires:

- app handoff packet;
- source artifact index;
- runtime/delivery readiness metadata;
- validation command map;
- non-goals.

`external_no_code` requires:

- app handoff packet;
- source artifact index;
- ownership boundary;
- selected target extension packet;
- validation command map;
- confirmation-required action list;
- non-goals.

`hybrid` requires both sets plus a canonical-surface statement.

## Validation direction / validation direction

Validation should check:

- known mode;
- selected target extension packet exists;
- source artifact refs and hashes;
- mutation/idempotency policy;
- token storage policy;
- offline sync contract when requested;
- native generation/signing/store non-goals;
- AI task packet confirmation/forbidden-guess rules.

## Next / 次

MW-12 is complete as a documentation/spec hardening slice.

The next active item remains MW-13:

- decide whether Mtool should expose UI-triggered artifact generation;
- do not implement execution UI until CSRF, output-dir, overwrite, audit, and failure policy are explicit.
