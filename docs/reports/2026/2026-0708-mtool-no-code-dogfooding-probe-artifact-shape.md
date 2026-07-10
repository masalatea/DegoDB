# 2026-07-08 Mtool no-code dogfooding probe artifact shape

Status: `FIRST_SLICE_DONE`

## Summary

#434 proves that the Mtool dogfooding probe can use the same no-code runtime artifact shape as ordinary no-code projects.

The probe does not need a hand-edited generated artifact. The `MTOOL` Source Output review fixture can flow through the existing no-code runtime payload and emitted-file builder.

## Accepted Capability

Focused coverage now verifies:

- `app_no_code_mtool_dogfooding_probe_screen_definition()` builds the MTOOL Source Output review definition.
- `app_project_output_no_code_runtime_payload('MTOOL', ...)` accepts that definition.
- `app_project_output_no_code_runtime_build_emitted_files()` emits:
  - `screen-definition.json`
  - `runtime-preview.json`
  - `runtime-preview.html`
  - `README.md`
- The screen definition carries `mtool_source_output_review`.
- The preferred view variant remains `review_list`.
- The HTML preview renders the Mtool Source Output Review list title.

## Custom Extension Boundary Check

This slice intentionally keeps custom behavior out of generated files:

- no generated HTML hand edit;
- no custom React component implementation yet;
- no custom slot schema yet;
- no custom operation added only to satisfy the probe.

The output proves that the standard generated layer can carry the first Mtool review surface. #435 should inspect the resulting surface and classify any missing behavior as one of:

- configured presentation;
- custom UI slot;
- custom operation / Custom Proxy;
- full custom app handoff.

## Verification

- `php -l tests/Integration/NoCodeScreenDefinitionTest.php`
- `docker exec mtool-rebuild-web-admin-1 phpunit /var/www/tests/Integration/NoCodeScreenDefinitionTest.php`
  - Result: `OK (7 tests, 69 assertions)`
  - Note: PHPUnit emitted a cache write warning for `.phpunit.result.cache` because that container path is read-only; test execution still passed.

## Next Step

#435 should close the first Mtool dogfooding probe by recording concrete findings from the Source Output review surface: what standard generation already covers, what should become configured presentation, and what should become custom slot / custom operation candidates.
