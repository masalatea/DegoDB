# SSO app-user promotion first rehearsal / SSO app-user promotion first rehearsal記録

Date: 2026-07-13

## Summary / 概要

#875 now has a first SQLite-to-MySQL promotion rehearsal fixture for SSO app-user data.

#875に、SSO app-user dataを対象にした最初のSQLite-to-MySQL promotion rehearsal fixtureを追加した。

## Implemented evidence / 実装根拠

`SsoAppUserPromotionTest` creates a SQLite fixture through the existing runtime resolver:

- JIT creates `app_user`;
- repeat login restores the same `app_user_id`;
- profile refresh stores safe email/display name data and excludes token-like fields;
- `saved_item` stores application-owned data through an FK to `app_user`.

The test then builds a promotion rehearsal chain covering:

- `app_user`;
- `app_user_external_identity` with unique `issuer, subject`;
- `app_user_profile` with `profile_json`;
- `saved_item` with FK to `app_user`;
- target schema plan;
- deterministic SQLite export;
- import checkpoint evidence;
- verification artifact;
- cutover contract;
- operator package;
- rehearsal package.

## Boundary / 境界

This first #875 slice proves that runtime-created SQLite SSO app-user data can enter the promotion contract without losing the stable app user ID, external identity mapping, safe profile cache, or app-owned FK data.

このfirst #875 sliceでは、runtimeで作成されたSQLite SSO app-user dataが、stable app user ID、external identity mapping、safe profile cache、app-owned FK dataを失わずpromotion contractへ入ることを確認した。

Live MySQL post-promotion resolver behavior remains next. The current slice does not claim full MySQL SSO runtime qualification yet.

live MySQLでのpromotion後resolver behaviorは次段であり、このsliceではMySQL SSO runtime完全認定までは主張しない。
