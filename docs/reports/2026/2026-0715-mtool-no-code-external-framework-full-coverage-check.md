# Mtool no-code external framework full-coverage check

## Status

`PRECHECK_DONE_PORTABLE_CONTRACT_FIRST`

## Question

Can an external FE/no-code/app framework cover the full Mtool-owned no-code scope?

Important reading:

- This does not mean covering every capability of the external framework.
- This does not mean Mtool itself claims complete no-code coverage for every possible app.
- This means: can the current Mtool-supported no-code capability set be represented by external framework input/output without losing Mtool's responsibility boundaries?

## Short answer

Yes, likely, if the migration is contract-first.

The current Mtool-owned no-code scope is already mostly expressed as metadata, artifacts, validation gates, and authority boundaries. Those are portable to external framework input packets.

No, if the migration is treated as a literal rewrite of Mtool's generated runtime implementation into one chosen external framework. That would be larger, less stable, and would incorrectly make the external framework implementation the source of truth.

## Decision history / 判断経緯

This precheck was added after two related concerns were raised:

1. Mtool has already built meaningful no-code output, so later migration might become expensive.
2. The existing responsibility boundary between generated/core behavior and custom behavior should not be lost if external frameworks are introduced.

The first framing was "can Mtool's own no-code output be fully replaced by an external framework?" That was clarified to mean Mtool-side full coverage only: can the current Mtool-supported no-code scope be represented by external framework output? It does not mean complete external-framework feature coverage, and it does not mean Mtool claims every possible app is no-code.

Under that clarified scope, the answer is feasible in principle because the important Mtool responsibilities are already contract-like: metadata, artifacts, validation gates, server authority statements, and generated/custom ownership boundaries.

The next judgment was product priority. Feasible replacement does not imply immediate replacement. Current judgment is that it is more valuable to expose optional external framework outputs than to migrate away from Mtool's own runtime. The existing runtime remains useful as a supported baseline, reference implementation, and fallback preview. External framework support should be additive.

This led to the plan being repositioned from "migration boundary check" to "optional output boundary check".

## Mtool-owned full scope for this check

The "full" Mtool scope means the currently supported no-code contract:

| Mtool-supported capability | External framework full-coverage feasibility | Notes |
| --- | --- | --- |
| screen definition metadata | `PORTABLE` | Treat as canonical screen/input contract. |
| runtime preview metadata | `PORTABLE` | External framework can consume the same preview/readiness metadata. |
| list/detail/form surfaces | `PORTABLE` | Standard external rendering target. |
| selected row/key markers | `PORTABLE` | Must remain explicit metadata, not inferred by framework code. |
| field display/input metadata | `PORTABLE` | Maps to form/schema libraries or generated components. |
| action intent draft | `PORTABLE_WITH_AUTHORITY_BOUNDARY` | External UI may render/draft intent; server remains authority. |
| required field readiness | `PORTABLE` | Can be implemented as form validation/readiness state. |
| guarded submit / outbox handoff where supported | `PORTABLE_WITH_SERVER_OWNERSHIP` | External framework submits to guarded/outbox boundary; it must not own business mutation authority. |
| publish candidate / approval | `PARTIALLY_PORTABLE` | External output can be a candidate artifact, but Mtool should own approval/current/alias policy. |
| current preview / alias preview | `PARTIALLY_PORTABLE` | External preview can be served or referenced, but current/alias resolution policy stays Mtool-owned. |
| validation command map | `PORTABLE` | Becomes conformance gates for external output. |
| browser smoke / representative gates | `PORTABLE` | Same acceptance idea; target-specific commands may differ. |
| generated/custom ownership boundary | `PORTABLE` | Rename as contract/core vs external/custom/extension-owned. |
| forbidden / confirmation-required boundary | `PORTABLE` | Should be copied into external framework task packets/adapters. |

## Boundary decision

The existing "Mtool function vs custom" classification can survive the move, but the words should become more neutral:

| Current wording | External framework wording |
| --- | --- |
| Mtool-generated / 本機能 | contract-owned / core / supported surface |
| custom | external-owned / custom / extension surface |
| Mtool runtime | reference implementation / fallback preview |
| Mtool validation | conformance / acceptance gate |
| Mtool server authority | server-owned authority boundary |

This keeps the same conceptual boundary while avoiding the false implication that Mtool owns external app source code.

## What should not move

These should not be moved into the external framework as browser-owned behavior:

- server-side authorization;
- CSRF policy;
- idempotency;
- Transaction Full / DB transaction responsibility;
- audit policy;
- guarded operation allow-list;
- outbox processing;
- approval/current/alias policy ownership;
- offline sync or realtime sync unless a separate sync contract exists.

The external framework may render UI and submit requests, but it should not become the authority for those decisions.

## What may become external-framework-owned

These are good candidates to move out of Mtool's own runtime implementation over time:

- visual list/detail/form rendering;
- component layout;
- client-side state;
- form binding;
- target-specific design system;
- target-specific routing/navigation;
- frontend adapter code;
- mobile/native shell integration;
- target-specific build/test commands.

Mtool should continue to own the input contract and validation, not necessarily the final UI implementation.

## Missing or risky areas

No immediate blocker was found for Mtool-supported full coverage.

The likely missing pieces are metadata hardening items, not a fundamental architecture blocker:

| Area | Risk | Needed before full external-framework replacement claim |
| --- | --- | --- |
| target-neutral UI/component vocabulary | Medium | Ensure every Mtool-supported screen/action maps to a stable framework-neutral contract. |
| current/alias external artifact policy | Medium | Define whether external output can become a publish candidate/current artifact and how rollback works. |
| validation parity | Medium | Define conformance gates for external output equivalent to current Mtool browser/contract gates. |
| target-specific extension registry | Low to medium | Keep framework-specific settings outside the core contract. |
| custom extension slots | Medium | Make extension ownership explicit enough that generated/core and custom code do not blur. |

## Verdict

Mtool-level full coverage by an external framework is feasible in principle.

But feasibility does not mean it is the right next implementation.

Current product judgment: do not prioritize replacing Mtool's own no-code runtime with an external framework. The more useful near-term direction is to keep Mtool's own no-code output as the supported baseline and add optional external framework outputs that consume the same contract.

Recommended direction:

1. Keep Mtool's no-code contract as the source of truth.
2. Treat Mtool's current runtime as reference/fallback, not the final required UI implementation.
3. Let external frameworks optionally implement the supported/core surface from the same contract.
4. Preserve custom/extension ownership as a first-class part of the contract.
5. Add target-specific optional output proofs before claiming one concrete framework target is production-usable.

## Plan impact

The planned external-framework work should be read as optional output support, not as a migration or replacement program.

After NC-S5 closes standalone no-code completion, the next external-framework step should produce a target-neutral optional-output matrix. A later target slice can choose one concrete target, such as React/Web + Capacitor, and prove that Mtool can emit useful optional output for that target while keeping `mtool_no_code` intact.
