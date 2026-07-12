# Transaction Full Real Guarded Execution Smoke Preflight

## Finding

Sample18 can support a real guarded browser smoke for its application-database transaction, but it cannot honestly prove one atomic transaction across every route-side write.

The current execution order is:

1. append/request idempotency and audit metadata in the config database;
2. open the Sample18 application-database transaction;
3. invoke one generated `TaskCardDBAccess` mutation;
4. commit or roll back the application database;
5. record execution audit and idempotency outcome in the config database after commit.

The application DB and config DB are separate transaction domains. Post-commit recording failure is intentionally represented as recovery-required; it cannot roll back an already committed application mutation.

## Claim boundary

The browser smoke may prove:

- the authenticated guarded route opens the application DB transaction;
- successful generated DBAccess mutation commits and becomes visible;
- a generated DBAccess failure after issuing SQL rolls back the application mutation;
- rollback skips post-commit execution recording;
- commit followed by config DB recording failure is reported as recovery-required, not falsely reported as full rollback.

It must not claim:

- distributed atomicity across application and config databases;
- that request audit/idempotency metadata is rolled back with application data;
- multiple required application mutations, because the current Sample18 operation invokes one DBAccess method.

The existing Sample14 composite transaction proof remains the evidence for multiple same-database required updates sharing one transaction.

## Failure injection decision

Do not add a production HTTP failure flag or a general-purpose failure hook.

Use a smoke-only runtime reference directory selected through the existing explicit runtime-reference configuration. Its generated DBAccess method will:

1. execute the same application SQL through the shared transaction handle;
2. return a controlled DBAccess failure after SQL;
3. allow the existing route transaction adapter to perform rollback.

The harness must be isolated, opt-in, and removed after the smoke. The normal generated runtime reference remains unchanged.

## Proposed scenarios

### Commit

- authenticate;
- enable mutation and executor flags explicitly;
- use the normal generated runtime reference;
- submit a uniquely named create operation;
- require HTTP success, `transaction_status=committed`, no recovery requirement;
- query application DB and require exactly one row;
- remove the row during cleanup.

### Rollback

- restart or configure the isolated smoke with the failure runtime reference;
- submit a second uniquely named create operation;
- require HTTP failure, `transaction_status=rolled_back`, `rolled_back=true`, and no commit;
- query application DB and require zero matching rows;
- require no post-commit execution recording;
- remove temporary runtime files and restore configuration.

### Recovery visibility

Keep this as a separate injected-contract test unless a safe isolated config-DB failure harness is added. Do not corrupt the shared config database merely to force browser-visible recovery.

## Next implementation slice

Build the isolated runtime-reference fixture and route-level HTTP/browser harness first. If the container cannot switch references without broad production code changes, stop and keep the existing real DB PHPUnit coverage as the highest safe evidence.

## Reference selection first slice

The existing app-level runtime-reference directory setting now also accepts `MTOOL_SAMPLE18_GENERATED_SUBMIT_RUNTIME_REFERENCE_DIR` as an env fallback. App config remains authoritative, env is second, and the sample reference is the default. The existing complete-file validation still fails closed before execution, allowing an isolated HTTP container to select a smoke-only full generated reference without adding any request-level failure switch.

## Failure reference fixture slice

`mtool/scripts/create_sample18_failure_runtime_reference.php` now copies the full generated reference into a caller-selected empty directory outside the repository. It replaces only the `TaskCardDBAccess` wrapper: the override calls the generated parent insert first, then returns a controlled DBAccess error. A `smoke-fixture.json` manifest marks the directory smoke-only and records the expected rollback outcome. The canonical sample reference and generated base class remain untouched.

## HTTP proof completed

The isolated guarded-route harness now passes both scenarios against MariaDB: normal reference commits exactly one row, while the failure-after-SQL reference reports `rolled_back` and leaves zero rows. Both runs use authenticated CSRF-protected HTTP requests and fresh isolated Compose volumes, followed by row, stack, volume, and fixture cleanup.
