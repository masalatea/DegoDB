# Sample19 Reviewable Schema Proposal Gap Inventory

Status: `DONE_ONE_GAP`

## Decision

Sample19 is ready to fixture G-L4 through one bounded missing foundation: a versioned, machine-reviewable schema-proposal artifact contract.

The gap is not AI model integration. The current JSON-to-DB guidance already defines the reasoning categories, and Sample19 already provides source JSON plus a stable canonical target. What is missing is the durable boundary between those two sides.

## Existing evidence mapping

| Required proposal area | Existing Sample19 evidence | Gap |
| --- | --- | --- |
| Source material | README user JSON with `article`, nested `author` and `category` | No standalone source identity/fingerprint |
| Entity candidates | `json_author`, `json_category`, `article_json_model`, `article_public_summary` | Not represented as proposal records |
| Field/type/key candidates | Table DDL, required/null/default/unique/FK/index definitions | No source JSON Pointer evidence per candidate |
| Relationships | Article foreign keys to author and category | No proposal confidence/rationale record |
| Lifecycle | `draft` default, `published`, nullable `published_at` | Narrative/DDL only |
| Transaction boundary | Author/category/article import ordering is implied | No explicit candidate or uncertainty |
| DegoDB targets | DataClass, DBAccess public summary query, Source Outputs, AI Context | Seed metadata only |
| Questions/assumptions | JSON-to-DB AI contract defines both categories | Sample19 instance has none recorded |
| Canonical comparison | Stable seed SQL and generated outputs | No proposal-to-canonical diff |
| Mutation safety | Current workflow is manually seeded | No artifact-level `proposal_only` prohibition |

## Chosen artifact model

The source of truth is one UTF-8 JSON file. Markdown is a deterministic derived review view and must never become a second editable source.

### Identity and safety

Required top-level fields:

- `proposal_version`: initial value `schema-proposal-v0`;
- `proposal_id`: stable fixture/revision identity;
- `project_key`;
- `state`: exactly `proposal_only` for this lane;
- `apply_supported`: exactly `false`;
- `created_at` or deterministic fixture timestamp;
- `source`;
- `provenance`;
- proposal sections;
- `canonical_diff`;
- `validation`.

Any missing/unknown version, non-proposal state, or `apply_supported=true` fails validation.

### Source evidence

`source` records:

- media type and logical filename;
- SHA-256 of canonical source bytes;
- source root JSON Pointer;
- optional redaction declaration;
- embedded fixture input only for Sample19 test assets, not as a general production requirement.

Every proposed entity, field, and relation carries one or more evidence entries with JSON Pointer, evidence type, and short rationale. Sample19 pointers include `/article`, `/article/author/name`, and `/article/category/name`.

### Proposal sections

The minimum contract contains:

- raw JSON retention candidates;
- entities/tables;
- fields/columns including type guess, nullable, required, default, unique, identity role, and source evidence;
- keys/indexes;
- relationships and cardinality;
- lifecycle states/transitions;
- transaction candidates;
- DataClass candidates;
- DBAccess candidates;
- Source Output candidates;
- import/migration notes;
- blocking questions;
- non-blocking assumptions.

Confidence is descriptive review metadata, not permission to apply.

### Canonical diff

Diff is proposal-relative-to-current-canonical metadata and uses only:

- `add`;
- `change`;
- `remove`;
- `unchanged`;
- `conflict`.

Each entry identifies object kind, stable key, proposal value, canonical value when present, source evidence, and review note. `remove` and `conflict` are always visually blocking in later review UI, but this contract adds no approval/apply action.

For the first Sample19 golden fixture, the proposal is expected to be predominantly `unchanged` against the seed baseline. That tests traceability rather than pretending the already-seeded model is newly discovered.

## Provenance boundary

`provenance` distinguishes:

- `deterministic_fixture` for #761;
- a future AI provider/model/prompt version path;
- human edits, if later allowed, as separate append-only review metadata.

#761 must use `deterministic_fixture`; it must not fake AI authorship. AI context artifacts remain inputs an AI may read, not evidence that AI authored the proposal.

## Validation boundary

The fail-closed validator must reject at least:

- unknown/missing version;
- missing source SHA-256 or malformed JSON Pointer evidence;
- duplicate stable keys;
- field evidence outside the declared source root;
- relation endpoints not present in entity candidates;
- DBAccess targets referencing unknown DataClass/entity candidates;
- unknown diff category;
- `state` other than `proposal_only`;
- `apply_supported` other than `false`.

Validation reports errors and warnings without modifying any artifact or canonical store.

## Review progression

The bounded progression is:

1. #761: JSON contract, deterministic Sample19 golden, validator, derived Markdown;
2. later: canonical diff builder against current metadata;
3. later: read-only diff/review UI;
4. only after separate policy: optional persistence and review lifecycle;
5. AI provider integration remains independent and cannot imply apply authority.

## Explicit exclusions

- No AI call or prompt execution.
- No proposal repository or database table.
- No SQL/DDL generation.
- No canonical metadata import/update/delete.
- No approve/apply button.
- No arbitrary uploaded material or privacy policy expansion.
- No G-L4 completion claim from the contract alone; review UI evidence is still required.

## Next

#761 implements only the deterministic artifact foundation and validator. Any need for persistence, mutation, generic uploads, or AI provider configuration returns to planning.
