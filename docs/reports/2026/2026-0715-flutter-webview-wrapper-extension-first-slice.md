# Flutter WebView wrapper extension first slice

## Status

`EF_M13_DONE`

## Purpose

Add the target-specific Flutter WebView wrapper extension packet that consumes the existing React/PWA-ready app handoff without generating Flutter native UI, Flutter project files, Dart source, dependencies, native files, signing, builds, or store submissions.

## Implementation

`flutter-input-packet.json` now includes:

- `flutter_webview_wrapper_extension.extension_version`;
- wrapper model metadata;
- React/PWA source mode metadata;
- shared backend endpoint policy;
- WebView policy;
- auth/deep-link policy;
- storage/session policy;
- native bridge policy;
- offline/cache policy;
- external-owner responsibilities;
- forbidden actions without explicit confirmation.

The extension is metadata-only.

## Boundary

Mtool owns:

- structured handoff metadata;
- source artifact references;
- shared backend/server-authority boundary;
- WebView/auth/storage/offline/native-bridge policy prompts;
- forbidden action list.

External Flutter owner owns:

- Flutter project initialization;
- WebView package choice;
- dependency installation;
- iOS/Android project files;
- native permission configuration;
- app signing;
- native build;
- device QA;
- store submission.

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
OK (39 tests, 331 assertions)
```

Focused validation is sufficient because the implementation is isolated to later-platform packet metadata and the existing integration test.

## Next

Promote the new `flutter_webview_wrapper_extension` fields into durable docs/user-facing guidance and clarify how it relates to PWA readiness and `app_surface_config`.
