# No-Code Interface Usage And View-Layer Planning

Date: 2026-07-08

## Summary

#413 plans the next no-code product layer after the runtime-data stack cleanup. The current Web no-code kernel can already generate list/detail/form UI from database-first shared contracts and managed operations. The next layer should keep that data-flow-first model and add explicit interface usage intent, selectable view variants, and traceable navigation back to the settings that explain why a generated UI exists.

This is a planning slice only. It does not add schema changes, generator changes, UI behavior, tests, commits, or push.

## Current Kernel

The current no-code Web path is:

```text
DB schema
  -> canonical metadata
  -> shared contract
  -> managed operation
  -> screen-definition.json
  -> runtime-preview.json
  -> runtime-preview.html
```

This means the product can already derive a standard Web UI from the data and operation model. That is the core that should remain stable.

## Product Principle

No-code should not become a standalone visual builder with a hidden data model. The user should understand the data flow first:

```text
Where is this data from?
Why is this interface exposed?
Which operation can this screen submit?
Which Source Output artifact published it?
Which approval/current/alias route made it public?
```

Presentation should be adjustable, but it should sit above the interface and data-flow layers.

## Layer Model

```text
1. Data foundation
   DB schema / canonical table / canonical field

2. Interface contract
   shared contract / field role / operation role

3. Usage intent
   screen / external integration / sync / reporting / workflow / internal

4. Generated behavior
   list / detail / form / submit / refresh / approval / outbox review

5. Presentation variant
   table / card / compact / review / readonly / edit form / operator view

6. Traceability navigation
   links from generated UI back to source contract, fields, operations, artifacts, publish settings, and review state
```

## Interface Usage Intent

The same interface should be able to declare why it exists before choosing a presentation.

Candidate usage intents:

| Intent | Meaning |
| --- | --- |
| `screen` | Used to generate no-code Web list/detail/form UI. |
| `external_integration` | Used as a contract for another system, API, import/export, or webhook-style boundary. |
| `sync` | Used for App-local or offline/sync handoff. |
| `reporting` | Used for readonly dashboards, reports, review screens, or export-oriented views. |
| `workflow` | Used for approval, review, transition, or operator handoff. |
| `internal` | Used by generated internals and not intended as a direct user-facing surface. |

First implementation should probably record this as metadata near shared contracts, not in generated HTML. The generated runtime should consume only the resolved definition.

## View Variants

The same interface can have multiple views without changing the underlying contract.

Candidate view variants:

| Variant | Good first use |
| --- | --- |
| `standard_table` | Current generated list screen. |
| `detail_record` | Current generated detail screen. |
| `edit_form` | Current generated form screen. |
| `readonly_review` | Approval/review surfaces where mutation is not available. |
| `operator_review` | Outbox failure, retry, or needs-review workflows. |
| `compact_card` | Smaller dashboard-like summaries. |
| `public_readonly` | Customer or shared preview where fields/actions are intentionally restricted. |

First implementation should avoid a broad visual builder. A small enum-like variant model is enough to prove the layer.

## Traceability Navigation

Generated UI should make it easier to move from what the user sees to the settings that caused it.

Useful links:

| From generated UI | Link target |
| --- | --- |
| Screen title / summary | Source Output detail and `screen-definition.json`. |
| Contract name | Shared contract detail. |
| Field label | Canonical table/field detail and shared contract field role. |
| Action button / draft | Managed operation detail and managed operation fields. |
| Public current / alias preview | Publish candidate, current revision selection, and alias settings. |
| Submit/outbox status | Sync outbox detail, review/needs-review state, and retry path. |
| Runtime-data refresh | Read-only runtime-data endpoint boundary and selected current/alias revision. |

The first slice can be operator/admin side navigation. Public preview should stay careful and avoid exposing internal settings links to unauthorized users.

## Suggested Follow-Up Plan

| Plan | Work | Status | Rough effort |
| --- | --- | --- | --- |
| #414 | Interface usage intent inventory | TODO | 0.5 day |
| #415 | Minimal usage intent metadata contract | TODO | 0.5 - 1 day |
| #416 | View variant contract first slice | TODO | 1 - 2 days |
| #417 | Generated UI traceability links first slice | TODO | 1 - 2 days |
| #418 | Operator/admin navigation polish | TODO | 1 - 2 days |
| #419 | Closure and verification report | TODO | 0.5 day |

## First-Slice Recommendation

Start with #414 and #415. Define the metadata boundary first, then let later slices decide how much UI to expose. This keeps the architecture clean: interface usage explains why the interface exists, view variants explain how it is shown, and traceability explains how users move back to the data-flow roots.

