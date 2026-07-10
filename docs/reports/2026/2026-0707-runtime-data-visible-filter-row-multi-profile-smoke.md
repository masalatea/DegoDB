# Runtime Data Visible Filter Row Multi-Profile Smoke

Date: 2026-07-07

Status: `DONE`

## Summary

#308 chooses multi-profile visible filter-row smoke promotion after the sample28 first implementation slice. #309 verifies the third visible generated filter row across the other product-facing no-code runtime profiles without additional code changes.

## Verified Profiles

- sample29 support-case runtime preview
- sample31 inventory-request runtime preview

Both profiles retained three filter rows through current/alias runtime-data initial URL replay and browser history replay.

## Verification

- `make sample29-no-code-public-runtime-browser-smoke`
- `make sample31-no-code-public-runtime-browser-smoke`

Observed retained third filters:

- sample29: `customer_tier=standard`
- sample31: `item_sku=SKU-CABLE-99`

## Boundary

- In scope: promotion of the third visible generated filter row across sample29 and sample31 browser smokes.
- Out of scope: code changes, dynamic add/remove filter rows, exposing all 8 endpoint filters, additional operator families, multi-column sort, broader read-model shape, mutation behavior, and push.
