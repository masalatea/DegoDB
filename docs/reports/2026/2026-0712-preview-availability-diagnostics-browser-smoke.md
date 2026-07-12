# Preview Availability Diagnostics Browser Smoke

## Result

The focused Sample18 Playwright smoke proves that preview availability consumption remains diagnostic-only.

- enabled response renders `Server availability: enabled`;
- authorization denial exposes `authorization_not_allowed`;
- artifact mismatch renders a stale-preview refresh diagnostic;
- HTTP/non-JSON authentication failure renders unavailable;
- all requests to the availability endpoint use GET;
- no POST is issued in any scenario;
- `disabled`, `data-action-enabled`, `data-action-state`, and guarded-submit trigger values remain unchanged.

The smoke uses the real generated Sample18 runtime preview HTML, serves it and the stubbed response through one virtual same-origin host, and can be run with:

```sh
make sample18-no-code-preview-availability-diagnostics-smoke
```

This closes browser evidence for presentation diagnostics only. It does not authorize generated button enablement or real execution.

