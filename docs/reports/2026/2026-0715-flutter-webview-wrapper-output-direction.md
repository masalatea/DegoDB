# Flutter WebView wrapper output direction

## Status

`EF_M12_DIRECTION_SELECTED`

## Purpose

Clarify the next Flutter-related target before implementation.

The goal is not to generate Flutter native UI from Mtool metadata.
The goal is to let users select another output shape for the same Mtool design:

```text
Mtool design
  -> React/Web output
  -> PWA-ready metadata
  -> Capacitor handoff
  -> Flutter WebView wrapper handoff
```

## Decision

Treat EF-M12 as Flutter WebView wrapper output.

The expected app shape is:

```text
Flutter native shell
  -> WebView
       -> React app
            -> PWA-ready web app
```

This means React remains the primary app surface.
Flutter is a native shell / wrapper target.
PWA readiness remains useful for the browser/web distribution path and for clarifying cache/storage/offline assumptions, but WebView behavior must be treated as a separate runtime mode with its own constraints.

## User-facing model

From the user's point of view, this should feel like choosing an output format and re-outputting the same design.

The UI should avoid exposing internal artifact categories too early.
It can present choices such as:

- Mtool Web/React;
- PWA-ready Web app;
- Capacitor wrapper handoff;
- Flutter WebView wrapper handoff.

Internally, these choices may emit generated code, metadata, readiness checklists, or external-tool input packets.
That distinction is Mtool/tooling responsibility, not something the app creator should have to infer manually.

## EF-M12 implementation boundary

EF-M12 should define `flutter_webview_wrapper_extension` or equivalent metadata.

It should cover:

- React/PWA source reference;
- `app_surface_config` with shared backend endpoint and one or more selected app surfaces;
- URL-hosted mode vs bundled static asset mode;
- WebView policy and navigation allowlist;
- SSO/OIDC callback and deep-link boundary;
- token/session/storage responsibility;
- optional native bridge API boundary;
- offline/cache behavior notes for browser PWA vs Flutter WebView;
- external owner responsibilities for Flutter project, dependency installation, native files, signing, build, device QA, and store submission.

It should not:

- generate Flutter native UI;
- initialize a Flutter project;
- install Flutter dependencies;
- write iOS/Android files;
- choose signing assets;
- build or submit apps;
- claim offline sync without an explicit sync contract.

## App surface config follow-up

The agreed configuration model separates backend endpoint from app surfaces.

Default:

```text
same backend/API/auth endpoint
  -> PWA surface
  -> Flutter WebView surface
```

The PWA and Flutter WebView surfaces may be enabled independently or together.
They can share the same API endpoint while keeping redirect URI, storage, navigation, native bridge, and offline/cache policies surface-specific.

Separate backend endpoints are allowed only with an explicit reason, such as staging/production separation, tenant separation, a native-only BFF, or a separate sync server.
