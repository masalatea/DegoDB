# 2026-06-27 PostgreSQL Input / Output Support Completion

## Status

- status: `POSTGRESQL_INPUT_OUTPUT_SUPPORT_COMPLETED`
- scope: PostgreSQL input and output support
- non-goal: Mtool config store PostgreSQL support

## Summary

PostgreSQL input and output support is complete for the intended support boundary. It remains opt-in, but it is no longer dependent on ad hoc PostgreSQL containers or undocumented local setup.

Completed in this slice:

- added `compose.user-db-pgsql.yaml` as a reusable local PostgreSQL service for user DB contract gates
- added Make targets to start, stop, inspect, reset, and health-check that PostgreSQL service
- added `postgresql-user-db-test-local` as the local completion gate
- passed PostgreSQL runtime DSN / credential env through the sample PHPUnit runner
- kept Mtool config store PostgreSQL support outside this input/output completion scope

## Completion Boundary

Current PostgreSQL support means:

- Input: imported user database schema is covered by opt-in PostgreSQL live schema import
- Output: generated DBAccess runtime is covered by opt-in representative PostgreSQL contracts
- Input verification: sample12 live schema import has a local PostgreSQL runbook/compose-backed verification path
- Output verification: sample10 MySQL/PostgreSQL user DB contract capture and comparison verifies generated DBAccess behavior
- generated proxy/OpenAPI: covered through generated surface and naming contracts, not as a separate PostgreSQL proxy runtime claim

Current PostgreSQL support does not mean:

- Mtool config store PostgreSQL support, which is outside the input/output support scope
- default PostgreSQL service in every tutorial sample stack
- SQL Server / Oracle support
- double-quoted mixed-case PostgreSQL identifier support

## Local Completion Gate

Use this command when a local PostgreSQL service is acceptable:

```sh
make postgresql-user-db-test-local
```

This starts `compose.user-db-pgsql.yaml` on `USER_DB_PGSQL_HOST_PORT` and verifies:

- MySQL/MariaDB baseline capture for the selected user DB contract sample
- PostgreSQL capture for the selected user DB contract sample
- MySQL/MariaDB vs PostgreSQL user DB contract comparison
- sample12 PostgreSQL live schema import against the same local PostgreSQL service

Default local settings:

```sh
USER_DB_PGSQL_HOST_PORT=15432
USER_DB_PGSQL_CONTAINER_HOST=host.docker.internal
USER_DB_PGSQL_DB=lab_app
USER_DB_PGSQL_USER=lab_app
USER_DB_PGSQL_PASSWORD=lab_app_password
```

`USER_DB_PGSQL_CONTAINER_HOST` is the host name used from sample PHPUnit containers to reach the local PostgreSQL service published on the host port. Override it when the Docker environment does not provide `host.docker.internal`.

Stop the local PostgreSQL service without deleting data:

```sh
make down-user-db-pgsql
```

Reset it with volume deletion:

```sh
make reset-user-db-pgsql
```

## Remaining Policy

Do not expand live PostgreSQL coverage just for count. Add new live PostgreSQL contract coverage only when a new sample introduces a genuinely new input or output behavior surface.

Mtool config store PostgreSQL support is not part of this completion definition.

## Verification

Completed after this slice:

```sh
make postgresql-user-db-test-local
make test
```

Results:

- `make postgresql-user-db-test-local`: passed. It completed sample10 MySQL/PostgreSQL output contract capture and comparison, then passed sample12 PostgreSQL input live schema import.
- `make test`: passed with `258 tests`, `9023 assertions`, `1 skipped`. The skipped test is the opt-in PostgreSQL live import when explicit `MTOOL_RUNTIME_PGSQL_*` values are not provided to the normal suite.
