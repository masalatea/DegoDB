# 2026-0711 Generated Custom Proxy Transaction Mutation Fixture

Status: `FIRST_SLICE_DONE`

## Goal

Prove Transaction Full through the generated Custom Proxy endpoint path, beyond wrapper-level and callback-order contract tests.

## Fixture

The integration fixture generates and loads:

- the canonical generated DBAccess runtime support;
- the generated Custom Proxy endpoint base;
- one ordinary DBAccess class with two insert methods;
- one transaction-enabled endpoint with two required mutation steps.

Both DBAccess instances use the shared global `$mtooldb` connection and remain transaction-unaware.

## Success Case

- The endpoint opens one transaction.
- Step 1 inserts `success-one`.
- Step 2 inserts `success-two`.
- Both required steps succeed.
- The endpoint commits.
- The response is `OK` and exposes insert IDs 1 and 2 through the driver-neutral last-insert-id API.

## Failure Case

- The endpoint opens a new transaction.
- Step 1 inserts `rollback-one` successfully.
- Step 2 attempts duplicate `success-one` and returns failure.
- Generated required-step handling raises `step failed: InsertSecond`.
- The endpoint rolls back and returns `NG`.
- Database verification contains only `success-one` and `success-two`; `rollback-one` is absent.
- No transaction remains active.

## Boundary

This is a real generated endpoint/runtime/database integration fixture, but it is not yet visible in a tutorial artifact. The next slice promotes the same capability into Sample14 so metadata, generated output, reference checks, and user-facing documentation show how to configure Transaction Full.
