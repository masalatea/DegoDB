# Generic deterministic scan contract / 汎用deterministic scan contract

Date: 2026-07-13

Status: `DONE_GENERIC_SCAN`

## Summary

#878 lifts the deterministic advisory scan out of the Sample19-specific task packet builder into a provider-free generic scan module.

The scan remains deliberately weak: it records source-bound structural facts only. It does not infer entities, relationships, schema types, candidate fields, or acceptance decisions.

## Implemented

Added:

- `mtool/app/task_packet_scan.php`
  - `APP_TASK_PACKET_SCAN_VERSION = mtool-deterministic-source-scan-v1`
  - `app_task_packet_scan_json(string $sourceBytes, string $rootPointer = '')`
  - configurable JSON root pointer;
  - source SHA-256 binding;
  - advisory authority;
  - pointer/type/object-key/array-count items;
  - empty `inference`;
  - `mutation_performed=false`.

Updated:

- `mtool/app/schema_proposal_task_packet.php`
  - Sample19 scan generation now delegates to the generic scanner with root pointer `/article`;
  - the old `app_schema_proposal_deterministic_scan_json()` function remains as a compatibility wrapper.

Added tests:

- `tests/Integration/TaskPacketScanTest.php`
  - deterministic output;
  - root pointer selection;
  - escaped JSON Pointer segments;
  - invalid JSON rejection;
  - missing root pointer rejection;
  - advisory/no-inference/no-mutation fields.

Updated tests:

- `tests/Integration/SchemaProposalTaskPacketTest.php`
  - Sample19 task packets now expect the generic scan version while preserving `/article` scan behavior.

## Boundary

This is not an AI inference layer. It is a local scan artifact for humans, Codex/Claude, or optional local fallback tooling to read as advisory context.

The authoritative ordering remains:

1. source;
2. canonical context;
3. output contract;
4. deterministic scan;
5. optional fallback candidate.

If scan content conflicts with source, source wins.

## Verification

- `php -l mtool/app/task_packet_scan.php`
- `php -l mtool/app/schema_proposal_task_packet.php`
- `php -l tests/Integration/TaskPacketScanTest.php`
- `php -l tests/Integration/SchemaProposalTaskPacketTest.php`
- focused `TaskPacketScanTest`: 4 tests / 19 assertions
- focused `SchemaProposalTaskPacketTest`: 3 tests / 28 assertions
- `make test`: 597 tests / 15175 assertions / skipped 5

## Next

Proceed to #879: replace the Sample19-only local fallback command boundary with a generic task-bound CLI that still requires explicit execution and writes only declared advisory fallback artifacts.
