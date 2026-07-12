# AI material-to-UI replan checkpoint

Date: 2026-07-12

## Summary

#806 selects the first bounded AI material-to-UI investigation.

Use Sample19 rather than starting from a new domain. Sample19 already has:

- fixed synthetic source material: `sample/tutorials/sample19-json-first-content-model-demo/proposal/source/article.json`;
- a canonical schema snapshot for read-only comparison;
- a task-packet workflow for Codex/Claude;
- one validation facade/CLI;
- review artifacts that keep AI candidate ownership separate from Mtool-derived diff ownership;
- default-off read-only review routes and browser evidence.

That makes the next step narrow: derive one normalized material insight structure that can support both Q&A and a generated/read-only UI outline from the same source.

## Selected investigation

### Source material

Use the existing Sample19 synthetic article JSON and existing schema proposal/task-packet assets.

Do not introduce a new external document, PDF, website, customer data, or provider transmission in this lane.

### Q&A purpose

Bound Q&A to product-design questions that can be answered from the normalized structure, for example:

- which entities does this material imply?
- which fields and relationships are supported by source evidence?
- which assumptions or open questions block safe UI generation?
- which read-only UI sections could be generated from the same structure?

This is not open-ended chat over arbitrary documents.

### Normalized structure

Preflight a small `material_insight_v0` style contract derived from the validated Sample19 source/proposal/review pipeline. It should include:

- source identity and hashes;
- entities, fields, relationships, lifecycle hints, evidence pointers;
- questions and assumptions;
- Q&A answer cards with source-backed references;
- read-only UI outline candidates that reference the same entities and fields;
- validation status and mutation prohibition.

### Generated UI/action target

The first UI target should be read-only:

- no import/apply/build/publish;
- no generated DB/config mutation;
- no automatic route execution;
- default-off inspection route or fixture preview only;
- generated UI outline/cards are review material, not canonical metadata.

## Next lane

#807: Sample19 material insight contract preflight.

The preflight should define the exact JSON shape, validation stages, test evidence, feature flag/rollback boundary, and first fixture-backed implementation target before code is added.
