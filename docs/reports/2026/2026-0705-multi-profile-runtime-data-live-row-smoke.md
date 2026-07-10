# Multi-profile Runtime Data Live Row Smoke

Status: DONE
Date: 2026-07-05

## Summary

This slice promotes the #208 successful `runtime-data.json` live-row behavior from sample28 to the other product-facing no-code sample profiles.

The important boundary remains unchanged: current/alias `runtime-data.json` is an authenticated, read-only, `GET`-only, `no-store` data route backed by generated DBAccess/read-model materialization. It does not weaken into static `runtime-preview.json`, and it does not process outbox work inline.

## Verified Profiles

| Profile | Route selection | Status | Contract | Screen count | First row key |
| --- | --- | --- | --- | --- | --- |
| SAMPLE28 | current / alias | 200 | `no-code-runtime-data-v0` | 3 | `1001` |
| SAMPLE29 | current / alias | 200 | `no-code-runtime-data-v0` | 3 | `2001` |
| SAMPLE31 | current / alias | 200 | `no-code-runtime-data-v0` | 3 | `3101` |

## Behavior Confirmed

- The same generated DBAccess web-request binding works across sample28, sample29, and sample31.
- Current and alias selections return the same selected artifact data shape for each profile.
- Existing current/alias submit enqueue checks continue to return accepted sync outbox items.
- Existing generated server DBAccess outbox processing smokes continue to apply the queued work to seeded rows.
- Static artifact-key preview remains immutable artifact delivery; fresh business data is exposed through the separate current/alias `runtime-data.json` contract.

## Verification

- `make sample28-no-code-public-runtime-browser-smoke` from #208
- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`

## Result

The first successful fresh runtime data endpoint milestone is now multi-profile: sample28, sample29, and sample31 all prove current/alias live row reads plus existing submit/outbox processing behavior.

Push was not performed.
