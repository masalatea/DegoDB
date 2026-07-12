# Sample19 Local AI Generation First Proof

Status: `DONE_REJECTED`

## Result

The first real AI generation proof ran locally with Ollama `qwen2.5-coder:7b`. No data was transmitted externally and no credential was used. Both the initial response and the single permitted corrective retry were rejected by the existing proposal contract, so G-L4 remains unqualified and no AI response entered the review artifact boundary.

## Execution boundary

- Provider: `ollama-local` at `127.0.0.1:11434`.
- Exact model: `qwen2.5-coder:7b`, already installed locally.
- Input: fixed synthetic Sample19 source, checked-in canonical snapshot, and versioned prompt.
- Output mode: JSON, temperature 0, context 32,768.
- Credentials: none.
- External transmission: none.
- Database/config metadata writes, SQL, import, build, publish, approve, and apply: none.
- Disposable proof bundles: ignored `work/tmp`, not repository artifacts.

## Attempt evidence

Attempt 1:

- response SHA-256: `f53c1b3717b84439148e95d64b87e072105df105c97d2b84d4ca97dce3c40110`;
- response bytes: 2,637;
- generated JSON completed normally;
- acceptance: rejected.

Attempt 2 was the single bounded corrective retry. It received the prior rejected response and exact validator errors:

- response SHA-256: `0772f72e83383585ba87e322feff8721f19fa0eda2ec054d075915f06a46fa3d`;
- response bytes: 2,637;
- response differed from attempt 1 but retained the same contract failures;
- acceptance: rejected.

Both attempts failed on the same nine errors:

- missing entity evidence for four entities;
- unknown entity references and missing evidence for two relationships;
- missing evidence for the declared diff.

Neither attempt requested unsafe apply state. The rejection is a response-shape/evidence quality gap, not a provider transport failure or a mutation-safety failure.

## Runner

`mtool/scripts/run_sample19_local_ai_proposal.php` fixes localhost endpoint/model/options, creates truthful request/run hashes, supports only attempt 1 plus one retry bundle, invokes the existing response acceptance boundary, and stores disposable evidence under `work/tmp`.

## Decision

- Do not perform a third attempt under the current prompt version.
- Do not relabel either rejected response as reviewable or AI proof completion.
- G-L4 remains open.
- The next unit must improve the machine-readable output contract presented to a small local model before authorizing a new prompt-version run. Candidate work is a compact JSON Schema/skeleton plus response-budget and relationship/evidence examples; it must be tested offline first and must not copy the deterministic golden answer into the prompt.

## Next

#771 inventories the prompt/schema reinforcement gap and defines one new prompt version. It must preserve source-only evidence, independent diff verification, and the two-attempt cap. No additional AI call is part of that preflight.
