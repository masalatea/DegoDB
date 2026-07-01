# 2026-06-29 DTO Save/Read Spike

Status: `GATE0_FS_COMPLETED`

## Purpose

Gate 0 の 3 本目として、shared DTO shape を App-local SQLite DB に保存し、同じ shape で読み戻せるか確認した。

This spike checks the minimum core of:

```text
server fixture row
-> DTO object
-> local SQLite save
-> local SQLite read
-> DTO compare
```

The previous SQLite schema spike generated and applied the local `task` schema. This spike uses that disposable SQLite DB under `work/feasibility/`.

## Implemented

- Added standalone script:
  - `mtool/scripts/experimental/dto_save_read_spike.php`
- Input:
  - SQLite DB from App Local SQLite Schema Spike
  - two DTO-shaped fixture rows
- Output:
  - `work/feasibility/dto-save-read-sample02/dto-input.json`
  - `work/feasibility/dto-save-read-sample02/summary.json`

The `work/` output is intentionally disposable and is not committed.

## Run

```bash
php mtool/scripts/experimental/dto_save_read_spike.php \
  --sqlite-path=work/feasibility/app-local-sqlite-schema-sample02/app-local.sqlite \
  --output-dir=work/feasibility/dto-save-read-sample02
```

## Result

Summary:

- round trip count: `2`
- all DTO reads matched input DTO shape
- key field survived:
  - `id`
- default-like fields survived:
  - `status`
  - `sortOrder`
  - `isPinned`
- nullable fields survived:
  - `publishedAt`
  - `note`
- local metadata was kept outside the DTO shape:
  - `dirty = 1`
  - `sync_status = dirty`
  - `tombstone = 0`
  - `local_updated_at` present
  - `last_synced_at = null`

The tested DTO shape:

```json
{
  "id": 1001,
  "title": "Draft local task",
  "status": "draft",
  "sortOrder": 10,
  "isPinned": false,
  "publishedAt": null,
  "note": "saved by DTO Save/Read Spike"
}
```

## Decision

DTO-shaped rows can be saved to and read from the App-local SQLite schema without shape loss in this minimal spike.

This supports the App-local persistence direction:

- shared contract can drive local schema;
- DTO shape can remain free of local metadata columns;
- local metadata can track dirty / sync state beside the business fields;
- null / default / key behavior is understandable when the contract carries those semantics explicitly.

This does not yet prove generated TypeScript DBAccess or browser SQLite runtime behavior. It proves the data-shape and local-schema boundary is workable.

## Runtime Note

The FS catalog originally suggested a Node-based harness first. Local Node did not have a SQLite library available, and choosing/installing one would have made library selection dominate the experiment. This spike therefore uses PHP PDO SQLite as a dependency-free harness.

TypeScript / Node / browser runtime selection remains a later implementation decision.

## Remaining Risks

- Generated TypeScript DTO is not implemented.
- Generated App-local DBAccess is not implemented.
- Browser worker / SQLite WASM / OPFS behavior is not tested.
- Server read is represented by fixture DTO rows, not a live server DBAccess call.
- Dirty/sync lifecycle only records initial dirty state; synced / tombstone transitions are not covered.

## Next

Gate 0 now has enough evidence for the core data path:

1. DataClass metadata can describe generated DTO shape but lacks persistence semantics.
2. Explicit shared contract semantics can generate App-local SQLite schema.
3. DTO-shaped rows can round trip through that schema without shape loss.

Recommended next step:

- Promote Shared DataClass contract foundation as the next implementation work unit.
- First implementation should add explicit shared contract metadata rather than treating generated DataClass as the contract source of truth.

Operation Policy Evaluator can remain a later FS or implementation-support spike for managed data operation layer.

## Verification

- `php -l mtool/scripts/experimental/dto_save_read_spike.php`
- `php mtool/scripts/experimental/dto_save_read_spike.php --help`
- `php mtool/scripts/experimental/dto_save_read_spike.php --sqlite-path=work/feasibility/app-local-sqlite-schema-sample02/app-local.sqlite --output-dir=work/feasibility/dto-save-read-sample02`
