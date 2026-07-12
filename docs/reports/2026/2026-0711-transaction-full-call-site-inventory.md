# 2026-0711 Transaction Full Call-Site Inventory

Status: `DONE`

## Scope

Find real composite SQL update callers that should own one shared generated DBAccess transaction. Rank same-database candidates and exclude cross-store work from local atomicity claims.

## Priority 1: Generated Custom Proxy

The generated Custom Proxy model already has:

- ordered multi-step DBAccess execution;
- an `in_transaction` / legacy `InTransaction` setting;
- failure-to-exception conversion for required steps;
- a caller-owned `withOptionalTransaction()` boundary.

This is the clearest intended Transaction Full consumer.

The current generated runtime was broken for `in_transaction=1`: it required `$mtooldb instanceof mysqli` and called native `autocommit(false)`, while current generated DBAccess output uses the custom `MtoolGeneratedDbAccessRuntimeDb` wrapper. Insert-id response handling also assumed native mysqli.

The first integration slice therefore replaces the native-mysqli-only path with the shared wrapper methods and a driver-neutral last-insert-id method.

## Sample Inventory

### Sample14

Sample14 is the representative Custom Proxy generator sample. Its current `CATALOG-SUMMARY` proxy has two read-only steps and `in_transaction=0`. It proves step composition and artifact generation but cannot prove mutation commit/rollback.

Recommendation: extend Sample14 or add a tightly related fixture with multiple required same-database mutation steps, `in_transaction=1`, a successful all-commit case, and a deterministic second-step failure that proves first-step rollback.

### Sample18

Sample18 generated submit currently performs one application DBAccess mutation per route execution. Audit and idempotency records are in the config store, so the complete route spans separate stores. Its app DB mutation can use a local transaction, but app DB plus config DB cannot be claimed as physically atomic under one local transaction.

Recommendation: keep Sample18 outside the first generic composite DBAccess proof. Treat its cross-store recovery policy separately.

### Other samples

No handwritten sample caller was found that directly creates multiple generated DBAccess instances and performs multiple required mutation calls in one processing unit. Most sample DBAccess usage is through generated proxy/runtime artifacts. Do not add transactions indiscriminately to single-update samples.

## Mtool Self Inventory

The primary Mtool multi-write repositories and services found in the scan already contain explicit PDO transaction boundaries, including:

- custom proxy repository;
- DBAccess metadata repository;
- HTML template and project HTML repositories;
- no-code publish candidate repository;
- project/data-class/table import services;
- project, membership, identity membership, page security, source output, and metadata bundle paths.

Several repository functions also use an `ownsTransaction = !inTransaction()` pattern so they can participate in an outer transaction.

Recommendation: do not rewrite these wholesale. Later Mtool self work should be a gap-only audit for multi-write functions that lack a transaction, plus focused nested-ownership verification.

## Exclusions

- Cross-database or cross-store writes cannot be made physically atomic by one PDO/mysqli transaction.
- Read-only multi-step proxies do not require transaction wrapping.
- Single SQL update callers do not gain Transaction Full value from an extra wrapper.
- DDL and storage engines with implicit commit or no transaction support require separate policy.

## Ordered Next Work

1. Connect generated Custom Proxy transaction handling to the shared runtime wrapper.
2. Add a transaction-enabled multi-mutation Custom Proxy fixture and endpoint-level success/rollback proof.
3. Expand only to additional same-database composite sample callers found later.
4. Perform a gap-only Mtool self audit instead of replacing existing PDO transaction code.
