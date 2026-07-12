# Sample19 Material Insight Refinement Lane Closure

Date: 2026-07-12

## Closed lane

The Sample19 material insight Q&A/UI outline refinement is complete.

Accepted capability:

- The fixture-backed `material_insight_v0` artifact now carries explicit Q&A answer categories.
- Each Q&A card exposes source evidence pointers as stable preview markers.
- The read-only UI outline groups screens into sections.
- Validation now rejects unsupported Q&A categories, missing/invalid evidence pointers, and unsupported UI sections.
- The preview remains read-only and marker-testable.

This gives enough structure for a downstream AI assistant, Codex/Claude-style prompt workflow, or fallback local scanner to inspect the material in a consistent way without pretending the whole application is generated.

## Decision

Do not spend the next slice on additional preview polish. The preview already has route evidence and stable markers.

Promote a first generated UI handoff preflight next:

- Input: the fixture-backed material insight `ui_outline`.
- Output target: existing no-code runtime metadata, initially read-only.
- Boundary: default-off, no AI calls, no generated execution, no mutation, no import/apply/build/publish.
- Goal: determine whether the existing runtime can consume enough of the `ui_outline` shape to render a small review surface, and where a custom/manual boundary must remain.

## Explicit non-goals

- No full sample conversion.
- No attempt to make every Sample19 screen no-code.
- No action execution route.
- No AI/Ollama integration in the product path.
- No browser re-smoke unless the route behavior changes. Any future browser smoke should run headless by default.

## Next active unit

#818: Sample19 generated UI handoff preflight.

The preflight should identify:

- Which `ui_outline` fields map directly to existing no-code metadata.
- Which fields need an adapter or should remain custom.
- The default-off flag and rollback point.
- Minimal PHPUnit/DOM contract tests.
- Whether a browser smoke is necessary; if it is, use headless mode.
