# 2026-06-29 Shared Contract Manifest Spike

Status: `GATE0_FS_COMPLETED_WITH_GAP`

## Purpose

Gate 0 の最初の App-local feasibility study として、既存の `dataclass` / `dataclassfields` metadata だけから language-neutral contract manifest を出せるか確認した。

この spike は正式な Source Output 実装ではない。config DB schema、generator、tutorial reference は変更せず、standalone script で `work/feasibility/` に disposable output を作るだけにした。

## Implemented

- Added standalone script:
  - `mtool/scripts/experimental/shared_contract_manifest_spike.php`
- Input:
  - existing DataClass metadata via `app_fetch_data_class_metadata_snapshot`
  - optional generated PHP DataClass root for public property comparison
- Output:
  - `work/feasibility/shared-contract-manifest-sample02/manifest.json`
  - `work/feasibility/shared-contract-manifest-sample02/summary.json`

The `work/` output is intentionally not committed.

## Run

Target sample:

- `SAMPLE02`
- generated PHP comparison root: `sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP`

Command used inside sample02 `web-admin` container:

```bash
php /var/www/mtool/scripts/experimental/shared_contract_manifest_spike.php \
  --project-key=SAMPLE02 \
  --output-dir=/var/www/work/feasibility/shared-contract-manifest-sample02 \
  --generated-root=/var/www/sample/tutorials/sample02-dataclass-nullable-default-status/reference/DATACLASS-PHP
```

## Result

Summary:

- contract count: `1`
- field count: `7`
- generated PHP comparison: available
- generated PHP mismatch count: `0`
- known missing semantics:
  - nullable: `7`
  - default: `7`
  - key: `7`
  - enum-like status: `7`

Generated manifest field names matched generated PHP DataClass public properties:

- `id`
- `title`
- `status`
- `sortOrder`
- `isPinned`
- `publishedAt`
- `note`

Conclusion:

Existing DataClass metadata can produce a field-compatible language-neutral manifest. It can represent physical / logical / generated names without ambiguity for the tested sample. However, DataClass metadata alone does not carry enough semantics for nullable / default / key / enum-like status.

## Decision

The Shared Contract Manifest Spike is feasible as a skeleton, but not sufficient for App-local SQLite schema generation by itself.

This supports the original design assumption: generated DataClass and shared contract should not be the same canonical artifact.

Responsibility split confirmed by this spike:

| Artifact | Suitable responsibility | Not suitable as-is |
| --- | --- | --- |
| DataClass metadata / generated DataClass | implementation-facing DTO/class shape, generated field names, language/runtime output surface | canonical source for nullable/default/key/sync/storage semantics |
| Shared contract metadata | language-neutral data contract, persistence semantics, sync/storage role, no-code/operation inputs | direct replacement for generated language-specific classes |

The important finding is not merely that some manifest can be emitted. The finding is that DataClass metadata can describe the shape that generated code uses, but it does not carry the richer contract semantics needed by App-local DB, sync, or no-code runtime. Therefore, treating DataClass as the shared contract source of truth would either lose important semantics or force unrelated persistence/sync concerns into a generated class model.

Before promoting `DATACLASS-CONTRACT-JSON` to a real Source Output, the implementation plan needs one of these decisions:

- join table / column metadata when the DataClass originates from table sync;
- add explicit contract metadata separate from generated DataClass metadata;
- allow missing semantics only for DTO-only contracts and require richer metadata for App-local persistence.

The roadmap already favors separate contract metadata. This spike supports that direction and narrows the reason: the shared contract must preserve persistence and sync semantics independently from language-specific DataClass output.

## Next

Do not jump straight to a permanent Source Output yet.

Recommended next Gate 0 step:

1. Run App Local SQLite Schema Spike with a small manifest that includes explicit nullable / default / key values.
2. Use the result to decide the minimal contract metadata needed for real implementation.
3. If SQLite schema apply succeeds, promote Shared DataClass contract foundation as the next implementation work unit.

## Verification

- `php -l mtool/scripts/experimental/shared_contract_manifest_spike.php`
- `Sample2DataclassNullableDefaultStatusOutputTest` in sample02 stack
- Shared contract manifest script run against `SAMPLE02`
