# First L1 Pattern and Second-Sample Selection

Status: `DONE`

## Result

The Sample18 qualification pattern is now a reusable checklist in `docs/no-code-l1-sample-qualification-checklist.md`. Sample29 is selected for the second-sample G-L2 comparison.

## Candidate comparison

| Candidate | Existing strength | Comparison cost/risk | Decision |
| --- | --- | --- | --- |
| Sample07 | Real generated DBAccess insert/update/delete, shared contract, managed update operation, no-code artifacts. | Primarily a generator/CRUD tutorial; lacks the current/alias public UI and product-facing browser evidence expected for L1 comparison. | Keep as DBAccess contract reference. |
| Sample28 | Richest foundational no-code sample: list/detail/form, React bridge, schema-form probes, approval/tryout, runtime data and submit processing. | Too many platform-foundation concerns make it difficult to isolate whether the Sample18 qualification pattern itself is reusable. | Keep as foundation/reference sample. |
| Sample29 | Separate support-case domain, list/detail/form, scoped `update_support_case`, current/alias submit, runtime-data and outbox/generated DBAccess processing evidence. | Needs a focused qualification inventory and explicit comparison to Sample18's direct guarded execution model. | Selected. |
| Sample31 | Separate inventory domain with numeric/date typed fields, scoped update, current/alias submit and processing evidence. | Adds typed-field/date/numeric variation before the simpler second-domain comparison is formally closed. | Use after Sample29 as typed-field extension candidate. |

## Why Sample29

Sample29 is the smallest credible test of reuse outside the task-card domain:

- the generated screen and runtime contracts are shared;
- authorization introduces the explicit `editor` role and `support_case:write` scope;
- the qualified action shape is keyed update rather than create;
- server processing uses the managed-operation outbox and generated DBAccess path;
- current/alias public runtime and post-processing live-row evidence already exist;
- it avoids Sample18-specific transaction/adaptor code.

This gives G-L2 a meaningful comparison: same no-code platform contracts, different domain and execution model.

## #749 boundary

#749 is evidence inventory only. It will apply the checklist to the existing Sample29 implementation and decide among:

- already qualified with explicit exclusions;
- one concrete missing qualification test or metadata unit;
- not ready due to multiple gaps.

It must not enable broader actions or copy the Sample18 UI-authority flag mechanically.

## Estimate

The inventory is 0.5–1 day. A discovered implementation gap receives a separate estimate after it is identified.
