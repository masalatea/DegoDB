# No-Code Next Phase Detailed Plan / no-code 次 phase 詳細計画

Status: `DONE`

Date: 2026-07-05

## Summary

The next phase is not a single-feature choice. The remaining work is a sequence:

1. practical runtime flow polish
2. synchronous demo processing
3. database-first plus no-code product narrative docs
4. next domain/sample expansion

The order matters. Practical flow polish makes the current async submit / outbox / refresh path understandable. Synchronous demo processing then improves the tryout story without replacing the async foundation. Product narrative docs should describe the actual product shape after those user-facing behaviors are clearer. A next domain/sample should come after the story and flow are stable enough to repeat.

## #179 Practical Runtime Flow Polish

Status: `DONE`

Estimate: 0.5 - 1.5 days

Goal:

- Make the runtime submit flow understandable without requiring the user to know internal outbox mechanics.
- Keep the current async outbox foundation.
- Reduce the gap between "submit accepted" and "what should I do now?"

First-slice deliverables:

- A clearer runtime result state model for pending / running / done / failed where the current response data allows it.
- Runtime copy that explains the next step in user language, not implementation language.
- Better visible grouping for submit result, outbox detail link/copy, and refresh action.
- Browser smoke coverage for sample28 and sample29 where practical.
- A short dated report recording accepted behavior and remaining gaps.

Result:

- First slice completed with a visible Submit / Outbox tracking / Refresh runtime flow indicator.
- sample28 and sample29 public runtime browser smokes verify the accepted flow after real submit.

Out of scope:

- Removing the async outbox path.
- Adding conflict resolution.
- Adding transport beyond current local/server execution.
- Broad redesign of generated runtime layout.

Verification:

- Focused PHP lint / JS syntax checks for touched files.
- sample28 no-code public runtime browser smoke.
- sample29 no-code public runtime browser smoke if affected.
- `git diff --check`.
- Full `make test` before committing code changes.

## #180 Synchronous Demo Processing

Status: `DONE`

Estimate: 1 - 2 days

Goal:

- Provide a safe tryout/demo mode where submit can process work immediately and refresh the visible result.
- Preserve async outbox as the production-oriented foundation.

First-slice deliverables:

- A scoped demo/tryout processing switch or route behavior with a fail-closed default.
- Clear separation between async production handoff and synchronous demo behavior.
- Runtime feedback that says whether work was accepted for async processing or processed during the demo request.
- Smoke coverage proving sample28 and at least one second-domain path still behave safely.

Result:

- First slice completed as an opt-in demo gate, not a production default.
- Runtime execution binding advertises `demo_processing: available` only when `MTOOL_NO_CODE_RUNTIME_SYNC_DEMO` is truthy and `MTOOL_RUNTIME_SQLITE_PATH` is set.
- Generated runtime submit sends `runtime_demo_process=1` only when that binding is available.
- Public execution endpoint requires the explicit POST flag and then processes one pending outbox item through the existing server DBAccess outbox processor.
- sample28/sample29 public runtime browser smokes still prove the normal async path remains unchanged by default.

Out of scope:

- Making synchronous processing the default production model.
- Long-running job orchestration.
- External queue/worker infrastructure.

Verification:

- Focused runtime execution tests.
- sample28 public runtime browser smoke.
- sample29 or equivalent second-domain smoke when the synchronous path is generic.
- Full `make test`.

## #181 Product Narrative Docs

Status: `DONE`

Estimate: 0.5 - 1 day

Goal:

- Update the product-facing docs so the two-layer structure is explicit:
  database-first tooling is the foundation, and no-code runs on top of canonical metadata, generated artifacts, approval flow, and managed operations.

First-slice deliverables:

- README wording that preserves the database tool identity.
- A no-code chapter or doc section that explains the upper no-code layer without making it sound detached from the foundation.
- Updated tryout path references for sample28/sample29.
- A concise capability boundary: what works now, what is intentionally async, what remains future work.

Result:

- Permanent docs now describe no-code as an upper layer on canonical metadata, generated artifacts, managed operations, Source Output review, and approval records.
- `README.md`, `docs/no-code-tryout.md`, `docs/overview.md`, `docs/use-cases.md`, and `docs/README.md` were updated.
- The docs keep runtime submit async by default and describe synchronous processing as demo-only opt-in.

Out of scope:

- Marketing-only rewrite detached from implemented behavior.
- Claims about unsupported deployment modes.
- Removing database-first documentation.

Verification:

- Link/path sanity check for updated docs.
- `git diff --check`.
- Focused test only if docs touch generated references or sample commands.

## #182 Next Domain/Sample Expansion

Status: `DONE`

Estimate: 2 - 5 days

Goal:

- Prove that the improved no-code flow repeats beyond sample28/sample29.
- Choose a domain that reveals a real product constraint without requiring domain-heavy compliance review.

Candidate directions:

- A lightweight approval/request workflow sample.
- A small inventory/order tracking sample.
- A content/editorial workflow sample if it exercises relation/read-model pressure.

Selection criteria:

- Demonstrates database-first metadata reuse.
- Exercises list/detail/form/action behavior clearly.
- Can be seeded and smoked deterministically.
- Avoids regulated-domain complexity unless domain review is available.

First-slice deliverables:

- Sample scaffold and catalog entry.
- Metadata seed for table/read model/no-code actions.
- Generated runtime pack or public runtime smoke.
- Dated report and current-plan update.

Result:

- First slice completed as `sample31-no-code-inventory-request-demo`.
- Added inventory request project/table/shared contract/managed operation/Source Output seed.
- Added sample31 runtime artifact test and generated runtime UI smoke.
- Registered sample31 in Makefile, sample catalog, tests, and sample documentation.

Out of scope:

- Japanese invoice / billing compliance sample without domain review.
- New native/mobile output family.
- New persistence architecture.

Verification:

- New sample pack/runtime smoke.
- Browser smoke for generated runtime if it has public preview.
- Full `make test` before committing code changes.

## Commit / Push Guidance

- Keep each implementation lane as a meaningful commit group: implementation, tests, and directly related docs together.
- Planning-only updates may be docs-only commits.
- Push after a coherent milestone or after explicit user approval.
