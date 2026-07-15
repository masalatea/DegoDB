# Firebird Sample Coverage Checkpoint / Firebird sample coverage確認

Date: 2026-07-14

Status: `F100_3_DONE_SAMPLE_COVERAGE_CONFIRMED`

## Purpose / 目的

Confirm whether the sample-first Firebird lane has enough evidence to move from representative samples into Mtool itself.

This checkpoint does not claim Firebird 100% completion. It only answers whether F100-2 produced enough sample evidence for F100-4.

## Completed sample-first evidence / 完了済みsample-first evidence

| Area | Evidence | Result |
| --- | --- | --- |
| Basic generated DBAccess read | sample05 Firebird DBAccess smoke | Proven |
| Join/read-model SQL | sample08 Firebird DBAccess smoke | Proven |
| Aggregate/report SQL | sample09 Firebird DBAccess smoke | Proven |
| CRUD/list generated runtime | sample18 Firebird DBAccess smoke | Proven |
| No-code runtime read/presentation | sample18 Firebird no-code runtime smoke | Proven |
| Transaction Full reuse | sample18 Firebird transaction smoke | Proven |
| Guarded mutation route | sample18 Firebird guarded route smoke | Proven |
| Rich catalog relationships | sample21 Firebird catalog smoke | Proven |
| Workflow/state transition | sample22 Firebird workflow smoke | Proven |
| Local durable profile boundary | sample27 Firebird server DTO -> App-local SQLite smoke | Proven |

## Driver/runtime findings / driver-runtime findings

- Existing generated DBAccess classes can run against PDO Firebird when the runtime DSN points to Firebird.
- Firebird-compatible runtime adapters are intentionally narrow:
  - trailing `LIMIT ?` is rewritten to `ROWS ?`;
  - `NOW()` is rewritten to `CURRENT_TIMESTAMP`.
- Firebird explicit-ID seed rows may require identity restart / sequence alignment.
- Firebird text values are represented with `BLOB SUB_TYPE TEXT` in the smoke schemas and hydrate correctly through PDO.
- Normal `make test` remains independent of Firebird; Firebird smokes are opt-in.

## sample30 decision / sample30判断

The original representative matrix listed `sample30-no-code-app-local-sync-demo` for local sync/offline-ish profile boundaries.

After inspecting the current sample30 implementation, this checkpoint defers Firebird-specific sample30 work:

- sample30's normal server-side handler currently uses a temporary SQLite server DB plus generated `SyncTaskDBAccess`;
- its main uniqueness is managed operation sync outbox, App-local identity/SSO-shaped actor handoff, failed outbox visibility, and no-code runtime sync hints;
- those concerns are primarily config-store/outbox/profile behavior, not a new generated DBAccess dialect or App-local persistence boundary;
- sample27 already proves the Firebird local durable server row -> DTO -> App-local SQLite handoff boundary.

Therefore, sample30 does not block moving to F100-4. Reopen sample30 only if F100-4 or F100-5 reveals a concrete Firebird profile gap in managed operation outbox processing, actor handoff persistence, or sync cutover behavior.

## Coverage conclusion / coverage conclusion

F100-2 is complete for the agreed representative sample-first scope.

F100-3 is complete because remaining sample-level gaps are either:

- covered by existing Firebird smokes; or
- explicitly deferred to the correct later lane.

The next active step is F100-4: adapt Mtool itself to an opt-in Firebird config-store/runtime profile.

## F100-4 starting guidance / F100-4開始指針

Start with a narrow, opt-in Mtool profile rather than broad replacement:

1. Inventory current config-store dialect assumptions in repositories and bootstrap/migration paths.
2. Reuse evidence from Firebird source-inspection and sample smokes.
3. Add the smallest Firebird profile initialization path.
4. Prove Mtool can initialize and operate against Firebird without changing MySQL/MariaDB or SQLite defaults.
5. Only after that, proceed to SQLite -> Firebird and Firebird -> MySQL/MariaDB migration paths.

## Validation / 検証

This checkpoint records documentation/planning state only. The immediately preceding sample27 slice was verified with:

```bash
php -l mtool/scripts/check_sample27_firebird_app_local_persistence_smoke.php
make sample27-firebird-app-local-persistence-smoke-docker
make sample27-pack-runtime-test
make test
git diff --check
```

Latest observed full regression result:

- `make test`: 632 tests, 15395 assertions, 5 skipped.
