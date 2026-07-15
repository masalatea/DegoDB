# Flutter WebView wrapper docs/schema hardening

## Status

`EF_M14_DONE`

## Purpose

Promote the new `flutter_webview_wrapper_extension` fields into durable documentation and user-facing guidance.

## Updates

Updated durable docs to state that:

- `platform-input-packets` emits `flutter-input-packet.json` and `react-native-input-packet.json`;
- `flutter-input-packet.json` includes `flutter_webview_wrapper_extension`;
- Flutter WebView wrapper output wraps the React/PWA-ready app in a Flutter native shell;
- PWA and Flutter WebView can share the backend endpoint by default;
- redirect URI, storage/session, navigation, native bridge, and offline/cache behavior remain surface-specific;
- Mtool does not generate Flutter projects, Dart source, native files, signing, builds, or store submission artifacts.

Updated CLI help wording so `platform-input-packets` is described as Flutter WebView wrapper + React Native input packets, not generic Flutter native UI generation.

## Boundary

This is docs/schema hardening only.
It does not add a new artifact type and does not generate app or native project files.

## Validation

Validation target:

```sh
php -l mtool/scripts/create_mobile_wrapper_target.php
git diff --check
```

No full runtime test is required for this docs/help-only hardening because EF-M13 already validated the packet structure with focused `MobileWrapperTargetTest`.
