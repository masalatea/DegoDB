# Schema Proposal Read-Only Review UI Preflight

Status: `DONE`

## Result

The first G-L4 review UI boundary is fixed before implementation. It is a parallel, default-off, authenticated admin page for the exact Sample19 fixture and has no mutation surface.

## Route and authorization

- Method/path: `GET /projects/SAMPLE19/schema-proposals/sample19-article-content-model-v1`.
- Route name: `project_schema_proposal_review`.
- Site: admin only.
- Feature switch: `MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED`, default off with explicit truthy normalization.
- Authorization: reuse `app_project_shared_contract_route_bootstrap($app, $request, ['GET'])` so existing admin/config role and project existence checks remain authoritative.
- Project key must be exactly `SAMPLE19` after normalization.
- Proposal ID must be exactly `sample19-article-content-model-v1` after URL decoding.
- Disabled, other-project, and unknown-proposal paths return not found.

The specific route must be matched before broader project routes. No public, current/alias, or lab route is introduced.

## Fixed read-only inputs

The first implementation reads only these mounted read-only fixture files:

- `sample/tutorials/sample19-json-first-content-model-demo/proposal/source/article.json`;
- `sample/tutorials/sample19-json-first-content-model-demo/golden/schema-proposal.json`;
- `sample/tutorials/sample19-json-first-content-model-demo/golden/canonical-schema-snapshot.json`.

There is no path parameter to arbitrary files and no upload. The route must verify:

1. all files are readable;
2. source SHA-256 matches the proposal;
3. proposal validation succeeds;
4. canonical snapshot validation succeeds;
5. declared diff exactly matches independently derived diff.

Failure returns an explicit integrity error and never falls back to README narrative, cached data, or prewritten Markdown.

## Required presentation

### Safety header

The first visible region states:

- proposal-only state;
- apply unsupported;
- deterministic fixture provenance;
- not AI-authored;
- read-only/no mutation.

### Source and provenance

Show logical filename, media type, SHA-256, hash verified state, source root JSON Pointer, redaction state, proposal version/ID/project, created time, provenance kind, and generator identity.

### Entity signatures and evidence

For each proposed entity, show purpose, field keys, key identities, connected relationships, and source evidence pointers/rationales. This is read-only expansion, not an editable schema form.

### Derived canonical diff

Render only the independently derived result. Each row shows category, entity key, proposal signature, canonical signature, review note, and severity:

| Category | UI severity | Meaning |
| --- | --- | --- |
| `unchanged` | `ok` | No review blocker |
| `add` | `info` | New proposal candidate; non-blocking in read-only review |
| `change` | `review_required` | Human review required before any future lifecycle |
| `remove` | `blocking` | Must not progress implicitly |
| `conflict` | `blocking` | Canonical ambiguity must be resolved |

The page-level state is `blocking` if any remove/conflict exists, `review_required` if no blocker but change exists, and `reviewable` otherwise. These states do not grant approval or apply authority.

### Questions and assumptions

Blocking questions and non-blocking assumptions are separate visible sections with stable keys. Evidence pointers remain visible so a reviewer can trace why the proposal exists.

## Zero-action boundary

The HTML must contain none of:

- `<form>`;
- `<button>`;
- non-navigation `POST` target;
- approve/apply/import/generate-SQL control;
- runtime execution binding;
- JavaScript dispatch code;
- CSRF token, because no submission exists.

The only navigation links are back to the Sample19 project/admin context and optional source documentation.

## Response behavior

| Case | Result |
| --- | --- |
| Switch off/malformed | 404 before fixture read |
| Unauthenticated | Existing login redirect |
| Wrong site/role | Existing forbidden behavior |
| Other project/unknown proposal | 404 |
| Missing/unreadable fixture | 500 integrity page |
| Invalid proposal/snapshot/hash | 500 integrity page |
| Declared/derived diff mismatch | 409 integrity page |
| Valid reviewable fixture | 200 read-only review page |

## Fast evidence required in #764

- route ordering and auth registration;
- feature switch normalization/default-off;
- exact project/proposal allowlist;
- source hash and all integrity failures;
- severity aggregation for all five diff categories;
- visible safety/source/provenance/entity/evidence/diff/question/assumption markers;
- complete absence of forms, buttons, POST/apply/execution bindings;
- checked-in Sample19 fixture renders `reviewable` with four unchanged rows.

## Browser promotion after #764

The separate promotion gate must prove login redirect, authenticated off-state 404, explicit enablement, live rendered fixture, stable markers, navigation back to Sample19 context, and Apache access-log POST zero. Only then may G-L4 qualification be considered.

## Explicit exclusions

- No arbitrary material upload.
- No AI provider/model/prompt execution.
- No proposal persistence or review transitions.
- No canonical metadata write or live snapshot construction.
- No SQL/DDL generation.
- No approve/apply/import action.

## Next

#764 implements the exact read-only route and fast contract. Scope expansion returns to planning.
