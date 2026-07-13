# SQLite to MySQL Cutover Contract

Date: 2026-07-13

## Scope

This records the first #873 cutover / rollback slice.

Implemented:

- Side-effect-free `sqlite-mysql-cutover-plan-v1` artifact builder.
- Cutover is allowed only when all of the following are present:
  - ready promotion manifest;
  - cutover-ready verification artifact for the same manifest digest;
  - write freeze evidence;
  - final source snapshot digest;
  - final verification digest;
  - target config reference;
  - post-cutover smoke reference and pass evidence;
  - retained SQLite source reference;
  - rollback procedure reference;
  - rollback window end timestamp;
  - explicit approvals: `freeze_confirmed`, `switch_approved`, `rollback_acknowledged`.
- Automatic source deletion is explicitly forbidden.
- Secret-bearing or unsafe references fail closed and are not copied into the safe artifact.

This slice does not mutate deployment config. It produces the approval-bound contract that a later rehearsal / packaging step can consume.

## Verification Evidence

- `php -l mtool/app/sqlite_mysql_cutover.php`
- `php -l tests/Integration/SqliteMysqlCutoverTest.php`
- Focused cutover PHPUnit:
  - `OK (3 tests, 21 assertions)`
- `make test`:
  - `OK, but incomplete, skipped, or risky tests! Tests: 584, Assertions: 15043, Skipped: 4.`

## Remaining #873 Work

- Rehearsal / operator package for the actual config switch boundary.
- Rollback rehearsal evidence format.
- Integration with the representative promotion sample.

## Plan State

`docs/current-plans.md` #873 moved from `PLANNED` to `FIRST_CONTRACT_DONE_REHEARSAL_NEXT`.
