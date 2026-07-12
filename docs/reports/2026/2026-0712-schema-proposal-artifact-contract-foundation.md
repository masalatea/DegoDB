# Schema Proposal Artifact Contract Foundation

Status: `FIRST_SLICE_DONE`

## Result

DegoDB now has a versioned, fail-closed schema-proposal artifact foundation. Sample19 provides the first deterministic fixture linking source JSON to reviewable schema candidates without an AI call or canonical mutation.

## Sample19 artifacts

- `proposal/source/article.json`: standalone source bytes used for evidence and SHA-256 binding.
- `golden/schema-proposal.json`: machine-readable source of truth using `schema-proposal-v0`.
- `golden/schema-proposal.md`: deterministic review view derived only from the JSON proposal.

The proposal describes four entity candidates, their fields and keys, two relationships, article lifecycle, an import transaction candidate, DegoDB DataClass/DBAccess/Source Output targets, migration policy, one blocking question, one assumption, and canonical-diff fixture entries.

## Safety boundary

The golden artifact declares:

- `state: proposal_only`;
- `apply_supported: false`;
- `provenance.kind: deterministic_fixture`;
- `provenance.ai_authored: false`.

There is no proposal repository, SQL/DDL renderer, metadata import, approval transition, or apply route. The Markdown renderer validates the JSON first and refuses invalid proposals.

## Source traceability

The proposal source SHA-256 matches the exact standalone JSON bytes. Entity, field, relationship, lifecycle, and diff evidence uses JSON Pointer paths rooted at `/article`.

This distinguishes source facts such as `/article/title` from inferred candidates such as surrogate IDs and derived public read models while keeping every candidate reviewable.

## Fail-closed validator

The shared validator rejects:

- invalid JSON and non-object JSON;
- unknown/missing proposal version;
- state other than `proposal_only`;
- `apply_supported` other than false;
- malformed source SHA-256 or root/evidence pointers;
- evidence outside the declared source root;
- missing/duplicate entity, field, key, or relationship identities;
- keys and relationships referencing unknown fields/entities;
- lifecycle records referencing unknown entities;
- DBAccess targets referencing unknown entities or DataClasses;
- diff categories outside add/change/remove/unchanged/conflict.

Validation only returns errors/warnings and never rewrites the artifact.

## Review Markdown

The derived view prominently states that apply is unsupported and shows:

- proposal/project/version/source hash/provenance;
- entity candidates and field counts;
- canonical diff summary;
- blocking questions;
- non-blocking assumptions.

The checked-in Markdown is compared byte-for-byte with current renderer output.

## Verification

- PHP syntax checks: passed.
- Direct golden validation, SHA binding, and Markdown parity: passed.
- `git diff --check`: passed.
- Full suite: 438 tests, 13,948 assertions, 1 skipped.

## Boundary

- The checked-in canonical diff remains fixture data in this slice; it is not yet independently derived.
- No AI provider, prompt, upload, privacy expansion, persistence, diff UI, or apply action.
- G-L4 is not complete until a derived diff and read-only review UI are proven.

## Next

#762 adds a read-only deterministic diff builder against a Sample19 canonical snapshot and verifies that prewritten diff claims cannot contradict the derived result.
