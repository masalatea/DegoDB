# 2026-0711 Sample14 Transaction Full Tutorial Foundation

Status: `DONE`

## Result

Sample14 now contains a visible Transaction Full custom proxy definition:

- `TRANSACTION-PAIR` has `in_transaction=1`;
- it runs `sample14_transaction_item.InsertSample14TransactionItem` twice;
- both steps are required because `continue_even_if_failed_to_insert=0`;
- the generated handler delegates begin, commit, and rollback to the shared `$mtooldb` runtime wrapper;
- generated artifact and build-plan references assert two proxies, four total steps, and zero unresolved steps.

## Generator Gap Fixed

Custom Proxy runtime entity loading already supported canonical project metadata as a fallback when a source was absent from the legacy generated catalog. Build-plan step resolution did not use that fallback, so a newly imported project table and its DBAccess metadata were incorrectly marked unresolved before runtime generation.

Build-plan resolution now uses the same canonical materialization path. Existing generated-catalog resolution remains the first choice.

## Verification

- `make sample14-pack-runtime-test`
  - 1 test
  - 24 assertions
  - generated `TRANSACTION-PAIR` endpoint and handler references match
  - `custom_proxy_count=2`
  - `step_count=4`
  - `unresolved_step_count=0`

## Executable Sample Proof

Sample14 now invokes the published endpoint over HTTP against its MariaDB lab database:

1. two unique inserts return `OK`, expose both insert IDs, and commit both rows;
2. a deterministic duplicate in step 2 returns `NG`, while database verification shows that the step 1 row was rolled back.

This execution exposed and fixed two generator/runtime contract gaps:

- canonical bootstrap writes now use the object variable declared by the configured function signature and logical DataClass property names;
- generated DB runtime accepts the documented `MTOOL_PROXY_DB_*` variables as mysqli connection fallbacks while preserving `MTOOL_RUNTIME_DB_*` precedence.
