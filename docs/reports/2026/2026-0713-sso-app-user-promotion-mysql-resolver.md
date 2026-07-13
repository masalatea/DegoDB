# SSO app-user promotion MySQL resolver qualification / SSO app-user promotion MySQL resolver認定

Date: 2026-07-13

## Summary / 概要

#875 is now qualified through the live MySQL/MariaDB resolver behavior that remained after the first rehearsal fixture.

#875は、first rehearsal fixture後に残っていたlive MySQL・MariaDB resolver behaviorまで認定した。

## Implemented evidence / 実装根拠

`sso_app_user_runtime.php` now dispatches the proof schema and profile upsert by PDO driver:

- SQLite keeps the existing proof schema and `ON CONFLICT` profile upsert.
- MySQL/MariaDB gets deterministic `CREATE TABLE IF NOT EXISTS` proof DDL.
- MySQL/MariaDB profile refresh uses `ON DUPLICATE KEY UPDATE`.
- JIT app-user creation now writes explicit `created_at` and `updated_at` values for both drivers.

For the promoted SSO external identity target, the canonical promotion manifest uses the SSO semantic identity pair, `issuer` and `subject`, as the primary key rather than preserving SQLite's AUTOINCREMENT surrogate. This keeps restored users and future JIT-created users independent from copied sequence state.

## Test evidence / テスト根拠

`SsoAppUserPromotionTest` now covers:

- runtime-created SQLite SSO app-user fixture creation;
- SQLite-to-MySQL target schema generation;
- deterministic SQLite export;
- transactional MySQL import with checkpoint updates;
- resolver restore on the promoted MySQL target for the same `issuer, subject`;
- preservation of application-owned `saved_item.app_user_id` FK data;
- safe profile refresh on MySQL;
- new JIT SSO login on the promoted MySQL target.

Executed checks:

- `php -l mtool/app/sso_app_user_runtime.php`
- `php -l tests/Integration/SsoAppUserPromotionTest.php`
- focused runtime PHPUnit: `SsoAppUserRuntimeTest` passed, 4 tests / 26 assertions
- focused promotion PHPUnit without live env: `SsoAppUserPromotionTest` passed with live test skipped, 2 tests / 17 assertions / skipped 1
- focused promotion PHPUnit with `PROMOTION_MYSQL_TEST_DB=mtool_promotion_test_sso`: `SsoAppUserPromotionTest` passed, 2 tests / 38 assertions
- `make test` passed, 593 tests / 15152 assertions / skipped 5

## Boundary / 境界

This qualifies the supported #875 behavior: SQLite-first SSO app-user data can be promoted to MySQL/MariaDB and the resolver can continue both repeat-login restore and new JIT login behavior on the promoted target.

これは#875のsupported behaviorを認定する。SQLite-firstのSSO app-user dataをMySQL・MariaDBへ昇格し、昇格後targetでresolverがrepeat-login restoreと新規JIT loginの双方を継続できる。

This does not add zero-downtime CDC, bidirectional sync, MySQL-to-SQLite rollback import, or automatic production cutover. Those remain outside the v1 promotion contract.

zero-downtime CDC、双方向sync、MySQLからSQLiteへのrollback import、自動production cutoverは追加していない。これらはv1 promotion contractの範囲外のまま。

## Next / 次

Proceed to #876: promotion lane checkpoint and integration. Record the supported boundary, test evidence, operational guide state, remaining demand-driven scale work, and PR/commit shape.

#876へ進む。supported boundary、test evidence、operational guide state、残るdemand-driven scale work、PR・commit形を記録する。
