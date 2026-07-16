# Sample 45: Map Info Popup Site

This sample is a small local information site with map-like markers and popups.

It is intentionally lighter than a full Google Maps integration. The first slice proves the data and UI shape: structured locations, markers, click popups, list navigation, site search, hit icons, and category filtering.

## Product idea

- Show a local map-like panel.
- Render markers from structured location data.
- Click a marker to show a popup.
- Click a list item to show the same popup.
- Search the site and show hit icons on matched markers/list rows.
- Filter markers by category.
- Include title, category, summary, details, and URL in each popup.
- Run locally without any Google Maps API key.

## First-slice implementation

The first slice uses only Node.js and browser standard APIs:

- no npm dependencies;
- no database;
- no Google Maps API key;
- no remote map tiles in validation;
- no production deployment config.

The local map is a static CSS/grid panel with normalized marker coordinates. That keeps the sample deterministic and key-free while preserving the important site behavior.

## Mtool handoff structure

The sample includes a Mtool-shaped handoff packet:

- `reference/map-info-site-input.sample.json`

The packet defines:

- map provider boundary;
- marker schema;
- popup fields;
- site search / hit icon metadata;
- structured location data;
- key-free local runtime boundary;
- optional future Google Maps provider boundary.

## Validate

```bash
node sample/tutorials/sample45-map-info-popup-site/scripts/validate-sample.mjs
```

## Run locally

```bash
node sample/tutorials/sample45-map-info-popup-site/src/server.mjs
```

Then open:

```text
http://127.0.0.1:8790/
```

Google Maps can be added later as an optional provider, but this sample must continue to run without a Google Maps API key.
