# Sample19 material insight Q&A/UI refinement preflight

Date: 2026-07-12

## Summary

#815 fixes the next small refinement boundary for Sample19 material insight.

The first material-to-UI slice already proves the safe route and headless browser boundary. The next increment should improve usefulness without expanding scope: make the existing Q&A cards and UI outline easier to inspect.

## Refinement scope

Add fixture-backed presentation metadata only:

- `answer_category` on each Q&A card;
- per-card evidence pointer rendering;
- UI outline `section` grouping;
- stable HTML markers for category, evidence pointers, and section.

Recommended categories:

- `structure`: entity inventory and normalized structure;
- `relationship`: source-backed relationship explanation;
- `ui_outline`: read-only UI candidate explanation.

Recommended UI sections:

- `entity_review`;
- `qa_review`.

## Contract boundary

The refinement may change `material_insight_v0` additively, but must preserve:

- same source/proposal/canonical fixture inputs;
- source/canonical hash binding;
- read-only route;
- empty `ui_outline.actions`;
- explicit prohibited actions;
- `mutation_performed=false`;
- no AI provider or Ollama call;
- no DB/config metadata mutation;
- no import/apply/build/publish;
- no generated submit/action execution.

## Validation additions

Fast validation should fail closed for:

- missing or duplicate answer categories if categories are malformed;
- blank Q&A evidence pointers;
- UI outline screens without section;
- unknown section values only if an allowlist is introduced.

Keep validation small. Do not create a general ontology, search index, or arbitrary document-Q&A contract.

## Render/test additions

Preview HTML should add stable markers:

- `data-material-insight-qa-category`;
- `data-material-insight-qa-evidence`;
- `data-material-insight-ui-section`.

Fast tests should assert:

- the three expected categories exist;
- evidence pointers render for each Q&A card;
- the two expected UI sections exist;
- forbidden form/button/script/POST/generated execution controls remain absent.

Browser smoke is not required for this refinement unless the render route behavior changes.

## Next lane

#816: implement the fixture-backed Q&A/UI outline refinement with fast tests.
