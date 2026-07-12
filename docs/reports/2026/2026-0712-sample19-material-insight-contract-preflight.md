# Sample19 material insight contract preflight

Date: 2026-07-12

## Summary

#807 fixes the first material-to-UI contract boundary before implementation.

The first slice is fixture-first and must not call an AI provider. It should reuse the existing Sample19 synthetic source and schema-proposal/review evidence, then add one normalized artifact that can drive both bounded Q&A and a read-only no-code UI outline.

## Contract

Use a versioned `material_insight_v0` artifact.

Required top-level shape:

- `version`: exact `material_insight_v0`;
- `project_key`: exact `SAMPLE19`;
- `source`: source material identity, logical path, media type, byte length, SHA-256, and root pointer;
- `basis`: validated proposal/review artifact identity, candidate hash, review hash, canonical snapshot hash, and derivation version;
- `entities`: proposed/reviewed entities with field, key, relationship, lifecycle, and evidence references;
- `qa_cards`: bounded question/answer cards derived from the same normalized entity/evidence structure;
- `ui_outline`: read-only no-code UI outline candidates derived from the same entities/fields/relationships;
- `prohibited_actions`: explicit no apply/import/build/publish/metadata mutation/route execution list;
- `validation`: stable stages, status, warnings, and `mutation_performed=false`.

## Q&A boundary

Allowed questions are product-design questions grounded in the normalized structure:

- what entities are implied?
- what fields and relationships are source-backed?
- what assumptions/questions remain?
- what read-only UI sections can be generated?

The artifact is not a general chat index, retrieval system, or arbitrary document Q&A service.

## UI boundary

The first UI target is an outline only:

- read-only list/detail/card candidates;
- entity/field references must point back to `entities`;
- no generated submit route;
- no operation execution;
- no canonical metadata update;
- no import/build/publish action.

## Validation stages

The first validator should fail closed through stable stages:

1. artifact decode;
2. version/project check;
3. source identity/hash check;
4. basis identity/hash check;
5. entity/reference integrity;
6. Q&A reference integrity;
7. UI outline reference integrity;
8. prohibited action and mutation check.

## First implementation lane

#808 should add the fixture-backed artifact and validator only.

It should not add:

- AI provider calls;
- Ollama execution;
- admin routes;
- browser smoke;
- DB/config metadata mutation;
- import, build, publish, or generated route execution.

Those can be promoted later only after the artifact contract is proven by fast tests.
