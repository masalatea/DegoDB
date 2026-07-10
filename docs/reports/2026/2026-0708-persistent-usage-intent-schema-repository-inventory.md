# Persistent Usage Intent Schema Repository Inventory

Date: 2026-07-08

## Summary

#421 inventories where explicit no-code interface usage intent should live. The smallest durable location is contract-level shared contract metadata, alongside the existing `sync_role`, `no_code_role`, and `app_persistence_role` values.

## Existing Shape

Current shared contract metadata is stored through:

- `project_shared_contracts`
- `project_shared_contract_fields`
- `shared_contract_metadata_repository_pdo.php`
- `shared_contract_manifest.php`

Existing contract-level roles:

| Field | Current meaning |
| --- | --- |
| `sync_role` | Sync/data ownership shape. |
| `no_code_role` | No-code role; `managed-screen` currently selects screen-generating contracts. |
| `app_persistence_role` | App-local/server persistence shape. |

Existing field-level roles:

| Field | Current meaning |
| --- | --- |
| `operation_role` | Field role in generated operations, for example editable. |
| `no_code_role` | Field-level no-code visibility/behavior hint. |
| `sync_role` | Field-level sync behavior hint. |
| `app_persistence_role` | Field-level app persistence hint. |

## Decision

Add explicit usage intent at the contract level first.

Candidate field:

```text
project_shared_contracts.usage_intent
```

Candidate values:

```text
screen
external_integration
sync
reporting
workflow
internal
```

## Rationale

- Usage intent explains why the interface exists, so it belongs to the interface/contract rather than an individual field.
- Existing `no_code_role=managed-screen` should continue to select generated screen contracts.
- Existing `sync_role` and `app_persistence_role` should continue to describe data movement and persistence, not presentation.
- `screen-definition.json` can prefer explicit `usage_intent` and fall back to derived intent when it is blank.
- This keeps the change additive and avoids a visual-builder jump.

## First Implementation Notes

- Add `usage_intent` as nullable/blank-safe metadata.
- Normalize it in `shared_contract_manifest.php`.
- Read/write it in `shared_contract_metadata_repository_pdo.php`.
- Validate allowed values close to the no-code/screen-definition layer first, then consider central validation once UI editing is added.
- Update sample no-code seeds only when the generated output needs an explicit proof; fallback from `no_code_role=managed-screen` should keep existing samples working.

