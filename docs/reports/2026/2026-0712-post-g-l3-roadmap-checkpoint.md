# Post-G-L3 Roadmap Checkpoint

Status: `DONE`

## Decision

Advance to a bounded G-L4 investigation using Sample19 as the first reviewable schema-proposal fixture. Do not begin AI execution or schema mutation.

## Candidate comparison

| Candidate lane | Current readiness | Risk / missing boundary | Decision |
| --- | --- | --- | --- |
| Sample19 reviewable JSON-to-DB proposal | User JSON, interpreted table model, generated DataClass/DBAccess outputs, MySQL/SQLite verification, and an existing AI contract are available | Missing explicit proposal artifact and source-to-canonical diff | **Select investigation** |
| Generic AI normalization service | Product intent exists | Input limits, model/provider policy, prompt/version reproducibility, privacy, cost, persistence, and failure boundaries are all undecided | Too broad; park |
| Mtool review-request execution | Guard, persistence, duplicate reuse, and audit metadata exist | User-visible execution and multi-write ownership remain a separate mutation decision | Keep parked |
| Additional Mtool self no-code workflow | First read-only workflow is qualified | No concrete second workflow gap is blocking G-L4 | Park until demanded |
| Broader generated UI actions | Shared authority exists for bounded actions | Each action still needs field, policy, success, and transaction/recovery evidence | Keep parked |
| Config-store PostgreSQL / enterprise dialects | Technically separable | Not connected to the current no-code/AI roadmap gate | Keep parked |

## Why Sample19

Sample19 is a controlled before/after fixture:

- the source-side user material is a small nested article JSON shape;
- the target-side interpreted model is stable: author, category, article, and public summary;
- canonical seed metadata and generated outputs already pass regression tests;
- the JSON-to-DB AI contract already names tables, columns, relations, lifecycle/transactions, DegoDB targets, blocking questions, and assumptions;
- no production data or automatic write is required to evaluate a proposal.

This lets G-L4 pressure the missing middle—the proposal and review boundary—without conflating it with model integration or canonical mutation.

## Concrete gap

Sample19 currently states that AI interpreted the JSON, but implementation jumps directly to prewritten SQL seed metadata. There is no versioned machine-readable artifact that records:

- input identity and source JSON paths;
- proposed entities, fields, types, keys, and relationships;
- lifecycle and transaction candidates;
- DegoDB DataClass/DBAccess/Source Output targets;
- blocking questions and non-blocking assumptions;
- proposal-to-current-canonical diff;
- explicit `proposal_only` / `no_mutation` state.

Without that artifact, the narrative is educational but does not satisfy G-L4's reviewability requirement.

## #760 boundary

#760 is inventory and contract selection only. It must:

1. map the existing JSON-to-DB AI contract onto Sample19 source and seed evidence;
2. decide whether one JSON artifact plus a derived Markdown view is sufficient;
3. define stable identity/version/source-evidence and proposal-only markers;
4. define diff categories against canonical metadata without writing them;
5. list the minimum fast validation and review UI needed for later slices;
6. return `ONE_GAP` or `NOT_READY` if the contract cannot be bounded.

It must not call an AI provider, persist a proposal, generate SQL, alter seed metadata, or expose an approve/apply action.

## Estimate

The inventory is 0.5–1 day. Artifact implementation, deterministic fixture generation, diff rendering, and optional AI-provider integration must remain separate estimates.

## Next

#760 performs the Sample19 gap-only inventory and fixes the minimum reviewable proposal contract before any implementation.
