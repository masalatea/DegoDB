# App surface config first slice

## Status

`EF_M12_FIRST_SLICE_DONE`

## Purpose

Implement the first bounded `app_surface_config` slice so one Mtool design can describe PWA, Flutter WebView wrapper, React/Web + Capacitor, or combined output surfaces while keeping the backend endpoint shared by default.

## Implementation

`output-mode-config.json` now includes:

- `supported_app_surfaces`;
- `app_surface_config.schema_version`;
- `app_surface_config.selected_surfaces`;
- shared `backend_endpoint` policy;
- per-surface policy for:
  - `pwa`;
  - `flutter_webview`;
  - `react_web_capacitor`.

The backend endpoint policy records:

- shared-by-default behavior;
- API base URL policy from the handoff packet;
- endpoint count;
- auth mode;
- server authority;
- mutation idempotency requirement;
- explicit reasons required for separate endpoints.

The surface policy records:

- source/app URL or bundled asset policy;
- redirect URI policy;
- storage/token policy;
- offline/cache policy;
- native bridge policy;
- distribution/build ownership.

## Boundary

This slice does not:

- create a new artifact type;
- initialize Flutter, Capacitor, or React Native projects;
- install dependencies;
- write app source or native files;
- create PWA manifest or service worker files;
- split backend endpoints automatically;
- claim browser PWA behavior and Flutter WebView behavior are identical.

## Validation

Focused validation:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Result:

```text
OK (39 tests, 320 assertions)
```

Focused validation is sufficient because the change is isolated to mobile wrapper target output-mode config metadata and its integration tests.

## Next

Add a target-specific Flutter WebView wrapper extension packet that consumes the React/PWA source and `app_surface_config` without generating Flutter project/source/native files.
