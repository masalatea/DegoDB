# Runtime-data mobile density first slice

Date: 2026-07-07

## Summary

#397 adds the first mobile-density slice for generated runtime-data controls.

The generated current/alias runtime preview now treats the runtime-data control header, query summary, and row groups more deliberately on narrow screens. At mobile width, the label and active-query summary occupy their own rows, each control row group stretches to the available width, and long query-summary tokens can wrap instead of forcing horizontal overflow.

## Accepted capability

- Generated runtime-data controls remain usable at a 390px viewport without horizontal control overflow.
- Search/filter/sort/page-size row groups keep their grouped shape, but stack to the mobile width instead of becoming narrow inline clusters.
- Active query-summary tokens can wrap safely on mobile.
- The shared public runtime browser smoke records a mobile screenshot and `mobileRuntimeDataControls` metrics for sample28, sample29, and sample31 current/alias runtime paths.
- Artifact-key previews that do not expose runtime-data controls are explicitly treated as a skipped mobile-control probe, not as a product failure.

## Preserved boundary

- Runtime-data URL/query values are unchanged.
- Endpoint parsing and `runtime-data.json` contracts are unchanged.
- Sample data, mutation behavior, sync outbox behavior, and current/alias routing are unchanged.
- This is a generated runtime CSS and browser-smoke viewport assertion change only.

## Verification

Passed:

- `php -l mtool/app/no_code_runtime.php`
- `node --check mtool/scripts/check_no_code_runtime_preview_ui_smoke.js`
- `git diff --check`
- `make sample-no-code-public-runtime-browser-smoke`

The umbrella public runtime smoke passed through sample28, sample29, and sample31 `ok: true` outputs. The mobile probe reported visible runtime-data controls at `390px` with `overflowCount: 0`, `narrowRowGroupCount: 0`, and `tokenOverflowCount: 0` for current/alias runtime paths.

Full `make test` was not rerun because the code change is limited to generated runtime CSS and browser-smoke viewport assertions, and the cross-profile public runtime smoke matrix covered the touched behavior.

## Push / history

Push was not performed. History was not rewritten.
