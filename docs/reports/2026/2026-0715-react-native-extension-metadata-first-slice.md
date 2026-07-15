# React Native extension metadata first slice

## Status

`EF_M11_DONE`

## Purpose

Add React Native-specific second-pass metadata to the existing later-platform input packet without generating React Native source or native project files.

## Scope

This slice extends `react-native-input-packet.json` with `react_native_extension`.

The extension records:

- navigation expectations and route inputs;
- state management classes and server-state authority;
- form binding, validation, and server error mapping expectations;
- API client, retry, and idempotency expectations;
- OIDC/auth and secure-storage policy boundaries;
- native module permission policy and Expo vs bare ownership boundary;
- environment/build ownership;
- typecheck, unit/component, and device/simulator QA expectations.

## Boundary

Mtool still owns only the structured input packet and validation surface.

The external React Native owner remains responsible for:

- React Native or Expo project initialization;
- dependency installation;
- navigation/state/API/auth package choices;
- native module installation;
- `ios/` and `android/` project files;
- signing, build, device QA, and store submission.

## Implementation notes

- Added `react_native_extension` only for the React Native later-platform packet.
- Preserved `not_generated_by_mtool`.
- Kept the extension metadata-only and fail-closed around native/project writes.
- Updated durable docs so React Native is described as a packet extension, not an app generator.

## Validation

Focused validation target:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Focused validation is sufficient for this slice because the change is isolated to mobile wrapper target packet metadata and its integration test coverage.

## Next

Use the same bounded approach for the next Flutter target, but aim it at Flutter WebView wrapper output rather than Flutter native UI generation.
The intended user-facing model is that the same Mtool design can be re-output as React/Web, PWA-ready metadata, Capacitor handoff, or Flutter WebView wrapper handoff.
