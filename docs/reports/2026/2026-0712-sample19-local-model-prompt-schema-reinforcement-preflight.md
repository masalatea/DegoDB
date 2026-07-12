# Sample19 Local-Model Prompt/Schema Reinforcement Preflight

Status: `DONE_ONE_GAP`

## Result

Both rejected local-model responses used the canonical snapshot's compact comparison shape as the proposal entity shape. The prompt named required top-level members but did not define nested object field names and types. The one corrective retry listed semantic validator errors but still did not provide the missing structural contract, so the model repeated the same shape error.

## Evidence

Both attempts correctly emitted all required top-level keys and safe `proposal_only` / no-apply state. However:

- each entity contained `entity_key`, `field_keys`, `key_keys`, and `relationship_keys` rather than proposal `evidence`, `fields`, and structured `keys`;
- relationships used `from_entity_key` / `to_entity_key` and omitted endpoint fields/evidence rather than `from_entity`, `from_field`, `to_entity`, `to_field`, and evidence;
- canonical diff used a compact changed-field summary rather than `object_kind`, `object_key`, proposal/canonical signatures, evidence, and review note;
- attempt 2 changed response bytes but repeated the same structural interpretation and nine validator errors.

This is not evidence that the model cannot reason about Sample19. It is evidence that the current prompt exposes two different JSON shapes without a machine-readable nested output contract and the smaller model chose the shorter comparison shape.

## Required reinforcement

Create a new prompt version with a compact, generic shape schema that:

- defines every proposal collection's object keys and JSON types;
- distinguishes proposal `entities[].fields[]` / `entities[].keys[]` from canonical `field_keys` / `key_keys` signatures;
- defines evidence as a non-empty list of `{pointer,type,rationale}` objects under `/article`;
- defines relationship endpoint entity and field keys exactly;
- defines canonical diff entry keys and permitted categories;
- repeats `proposal_only`, `apply_supported=false`, and AI provenance requirements;
- includes placeholders, not Sample19 golden entity/field answers;
- remains small enough for the 7B local model and leaves source/canonical bytes unchanged;
- is checked offline by deterministic hash, placeholder, forbidden-golden-value, and shape-completeness tests before another call.

## Retry policy

The current prompt version exhausted its two attempts. A new run is allowed only after the schema-guided prompt receives a new version/hash and offline tests pass. That new version starts a new bounded run with one initial attempt and at most one corrective retry; it does not retroactively alter either rejected response.

## Next

#772 implements the generic compact response-shape schema and prompt v1 integration with offline tests only. It must not invoke Ollama or copy the golden proposal as an example answer.
