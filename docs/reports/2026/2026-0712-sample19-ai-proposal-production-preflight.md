# Sample19 AI Proposal Production Preflight

Status: `DONE`

## Result

The repository has no approved general-purpose AI provider/model configuration or reusable LLM client. Existing provider settings are scoped to translation or identity and must not be treated as authorization to transmit Sample19 input to an AI service.

The next safe unit is therefore an offline request-envelope foundation. It may prepare and verify the exact source, prompt, output contract, and acceptance policy, but it must not contact a local or external model. A real AI call remains a separately authorized operation with an explicitly selected provider and model.

## Fixed input and privacy boundary

- Model context is limited to the checked-in synthetic source `sample/tutorials/sample19-json-first-content-model-demo/proposal/source/article.json`, the checked-in Sample19 canonical snapshot used for read-only diff comparison, and the versioned output instructions.
- Arbitrary uploads, production schemas, personal data, credentials, database rows, repository-wide context, and generated secret sidecars are excluded.
- The request manifest records logical filenames, media types, byte lengths, and SHA-256 values for the exact source and canonical-context bytes; the source also records its root pointer.
- Any later expansion beyond this fixed synthetic source requires a new data-handling review and explicit approval.

## Prompt and request reproducibility

- Store one versioned prompt template in the Sample19 proposal directory.
- Build the final prompt deterministically from the template plus the exact source bytes; do not add timestamps, host paths, credentials, or ambient repository state.
- Record template version and SHA-256, final prompt SHA-256, source SHA-256, proposal contract version, and request-envelope version.
- The offline builder emits the request envelope and hashes only. It has no network client, credential lookup, retry loop, persistence write, or apply path.

## Provider and provenance contract

A later real run must require explicit, non-default provider and model identifiers. They are deployment/run inputs, not hardcoded approval. Credentials remain environment or secret-sidecar values and are never written to the envelope, proposal, logs, fixtures, or repository.

Accepted AI provenance must truthfully include:

- `kind=ai_generated_proposal` and `ai_authored=true`;
- provider and exact model identifiers;
- prompt-template version/hash and final prompt hash;
- source hash and proposal contract version;
- attempt number and response SHA-256;
- provider request ID when one is returned;
- actual generation timestamp supplied by the controlled caller.

The deterministic golden fixture remains `ai_authored=false`; it must never be relabeled or reused as proof of an AI call.

## Structured-output acceptance

An AI response is accepted for review only when all of these independent checks pass:

1. The response is one JSON object with no executable wrapper.
2. `app_schema_proposal_decode()` and `app_schema_proposal_validate()` pass.
3. Project is exactly `SAMPLE19`; source metadata and SHA-256 match the fixed source bytes.
4. State is `proposal_only` and `apply_supported=false` as supplied by the model response. The caller must reject rather than silently repair unsafe values.
5. Evidence JSON Pointers remain under `/article`; entity, field, relationship, lifecycle, and DBAccess references are internally valid.
6. The declared canonical diff exactly matches the independently derived diff against the checked-in canonical snapshot.
7. Provenance matches the actual request/run metadata and cannot claim a provider/model that was not used.
8. The accepted artifact enters only the existing read-only validator/diff/review boundary. It gains no SQL generation, metadata persistence, approval, apply, import, DBAccess execution, or POST authority.

## Non-determinism, retry, and golden comparison

- Do not require byte-for-byte equality with the deterministic golden proposal; model wording and candidate ordering may vary.
- Compare contract invariants, source/evidence traceability, reference integrity, independently derived diff, and safety flags.
- The golden proposal is a semantic review baseline, not an expected AI answer and not training/prompt content by default.
- The first real proof permits one initial attempt and at most one explicitly recorded corrective retry for malformed/contract-invalid output.
- Each attempt keeps a distinct attempt number, response hash, validation result, and provider request ID. Never overwrite one attempt with another or present a corrected attempt as the original.
- Transport, timeout, refusal, invalid JSON, unsafe flags, provenance mismatch, source mismatch, or diff mismatch all fail closed. They do not trigger mutation or an unbounded retry.

## Zero-mutation proof required for the real run

The eventual qualification run must show:

- no proposal/config/user-database writes before, during, or after generation;
- no SQL, import, build, publish, approve, or apply call;
- accepted output is loaded by the same fixed-asset review validator and independently derived diff path;
- the review page still contains no form, button, script, execution binding, apply link, or review-route POST;
- runtime logs contain provider request metadata but no credential or full source/prompt body;
- the review feature is restored to default-off after evidence collection.

## Authorization boundary

No AI call is authorized by this preflight. Before a real call, the user must select or approve:

- provider and exact model;
- whether the provider is local or receives data externally;
- credential source;
- transmission of the fixed synthetic Sample19 source;
- any provider-specific retention/privacy settings that can be controlled.

## Next

#767 adds the provider-neutral, offline Sample19 request-envelope builder, versioned prompt template, deterministic hashes, and fail-closed tests. It must contain no network/provider adapter and no credential access. After that foundation passes, a separate plan can select a provider/model and request explicit authority for the first real AI generation proof.
