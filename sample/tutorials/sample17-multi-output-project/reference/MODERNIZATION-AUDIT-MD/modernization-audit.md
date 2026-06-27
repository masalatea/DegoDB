# Modernization Audit

Project: `SAMPLE17` / Sample 17 Multi Output Project

This report is deterministic generator output. AI may read it, but AI is not the author or source of truth.
The report is read-only and does not modify runtime code, DBAccess classes, DataClass files, templates, or database schema.

## Summary

| Metric | Count |
| --- | ---: |
| Tables | 1 |
| Data classes | 1 |
| DBAccess classes | 1 |
| Relationships | 0 |
| Source outputs | 6 |

## Risk Summary

| Level | Tables |
| --- | ---: |
| High | 0 |
| Medium | 1 |
| Low | 0 |

## Recommended Review Order

- `capstone_task`

## Table Audit

| Table | Physical Name | Risk | Columns | Primary Keys | Nullable Columns | Relationships | DBAccess Functions | Signals |
| --- | --- | --- | ---: | --- | ---: | ---: | ---: | --- |
| capstone_task | capstone_task | medium | 7 | id | 1 | 0 | 2 | no-relationship-metadata |

## Use Rules

- Treat this as a diagnostic artifact, not an automatic migration plan.
- Investigate high and medium risk tables before changing generated output contracts.
- Unknown relationship or key intent must stay unknown until source metadata or domain review confirms it.
