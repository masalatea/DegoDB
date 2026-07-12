# Mtool Source Output Hybrid Contract Browser Evidence

## Status

`DONE`

## Summary

Added a browser smoke script for the MTOOL Source Output hybrid contract marker:

```bash
node mtool/scripts/check_mtool_source_output_hybrid_contract_browser_smoke.js --expect=off
node mtool/scripts/check_mtool_source_output_hybrid_contract_browser_smoke.js --expect=enabled
```

The script verifies the existing admin browser path rather than adding a new route.

## Script coverage

The smoke checks:

- unauthenticated inspection route redirects to login;
- expected off-state hides the hybrid marker and shows a not-found page;
- expected enabled-state renders exactly one `data-mtool-no-code-hybrid-contract="true"` marker;
- enabled marker JSON parses and reports `no-code-mtool-source-output-inspection-hybrid-v0`;
- contract route path remains `/projects/MTOOL/source-outputs/no-code-inspection`;
- `generated_post_execution` remains explicitly excluded;
- runtime execution controls are absent;
- guarded submit controls are absent;
- editable review form screen is absent;
- canonical Source Outputs return link is present when enabled;
- no POST targets the inspection route;
- no POST targets Source Output operation routes.

## Browser verification performed

1. Default-off stack:
   - `make up-mtool`
   - browser smoke with `--expect=off`
   - result: `ok=true`, marker count `0`, inspection POST count `0`, Source Output operation POST count `0`.

2. Enabled stack:
   - `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=1 make up-mtool`
   - browser smoke with `--expect=enabled`
   - result: `ok=true`, marker count `1`, inspection POST count `0`, Source Output operation POST count `0`.

3. Rollback/default-off restored:
   - `make up-mtool`
   - browser smoke with `--expect=off`
   - result: `ok=true`, marker count `0`, inspection POST count `0`, Source Output operation POST count `0`.

The local stack was left in default-off state.

## Verification notes

On this macOS sandbox, Playwright's bundled Chromium was not installed and direct Chrome launch needed access to local Chrome support files. The verification used:

```bash
PLAYWRIGHT_CHROME_EXECUTABLE='/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' \
  node mtool/scripts/check_mtool_source_output_hybrid_contract_browser_smoke.js ...
```

## Next

#800 should close this browser-evidence slice and choose the next contained productization step. Entry-point hardening is now reasonable to consider, but mutation, broad Source Output replacement, and public/lab/current/alias exposure remain out of scope.
