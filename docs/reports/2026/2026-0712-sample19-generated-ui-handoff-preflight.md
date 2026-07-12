# Sample19 Generated UI Handoff Preflight

Date: 2026-07-12

## Decision

The next bounded material-to-UI step is a test-only adapter foundation from `material_insight_v0.ui_outline` into the existing no-code metadata shapes:

- `no-code-screen-definition-v0`
- `no-code-runtime-v0`

This should not create a new browser route or product action yet. It should prove that the already validated material insight can be translated into a no-code runtime preview shape using existing renderer contracts.

## Why this is the right next step

The previous slices already proved:

- Sample19 material can be normalized into a bounded fixture-backed insight artifact.
- The artifact is validated against source/basis identity, entity references, Q&A references, prohibited actions, evidence pointers, and UI sections.
- The read-only preview route is default-off, authenticated, rollbackable by flag, and has headless browser evidence.

Another preview polish pass would not reduce the main uncertainty. The main uncertainty is now the handoff: how much of `ui_outline` can be consumed by the existing no-code screen/runtime model without over-generating the application.

## Handoff mapping

| Material insight source | Existing no-code target | Decision |
| --- | --- | --- |
| `version = material_insight_v0` | traceability / source metadata | Carry as adapter source version. |
| `project_key = SAMPLE19` | `project_key` | Direct mapping. |
| `ui_outline.mode = read_only_review` | screen/action policy | Require read-only screens and empty generated actions. |
| `ui_outline.screens[].screen_key` | `screens[].screen_key` | Direct mapping for the first adapter. |
| `ui_outline.screens[].section` | `presentation_hint.field_groups` or extension metadata | Carry as review grouping metadata; do not force a visual component yet. |
| `ui_outline.screens[].purpose` | `screen_title`/description metadata | Carry as description; runtime renderer may still derive titles. |
| `ui_outline.screens[].fields` | `screens[].fields[].field_key` | Direct mapping to read-only display fields where possible. |
| `ui_outline.screens[].entity_refs` | preview data / traceability | Adapter-owned preview data, not durable storage. |
| `ui_outline.screens[].qa_refs` | extension slot or preview data | Keep as read-only Q&A review data; no action handoff. |
| `ui_outline.actions = []` | `screens[].actions = []` | Direct invariant. |
| `qa_cards[]` | extension/read-only preview data | Include only for read-only review evidence; no submit/action controls. |

## First implementation boundary

Create a small adapter function, likely near the material insight code, that accepts a validated artifact and returns a runtime preview payload or a screen definition plus runtime renders.

The adapter should:

- Require `app_material_insight_validate(...).ok === true`.
- Emit existing no-code versions, not a new runtime schema.
- Produce read-only fields.
- Produce no generated actions.
- Preserve traceability to material insight version, project key, source hash, basis proposal id, screen keys, Q&A refs, and evidence refs.
- Be covered by PHPUnit contract tests.

It should not:

- Add a route.
- Add a button.
- Run AI/Ollama.
- Persist artifacts.
- Import/apply/build/publish.
- Execute generated actions.

## Test plan for #819

Focused PHPUnit tests are enough for the first slice:

- Adapter rejects invalid material insight artifacts.
- Output version keys are `no-code-screen-definition-v0` and/or `no-code-runtime-v0`.
- Output project key is `SAMPLE19`.
- Output contains `material_entity_list` and `material_qa_cards`.
- All fields are read-only.
- All actions are empty.
- Traceability includes source/basis/evidence pointers.
- Render payload can be passed through existing no-code runtime helpers where applicable.

Browser smoke is not required unless a route is added. If a later slice adds browser evidence, run it headless by default.
