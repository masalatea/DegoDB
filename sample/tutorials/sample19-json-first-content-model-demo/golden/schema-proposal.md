# Schema Proposal: sample19-article-content-model-v1

> Proposal only. Apply is not supported.

- Project: `SAMPLE19`
- Version: `schema-proposal-v0`
- Source SHA-256: `1606f9fe652d1993d07e3d421cef5985beeef7a6911d7326b11e41c478675d59`
- Provenance: `deterministic_fixture`

## Entity Candidates

| Entity | Purpose | Fields |
| --- | --- | ---: |
| `json_author` | Reusable article author identity | 2 |
| `json_category` | Reusable article category identity | 2 |
| `article_json_model` | Canonical article lifecycle record | 8 |
| `article_public_summary` | Read model for published article lists | 6 |

## Canonical Diff

| Category | Kind | Key | Review note |
| --- | --- | --- | --- |
| `unchanged` | entity | `json_author` | Derived proposal and canonical entity signatures match. |
| `unchanged` | entity | `json_category` | Derived proposal and canonical entity signatures match. |
| `unchanged` | entity | `article_json_model` | Derived proposal and canonical entity signatures match. |
| `unchanged` | entity | `article_public_summary` | Derived proposal and canonical entity signatures match. |

## Blocking Questions

- **author_name_identity**: Is author name stable enough to be a natural unique key?

## Non-Blocking Assumptions

- **category_name_identity**: Category name is unique within this bounded sample.
