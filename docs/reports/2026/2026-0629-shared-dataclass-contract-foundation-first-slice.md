# Shared DataClass Contract Foundation First Slice

Status: `COMPLETED`

Date: 2026-06-29

## Summary

Shared DataClass contract foundation の first slice として、DataClass metadata と table metadata を join し、shared contract manifest v0 を作る app-layer builder を追加した。

This does not complete the entire Shared DataClass contract foundation plan. It completes the first implementation slice:

- generate a language-neutral manifest from existing project metadata;
- carry field type / nullability / default / key semantics from table metadata;
- validate the result with `mtool/shared/shared_contract_core.php`;
- compare generated manifest field shape back to existing DataClass field shape.

Explicit contract metadata and first TypeScript DTO output remain active follow-up work.

Follow-up within the same foundation lane added `shared-contract-json` as a generated source output strategy. The manifest builder can now be reached through the normal artifact generation path and emits:

- `shared-contract.json`
- `shared-contract-report.json`
- `README.md`

A second follow-up added `shared-contract-typescript` as the first TypeScript DTO output from the same shared contract manifest. It emits:

- `dto.ts`
- `README.md`

A final foundation follow-up added explicit shared contract metadata as separate config tables:

- `project_shared_contracts`
- `project_shared_contract_fields`

The manifest builder now merges that metadata into `contract_metadata` objects without adding flags to `dataclass` / `dataclassfields`.

## Added

- `mtool/app/shared_contract_manifest.php`
  - `app_shared_contract_manifest_from_project`
  - `app_shared_contract_manifest_from_snapshots`
  - `app_shared_contract_manifest_compare_dataclass_shape`
- `tests/Integration/SharedDataClassContractFoundationTest.php`
  - verifies manifest generation from bootstrapped SQLite config metadata;
  - verifies field semantics for integer / text / datetime / nullable / default / key cases;
  - verifies DataClass shape mismatch detection.
- `mtool/app/project_output_shared_contract_generator.php`
  - adds `shared-contract-json` source output generation;
  - publishes the manifest and validation / compare report as generated artifacts.
- `mtool/app/project_output_typescript_dto_generator.php`
  - adds `shared-contract-typescript` source output generation;
  - publishes TypeScript DTO interfaces from the shared contract manifest.
- `docker/mariadb/config-initdb/036_shared_contract_metadata.sql`
  - adds explicit shared contract and field metadata tables.
- `mtool/app/shared_contract_metadata_repository_pdo.php`
  - adds fetch / upsert helpers for explicit contract metadata.

## Design Result

This first slice turns the earlier FS finding into product code:

- DataClass remains the implementation-facing generated class / DTO shape.
- Shared contract manifest is a language-neutral artifact that carries persistence semantics.
- Table metadata is currently the bridge for nullable / default / key semantics.
- Explicit contract metadata now carries semantics that are not pure table facts, such as sync role, no-code role, operation role, notes, and app-local persistence role.
- `shared-contract-json` is now the first generated artifact form for this contract, separate from generated PHP DataClass output.
- `shared-contract-typescript` is now the first App-facing DTO artifact generated from the shared contract.
- Explicit contract metadata is separate from DataClass metadata and can be merged into the manifest without changing generated PHP DataClass shape.

Therefore, this slice supports the prior decision: generated DataClass and shared contract should be related and comparable, but not collapsed into one canonical artifact.

## Verification

Ran:

```sh
php -l mtool/app/shared_contract_manifest.php
php -l mtool/app/shared_contract_metadata_repository_pdo.php
php -l mtool/app/project_output_shared_contract_generator.php
php -l mtool/app/project_output_typescript_dto_generator.php
php -l tests/Integration/SharedDataClassContractFoundationTest.php
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/SharedDataClassContractFoundationTest.php
make test
```

Result:

```text
Tests: 272, Assertions: 9282, Skipped: 1.
```

## Completion Boundary

This completes the foundation work unit originally scoped as:

1. explicit shared contract metadata separate from `dataclass` / `dataclassfields`;
2. manifest generation from DataClass + table metadata + explicit contract metadata;
3. generated shared contract JSON output;
4. first generated TypeScript DTO output;
5. compare/report behavior for DataClass shape.

Remaining deeper semantics, such as managed operation policy and no-code screen behavior, belong to the next roadmap work units.
