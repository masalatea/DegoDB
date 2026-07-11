# Custom Operation Manifest Inventory First Slice

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#448 records the first custom operation manifest inventory after visible operator action affordances landed.

The purpose is to define the metadata contract before any custom operation becomes executable. The first slice remains non-executing.

## Manifest Shape

A custom operation manifest entry should carry:

- `operation_key`: stable key used by generated HTML, React bridge, routes, audit events, and tests.
- `label`: operator-facing action label.
- `category`: `build`, `publish`, `review_request`, `approval`, `rollback`, `navigation`, or `custom`.
- `target`: logical target such as `source_output`, `publish_candidate`, `artifact`, `shared_contract`, or `project`.
- `side_effect_class`: `read_only`, `queued_mutation`, `direct_mutation`, `approval_transition`, or `external_handoff`.
- `availability`: `disabled`, `available`, `blocked`, or `deferred`.
- `policy_key`: named policy or authorization contract required before enablement.
- `csrf_required`: whether browser execution must carry CSRF protection.
- `audit_event`: event name expected when the operation is requested or completed.
- `adapter_handoff`: optional generated HTML / React / custom app handoff target.
- `intent`: short human-readable intent text.

## Source Of Truth

The source of truth should be no-code metadata, not generated artifact hand edits.

Candidate storage path:

- `contract_metadata.custom_operations` for operations attached to a no-code contract / review surface.
- `contract_metadata.extension_slots[].action_items[].operation_key` for slot-local UI binding to a manifest operation.
- Runtime JSON carries the normalized manifest for generated HTML and adapters.

## Generated HTML Boundary

Generated HTML may render operation affordances, but execution remains disabled until a separate implementation lane adds all required server-side boundaries.

Allowed in first implementation:

- carry operation metadata into runtime JSON
- bind disabled buttons to `operation_key`
- show `intent`, `availability`, and unavailable reason
- expose stable data attributes for browser/adapter tests

Not allowed in first implementation:

- POST routes
- build/publish/approval mutation
- generated HTML server-side execution
- bypassing Source Output review or publish candidate workflows

## Policy / Auth / CSRF Boundary

Before execution, each operation must define:

- required principal role / scope
- target binding validation
- CSRF requirement for browser-originated requests
- fail-closed behavior when policy is absent or not satisfied
- audit event emission rules

## Adapter Handoff Boundary

The manifest should be adapter-neutral:

- generated HTML runtime can render disabled/available affordances
- React bridge can map manifest operations to custom components or callbacks
- full custom apps can consume the same operation keys
- adapters must not invent operation identity outside the manifest

## First Implementation Candidate

The next code-backed slice should be metadata carry-through only:

- add a normalized `custom_operations` manifest path
- add Mtool Source Output review dogfooding operations such as `review_artifact`, `request_publish`, and `open_source_output_settings`
- bind existing `operator_actions_panel` action items to manifest `operation_key`
- assert runtime JSON and generated HTML carry stable operation metadata
- keep all buttons disabled / non-mutating

## Verification

Docs-only inventory. Latest code verification remains #444:

- PHP syntax checks
- Focused PHPUnit: `OK (8 tests, 121 assertions)`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 345, Assertions: 11273, Skipped: 1.`
