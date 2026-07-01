# 2026-06-29 Shared Contract Core Vocabulary

Status: `COMPLETED`

## Purpose

Gate 0 FS の結果を受けて、Shared DataClass contract foundation に入る前の小さい共通前提として、shared contract manifest v0 の最小語彙と validator を固定した。

FS で確認した要点:

- DataClass metadata can describe generated DTO shape.
- DataClass metadata alone is not enough for nullable / default / key / persistence / sync semantics.
- App-local SQLite schema generation needs explicit language-neutral contract semantics.

Therefore, this slice defines the smallest common contract shape before adding generator / TypeScript DTO output.

## Implemented

- Added shared contract core module:
  - `mtool/shared/shared_contract_core.php`
- Added container mount for shared code:
  - `compose.yaml` mounts `./mtool/shared` into both `web-admin` and `web-lab`.
- Added contract test:
  - `tests/Integration/SharedContractCoreVocabularyTest.php`

## Vocabulary

The v0 manifest shape is:

- `manifest_version`
- `contracts`
- `contract_key`
- `entity`
  - `logical_name`
  - `physical_name`
  - `generated_name`
- `fields`
  - `logical_name`
  - `physical_name`
  - `generated_name`
  - `type`
  - `nullable`
  - `default`
  - `is_key`
  - `storage_role`
- `local_metadata`
  - `reserved_columns`
  - `collision_policy`

The initial supported field types are:

- `integer`
- `string`
- `text`
- `boolean`
- `datetime`

The local metadata reserved columns are:

- `local_updated_at`
- `last_synced_at`
- `sync_status`
- `dirty`
- `tombstone`

The v0 collision policy is `reject`.

## Fixture

The first fixture is a sample02/task-shaped manifest:

- contract key: `task`
- fields:
  - `id`
  - `title`
  - `status`
  - `sort_order`
  - `is_pinned`
  - `published_at`
  - `note`

This fixture preserves the semantics missing from DataClass-only metadata:

- key: `id`
- default: `status = draft`, `sort_order = 0`, `is_pinned = false`
- nullable: `published_at`, `note`
- local metadata collision policy: reject reserved names such as `dirty`

## Validation

The validator rejects:

- unsupported manifest version
- empty / malformed contract list
- invalid or duplicate `contract_key`
- missing entity identity
- missing field identity
- unsupported field type
- missing non-boolean `nullable`
- missing `default`
- non-boolean `is_key`
- missing key field
- duplicate physical / generated field names
- business field collision with reserved local metadata columns
- non-`reject` local metadata collision policy

## Decision

This completes the small common prerequisite before the larger Shared DataClass contract foundation.

The next implementation unit is Shared DataClass contract foundation:

- persist / derive separate contract metadata;
- generate the language-neutral manifest from real project metadata;
- add TypeScript DTO first output;
- compare generated contract output against DataClass field shape.

## Verification

- `php -l mtool/shared/shared_contract_core.php`
- `php -l tests/Integration/SharedContractCoreVocabularyTest.php`
- `make test`
  - `267 tests`
  - `9106 assertions`
  - `Skipped: 1`

