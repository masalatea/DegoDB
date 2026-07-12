# Sample19 Offline AI Request-Envelope Foundation

Status: `FIRST_SLICE_DONE`

## Result

Sample19 now has a deterministic, provider-neutral request envelope that prepares the exact synthetic source, read-only canonical comparison context, and versioned output instructions without contacting a model or reading credentials.

## Implementation

- Added the versioned `schema-proposal-v0` prompt template under the Sample19 proposal directory.
- Added `app_schema_proposal_request_build()` with envelope version `schema-proposal-request-v0`.
- The builder decodes and checks the fixed source root and Sample19 canonical snapshot identity.
- It records template/final prompt, source, and canonical snapshot SHA-256 values and byte lengths.
- The final prompt is deterministic and embeds the exact checked-in JSON bytes.
- The envelope explicitly records network, credential access, persistence, mutation, and apply as disabled.
- No provider/model field, API client, environment credential read, filesystem write, retry, or response acceptance was introduced.

## Fail-closed coverage

- Missing prompt placeholders are rejected.
- Invalid/non-object source or canonical JSON is rejected.
- A source without `/article` is rejected.
- A canonical snapshot with the wrong version or project is rejected.
- Repeated builds from identical bytes produce the identical envelope and hashes.

## Verification

- PHP syntax checks: passed.
- Full `make test`: 451 tests, 14,039 assertions, 1 skipped.

## Next

#768 adds an offline response-attempt acceptance boundary. It should take injected response bytes plus explicit run metadata, preserve the raw response hash, validate the proposal/source/diff without silent repair, and emit reviewable acceptance/rejection metadata. It must still have no provider client, credential lookup, persistence, mutation, or AI call.
