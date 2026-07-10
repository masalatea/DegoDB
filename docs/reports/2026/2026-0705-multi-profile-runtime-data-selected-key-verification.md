# Multi-Profile Runtime Data Selected-Key Verification

Status: DONE
Date: 2026-07-05

## Summary

This slice verifies the #221 selected-key query behavior across the other product-facing no-code sample profiles.

No code changed in this slice. The shared public runtime smoke already contains the selected-key endpoint assertions; this run proves sample29 and sample31 inherit those checks through their standard current/alias public runtime paths.

## Verified Behavior

- Normal current and alias `runtime-data.json` requests keep first-row behavior.
- `current-selected` requests include `?selected_key=...`.
- Successful selected-key responses echo `query.selected_key`.
- Detail/form selection metadata reports the requested selected key.
- Missing selected keys fail closed with JSON 422.
- Existing current/alias submit enqueue checks still pass.
- Generated server DBAccess outbox processing proof still renders the processed value through runtime-data list/detail/form screens.

## Verification

- `make sample29-no-code-public-runtime-browser-smoke`
  - normal current/alias selected key: `2001`
  - `current-selected` query echo: `2001`
  - missing selected key: 422 fail-closed
  - post-processing runtime-data proof selected key: `2001`
- `make sample31-no-code-public-runtime-browser-smoke`
  - normal current/alias selected key: `3101`
  - `current-selected` query echo: `3101`
  - missing selected key: 422 fail-closed
  - post-processing runtime-data proof selected key: `3101`

Full `make test` was already run in #221 after the code change. This slice is verification and documentation only.

## Remaining Candidates

- Browser UI affordance for selecting list rows and requesting `runtime-data.json?selected_key=...`.
- Query-driven pagination and page-size controls.
- Filter parameters derived from generated screen/operation metadata.
- Form default behavior for create/update screens.

Push was not performed.
