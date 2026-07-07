# Persistent Usage Intent Metadata First Slice Plan

Date: 2026-07-08

## Summary

#420 chooses the next implementation lane after #419. The #414-#419 slice proved that no-code screen definitions can carry derived interface usage, view variants, and traceability targets without adding a visual builder or changing public preview boundaries.

The next step should promote the most useful part of that derived model into persistent/editable metadata: interface usage intent.

## Decision

Start with persistent usage intent metadata, not Mtool self-replacement and not a broad visual builder.

Reason:

- Usage intent explains why an interface exists.
- View variants should depend on usage intent.
- Traceability links are more useful once usage intent is explicit.
- Mtool self no-code replacement is a final dogfooding program and should wait until the smaller metadata layers are stable.

## First Slice

Add a minimal contract for interface usage intent near shared contract metadata.

Candidate intents:

| Intent | Meaning |
| --- | --- |
| `screen` | Generates no-code Web screens. |
| `external_integration` | Used by API/import/export/webhook-style boundaries. |
| `sync` | Used by App-local/offline/sync handoff. |
| `reporting` | Used by readonly report/review/export surfaces. |
| `workflow` | Used by approval/review/transition/operator handoff. |
| `internal` | Used by generated internals and not directly exposed. |

## Implementation Shape

Prefer a small additive field or sidecar metadata path that can be read into `contract_metadata` and then into `screen-definition.json`.

First implementation should:

- Preserve existing `no_code_role=managed-screen`.
- Derive `screen` from `no_code_role=managed-screen` when no explicit usage intent exists.
- Derive `sync` from existing sync/app-persistence roles where appropriate.
- Let explicit usage intent override derived intent after validation.
- Keep public runtime previews free of internal admin links.
- Extend tests around shared contract metadata, screen definition, and no-code operator inspection.

## Suggested Follow-Up

| Plan | Work | Status | Rough effort |
| --- | --- | --- | --- |
| #421 | Persistent usage intent schema/repository inventory | TODO | 0.5 day |
| #422 | Add minimal usage intent persistence/normalization | TODO | 0.5 - 1 day |
| #423 | Screen definition explicit usage intent read-through | TODO | 0.5 - 1 day |
| #424 | Admin/operator usage intent display polish | TODO | 0.5 - 1 day |
| #425 | Closure and verification | TODO | 0.5 day |

## Parked Final Program

Mtool self no-code replacement is recorded as a parked final dogfooding/replacement program. It should begin with an inventory of Mtool's admin/lab/source-output screens, then replace low-risk read/review surfaces with generated no-code variants before considering edit/build flows.

