# View Variant Preference First Slice

Date: 2026-07-08

## Status

Done as #428.

This slice adds persistent view variant preference as a separate presentation layer above interface usage intent.

## What changed

- Added `project_shared_contracts.view_variant_preference`.
- Added an idempotent config-initdb ALTER slice for `usage_intent` and `view_variant_preference`.
- Added bootstrap preflight coverage for the shared contract metadata columns.
- Shared contract repository read/write now carries `view_variant_preference`.
- Shared contract manifest normalization includes the preference.
- No-code screen definition now emits `view_variant_preference` per contract.
- Existing generated list/detail/form screen variants remain unchanged.
- Operator/admin no-code inspection includes preferred view variant in interface profiles.
- Shared Contracts admin UI can edit view variant preference alongside usage intent.

## Boundary

This does not build a full visual builder.

The preference is contract-level metadata for adapters and future generated UI choices. It does not replace the concrete screen-level variants that are already generated from screen type:

- `standard_table`
- `detail_record`
- `edit_form`

The new `review_list` option is accepted as a future adapter preference and as a useful dogfooding probe for Mtool review/admin screens.

## Mtool Dogfooding Note

Gradual Mtool no-code probes are worth doing even before the full A7 replacement plan. They validate the generated UI with real Mtool admin/lab data, expose missing settings/navigation links, and keep the no-code layer grounded on the database-first contract model.

## Verification

Completed verification:

- `php -l mtool/app/config_db_bootstrap.php`
- `php -l mtool/app/no_code_screen_definition.php`
- `php -l mtool/app/no_code_operator_inspection.php`
- `php -l mtool/app/project_shared_contracts_page.php`
- `git diff --check`
- `make test`

Result:

- `Tests: 342, Assertions: 11195, Skipped: 1.`
