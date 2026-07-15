# App surface config plan

## Status

`EF_M12_APP_SURFACE_CONFIG_PLANNED`

## Purpose

Plan the configuration model needed for combined outputs such as PWA + Flutter WebView wrapper.

The key distinction is:

```text
backend_endpoint = shared API/auth/server authority by default
app_surfaces = one or more app entry/runtime surfaces
```

## Decision

Add an `app_surface_config` concept to the EF-M12 direction.

This should allow users to re-output the same Mtool design as:

- PWA-ready web app only;
- Flutter WebView wrapper handoff only;
- both PWA-ready web app and Flutter WebView wrapper handoff.

From the user's perspective, this is output selection.
Internally, Mtool must keep endpoint configuration and app-surface configuration separate.

## Planned shape

Illustrative shape:

```json
{
  "backend_endpoint": {
    "api_base_url": "https://api.example.com",
    "auth_issuer": "https://sso.example.com",
    "server_authority": true,
    "idempotency_required": true
  },
  "app_surfaces": {
    "pwa": {
      "enabled": true,
      "app_url": "https://app.example.com",
      "redirect_uri": "https://app.example.com/auth/callback",
      "storage_policy": "browser_storage_explicit",
      "offline_cache_policy": "explicit"
    },
    "flutter_webview": {
      "enabled": true,
      "source": "same_app_url",
      "app_url": "https://app.example.com",
      "redirect_uri": "myapp://auth/callback",
      "storage_policy": "webview_or_native_bridge_explicit",
      "native_bridge": "disabled_by_default"
    }
  }
}
```

## Rules

- The backend endpoint is shared by default.
- Multiple app surfaces may be enabled at the same time.
- Surface-specific redirect URI, storage, navigation, native bridge, and offline/cache policies must be explicit.
- Separate backend endpoints are not the default.
- Separate backend endpoints require an explicit reason such as staging/production separation, tenant separation, native-only BFF, or a separate sync server.
- Enabling both PWA and Flutter WebView does not imply that browser PWA runtime behavior and WebView runtime behavior are identical.

## EF-M12 implementation impact

The next implementation slice should add one bounded artifact/packet path that records:

- selected surfaces;
- shared backend endpoint references;
- surface-specific app URL / bundled asset mode;
- redirect URI policy;
- storage/token policy;
- offline/cache policy;
- native bridge policy;
- forbidden implicit actions.

It should not:

- initialize Flutter projects;
- install dependencies;
- write app source or native files;
- create service workers or manifests automatically;
- change backend endpoints unless explicitly configured;
- claim offline sync without a sync contract.
