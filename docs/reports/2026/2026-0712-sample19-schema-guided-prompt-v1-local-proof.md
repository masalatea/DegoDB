# Sample19 Schema-Guided Prompt v1 Local Proof

Status: `DONE_REJECTED_ONE_GAP`

## Result

The schema-guided prompt v1 materially improved real local-model output but did not qualify G-L4. Attempt 1 reduced the previous nine structural errors to two missing relationship target entities. The single corrective retry resolved all proposal structure/reference/evidence errors and left only `declared_canonical_diff_mismatch`. The response remained rejected and did not enter review.

## Attempt 1

- response SHA-256: `65baa1f56181da2a68b6dd7f7fb22f5b5c586b1ff0e42c8235a48ed50b8c2157`;
- response bytes: 5,444;
- errors: `unknown_relationship_entity:article_author`, `unknown_relationship_entity:article_category`;
- improvement: structured fields, keys, evidence, relationship endpoints, and diff shape passed their validators.

## Attempt 2

- response SHA-256: `23ead0a43539f4cf989b418d8f7c1313493127bc6ac943e727b0e3e23a682171`;
- response bytes: 7,475;
- sole error: `declared_canonical_diff_mismatch`.

The accepted candidate portion contained `article_json_model`, `json_author`, and `json_category` with valid fields/keys/evidence/relationships. Independent diff derivation correctly found:

- `article_json_model`: unchanged;
- `json_author`: unchanged;
- `json_category`: unchanged;
- `article_public_summary`: remove.

The model instead declared only `json_author` and `json_category` as add, omitted connected relationship keys in those signatures, omitted the article entity diff, and omitted the canonical-only public summary removal.

## Decision

- Do not run a third attempt.
- Do not overwrite the declared diff and claim the raw response passed.
- The remaining issue is no longer proposal extraction shape. It is ownership duplication: the model is asked to declare a comparison that Mtool already derives authoritatively and independently.
- Before changing behavior, define whether AI output should be a candidate-only contract whose canonical diff is caller-derived, while the final review artifact records both raw-response hash and derived diff provenance. This must remain distinguishable from silent repair.

## Safety

Both attempts used local Ollama only, no credential, no external transmission, no DB/config mutation, and disposable ignored `work/tmp` bundles. Neither response entered the review route.

## Next

#774 is a canonical-diff ownership preflight. It must decide whether to split raw AI candidate acceptance from final review-artifact enrichment, preserving immutable raw bytes, transparent derivation, and fail-closed safety without asking the model to duplicate deterministic comparison logic.
