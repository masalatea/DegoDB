# Sample19 Offline AI Response Acceptance Foundation

Status: `FIRST_SLICE_DONE`

## Result

Sample19 now has a provider-neutral acceptance boundary for injected AI response bytes. It records immutable attempt evidence and admits a response only to read-only review after proposal, source, provenance, and independently derived canonical diff checks pass.

## Contract

- Inputs are response bytes, explicit run metadata, exact source bytes, and exact canonical snapshot bytes.
- Attempt evidence records provider/model labels, prompt hashes, source/canonical hashes, attempt 1 or 2, generation timestamp, optional provider request ID, response hash, and response byte length.
- The acceptance operation explicitly records that it performed no network call, credential access, persistence, or mutation.
- Accepted proposals require `SAMPLE19`, the exact source identity/hash, `ai_generated_proposal`, `ai_authored=true`, and a proposal timestamp matching the run metadata.
- Existing schema proposal validation enforces `proposal_only`, `apply_supported=false`, evidence/reference integrity, and the version contract.
- The declared canonical diff must exactly equal the independently derived diff.
- Rejected responses return no proposal or derived diff for review and retain their response hash plus rejection reasons.

## Fail-closed evidence

- Unsafe approved/apply state is rejected without repair.
- Deterministic or false AI provenance is rejected.
- Source hash/identity mismatch is rejected.
- Declared/derived diff mismatch is rejected.
- Missing/invalid provider, model, prompt hashes, generation timestamp, or attempt number is rejected.
- Attempts are bounded to 1 or 2 by the acceptance contract.

## Verification

- PHP syntax checks: passed.
- Full `make test`: 454 tests, 14,061 assertions, 1 skipped.

## Next

#769 is the real AI generation authorization checkpoint. Before adding or invoking any provider client, it must receive an explicit provider, exact model, local-versus-external transmission decision, credential source, and approval to transmit the fixed synthetic Sample19 source plus canonical comparison context. No real call is authorized by #768.
