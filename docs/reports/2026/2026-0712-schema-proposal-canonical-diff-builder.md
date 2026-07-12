# Schema Proposal Canonical Diff Builder

Status: `FIRST_SLICE_DONE`

## Result

Schema proposal diff claims are now independently derived from a validated proposal and a versioned read-only canonical snapshot. A proposal cannot make its checked-in diff authoritative by declaration alone.

## Sample19 canonical snapshot

`golden/canonical-schema-snapshot.json` records the stable Sample19 seed baseline with:

- `canonical-schema-snapshot-v0`;
- project identity;
- source label;
- four entities;
- each entity's field, key, and connected relationship identities.

The snapshot is a fixture view of current canonical metadata. It is not imported or modified by the diff builder.

## Entity signature

For the first bounded diff, the comparison unit is an entity signature containing:

- ordered field keys;
- key identities;
- every relationship connected as source or target.

Comparison is order-insensitive, while emitted values retain stable artifact order. A field/key/relation addition or removal therefore produces an entity `change`, not a false `unchanged` result.

## Derived categories

The builder emits:

- `add`: proposal entity absent from canonical snapshot;
- `change`: both exist but signatures differ;
- `remove`: canonical entity absent from proposal;
- `unchanged`: signatures match;
- `conflict`: canonical snapshot explicitly reports an unresolved entity conflict.

Proposal entities retain their source evidence in derived entries. Canonical-only removals have no fabricated proposal evidence.

## Declared-diff verification

Sample19's proposal now carries the same structured entity signatures in `proposal_value` and `canonical_value`. Verification compares the full declared list against independently derived entries, including category, identity, values, evidence, order, and review note.

Any difference returns `declared_canonical_diff_mismatch` and exposes the derived read-only result for review. It does not rewrite the proposal.

## Fail-closed snapshot checks

Diff derivation rejects:

- unknown snapshot version;
- project mismatch;
- missing or duplicate canonical entity identity;
- missing field/key/relationship lists;
- empty canonical entity inventory.

Proposal validation still runs first, retaining all #761 safety checks.

## Coverage

- Golden proposal produces four `unchanged` entries and exactly matches its declared diff.
- Modified fixture derives all five categories across a single test without mutating the proposal.
- Contradictory declared category is rejected.
- Invalid version, project mismatch, and duplicate canonical entity are rejected.
- Sample19 README identifies the canonical snapshot and derived-diff boundary.

## Verification

- PHP syntax and direct golden diff parity: passed.
- `git diff --check`: passed.
- Full suite: 442 tests, 13,969 assertions, 1 skipped.

## Boundary

- Diff is entity-signature level; field-by-field visual expansion is deferred to review presentation.
- Snapshot construction from a live repository is not added in this slice.
- No persistence, AI call, SQL, approval, or apply execution.
- G-L4 remains open until a read-only review UI makes proposal, source evidence, questions/assumptions, and blocking diff states inspectable.

## Next

#763 defines the read-only review UI route and evidence boundary before implementation.
