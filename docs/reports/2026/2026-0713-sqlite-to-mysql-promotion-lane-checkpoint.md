# SQLite-to-MySQL promotion lane checkpoint / SQLite-to-MySQL promotion lane checkpoint

Date: 2026-07-13

## Summary / 概要

#876 closes the SQLite-first to MySQL/MariaDB promotion lane as integration-ready for the current v1 boundary.

#876で、SQLite-firstからMySQL・MariaDBへのpromotion laneを、現在のv1 boundaryとしてintegration-readyにした。

## Supported boundary / supported boundary

The supported v1 path is offline and one-way:

- freeze SQLite writes before final copy;
- create into a fresh empty MySQL/MariaDB target;
- build a deterministic promotion manifest from canonical metadata;
- generate/review target schema before mutation;
- export SQLite rows in stable order;
- import MySQL chunks transactionally with checkpoint evidence;
- verify row counts, PK sets, row values, nullability, unique keys, FKs, JSON/BLOB/timestamp values, sequence safety, and DBAccess smoke evidence;
- cut over only after explicit freeze, final verification, smoke, source retention, rollback window, operator package, and approvals;
- retain SQLite as rollback evidence.

The lane explicitly does not include MySQL-to-SQLite reverse migration, bidirectional sync, zero-downtime CDC, automatic production cutover, or automatic source deletion.

## Documentation added / documentation added

Added the stable guide:

- `docs/sqlite-to-mysql-promotion.md`

Connected it from:

- `docs/README.md`
- `docs/current-supported-workflow.md`
- `docs/proof-matrix.md`
- `docs/current-plans.md`

The guide records the artifact chain, operator checklist, SSO app-user promotion boundary, test evidence anchors, live MySQL opt-in environment, and non-goals.

## Test evidence / test evidence

Recent evidence on this branch:

- `php mtool/scripts/validate_sample33_promotion.php`: OK / `promotion_rehearsal_ready`
- `SsoAppUserPromotionTest` focused without live env: 2 tests / 17 assertions / skipped 1
- `SsoAppUserPromotionTest` focused with `PROMOTION_MYSQL_TEST_DB=mtool_promotion_test_sso`: 2 tests / 38 assertions
- `SsoAppUserRuntimeTest`: 4 tests / 26 assertions
- `make test`: 593 tests / 15152 assertions / skipped 5

Earlier lane reports also record focused/live evidence for target schema, import, verification, cutover, operator package, rehearsal package, and sample33 validator.

## PR shape / PR shape

Current branch:

- `feature/sqlite-to-mysql-promotion-plan`

Divergence at checkpoint time:

- ahead of `develop`: 23 commits
- behind `develop`: 0 commits

Recommended integration shape:

- Open PR from `feature/sqlite-to-mysql-promotion-plan` to `develop`.
- Do not squash by default; the branch is already organized as a semantic promotion stack.
- PR summary should group commits by promotion phases:
  1. planning/manifest/schema/export/import;
  2. verification/cutover/operator/rehearsal;
  3. sample33 tutorial/validator;
  4. SSO app-user promotion qualification;
  5. lane checkpoint documentation.

## Next / 次

The main promotion lane has no known feasibility blocker inside the v1 boundary. Further work should be demand-driven, for example:

- hands-on operator wrapper if a real operation needs it;
- larger fixture coverage if a concrete schema shape demands it;
- UI/admin surface only if an operator workflow needs it;
- scale/performance hardening only with a target data-size requirement.

The independent optional scan/Ollama fallback lane (#877-#883) remains parked until selected.
