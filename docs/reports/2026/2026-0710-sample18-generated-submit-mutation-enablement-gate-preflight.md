# Sample18 Generated Submit Mutation Enablement Gate Preflight

Date: 2026-07-10
Plan: #598
Status: DONE

## Purpose

Define the explicit gate contract required before sample18 generated submit may execute DBAccess mutation.

This preflight does not enable mutation and does not call DBAccess.

## Enablement Flag

Mutation must stay disabled by default.

Initial flag candidate:

- environment/config key: `MTOOL_SAMPLE18_GENERATED_SUBMIT_MUTATION_ENABLED`
- enabled value: exact string `1`
- any other value, missing value, or malformed value: disabled

The helper should expose both:

- `mutation_enabled`: boolean
- `mutation_gate.status`: `disabled`, `ready`, `blocked`, or `failed`

## Required Gate Inputs

Mutation may become `ready` only when all of these are true:

- request method is POST;
- CSRF is valid;
- request normalization succeeds;
- dispatcher dry-run is `ok=true`;
- dispatcher `mutation_enabled` is still false before the final gate decision;
- audit append status is `appended`;
- idempotency status is `recorded`;
- idempotency `created=true`;
- dedupe key is non-empty;
- operation key maps to a known generated submit contract;
- explicit enablement flag is on.

Any missing condition must keep the route blocked and non-mutating.

## Duplicate Behavior

Duplicate idempotency results must not execute DBAccess.

When `idempotency.status=duplicate`:

- route remains HTTP 409 in the first gate helper slice;
- `mutation_gate.status=blocked`;
- failure code candidate: `duplicate_generated_submit`;
- existing idempotency record is returned for inspection;
- DBAccess mutation remains disabled.

## Failure Behavior

If audit append or idempotency persistence fails:

- route remains HTTP 409 `generated_submit_disabled` in the first helper slice;
- `mutation_gate.status=failed` or `blocked`;
- DBAccess mutation remains disabled.

If the explicit enablement flag is off:

- route remains HTTP 409 `generated_submit_disabled`;
- `mutation_gate.status=disabled`;
- DBAccess mutation remains disabled even when audit and idempotency are healthy.

## First Helper Slice

#599 should add a non-mutating gate helper and focused coverage only.

It should prove:

- default/missing flag is disabled;
- enabled flag alone is insufficient without healthy audit/idempotency states;
- duplicate idempotency blocks mutation;
- audit or idempotency failure blocks mutation;
- the helper can return `ready` only as metadata, without executing DBAccess.
