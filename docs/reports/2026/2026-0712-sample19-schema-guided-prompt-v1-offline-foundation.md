# Sample19 Schema-Guided Prompt v1 Offline Foundation

Status: `FIRST_SLICE_DONE`

## Result

The Sample19 request envelope now embeds a compact generic nested response-shape contract under prompt version `sample19-schema-proposal-v1`. It directly distinguishes proposal objects from canonical comparison signatures without embedding Sample19's golden entity or field answers.

## Implementation

- Added `schema-proposal-v1-shape.json` with all proposal top-level collections and exact nested object keys/types.
- Defined non-empty evidence object shape, structured entity fields/keys, exact relationship endpoints, lifecycle evidence, target references, and complete canonical diff entries.
- Added `{{OUTPUT_SHAPE_JSON}}` to the versioned prompt and included its SHA-256 in the deterministic request envelope.
- Updated the local runner to use the new shape-guided request builder.
- Added fail-closed required-path validation so incomplete shape contracts cannot produce an envelope.

## Anti-answer-copy boundary

The shape uses generic angle-bracket placeholders. It contains neither `json_author` nor `article_json_model`, and does not include the deterministic golden proposal as an example. Source and canonical bytes remain the only Sample19 content supplied to the model.

## Verification

- PHP syntax checks: passed.
- Deterministic prompt/source/canonical/shape hash coverage: passed.
- Missing placeholder and incomplete nested shape rejection: passed.
- Full `make test`: 455 tests, 14,067 assertions, 1 skipped.
- No Ollama or other AI call was made in #772.

## Next

#773 runs the first bounded local proof for the new prompt version with Ollama `qwen2.5-coder:7b`, using the same local-only/no-credential/no-mutation boundary. It permits one initial attempt and at most one corrective retry, with both responses independently hashed and validated.
