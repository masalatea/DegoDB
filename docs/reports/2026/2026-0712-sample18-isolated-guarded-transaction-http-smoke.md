# Sample18 Isolated Guarded Transaction HTTP Smoke

## Result

The authenticated Sample18 generated-submit route now has isolated real HTTP evidence for its application transaction.

### Normal generated reference

- explicit mutation and executor flags enabled;
- authenticated login and CSRF-protected create request;
- HTTP 200 and `result=executed`;
- `transaction_status=committed`;
- the unique task title existed exactly once in MariaDB;
- the committed smoke row was deleted before the stack reset.

### Failure-after-SQL generated reference

- full temporary runtime reference selected through the env configuration;
- wrapper executed the real parent insert, then returned the controlled DBAccess failure;
- HTTP 500 and `result=failed`;
- `transaction_status=rolled_back` and `rolled_back=true`;
- post-commit recording remained empty;
- the unique task title had zero rows in MariaDB.

### Isolation and cleanup

- unique Compose project and host ports;
- separate fresh volumes for commit and rollback runs;
- temporary failure runtime under `work/tmp`;
- trap-based stack, volume, and fixture cleanup;
- no request-level production failure switch.

Run with:

```sh
make sample18-guarded-transaction-http-smoke
```

This proves the Sample18 application DB transaction only. It does not claim distributed atomicity with config DB audit/idempotency connections, nor multiple required application mutations. Sample14 remains the composite same-database Transaction Full proof.

