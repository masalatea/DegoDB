# 2026-0716 Map Info Popup Site First Slice

## Summary

Added `sample45-map-info-popup-site` as a local marker popup information site sample.

The sample keeps the scope intentionally small: click a marker, show popup information, click a list item, search the site, show hit icons, and filter by category.

## Product shape

- Local map-like panel.
- Structured location data.
- Marker rendering.
- Marker click popup.
- List click popup.
- Site search with hit icons.
- Category filter.
- Popup fields:
  - title;
  - category;
  - summary;
  - details;
  - URL.

## Boundary

This is not a production Google Maps integration.

It does not include:

- Google Maps JavaScript API;
- Google Maps API key;
- remote map tiles in validation;
- Places API;
- geocoding;
- routing;
- production hosting.

Google Maps can be added later as an optional provider, but the sample must remain usable without an API key.

## Mtool artifact

Added:

- `reference/map-info-site-input.sample.json`

The packet defines:

- local key-free map provider boundary;
- optional Google Maps provider boundary;
- marker schema;
- popup field schema;
- site search and hit icon schema;
- structured location data;
- validation checks;
- forbidden actions around storing API keys or requiring Google Maps for the local sample.

## Validation

```bash
node sample/tutorials/sample45-map-info-popup-site/scripts/validate-sample.mjs
```

The validator checks:

- required files;
- no npm dependency;
- no API key required;
- Google Maps key not stored in packet;
- structured location data;
- popup fields present;
- local runtime serves JSON;
- marker click popup hook;
- list click popup hook;
- site search hook;
- hit icon and hit count hooks;
- category filter hook;
- no Google Maps API dependency in client JS.
