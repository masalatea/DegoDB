# Runtime Data Browser History Replay Plan

Date: 2026-07-07

Status: `DONE`

## Summary

#290 replans after the runtime-data URL multi-filter replay closure and chooses a boundary plan before changing browser history behavior. #291 records the first-slice plan.

Current URL persistence uses `history.replaceState`. That is intentionally low-risk: it makes the current runtime-data query shareable and replayable on initial load, but it does not create back/forward history entries. Back/forward replay should be added as a separate behavior because it changes browser navigation semantics.

## Recommended First Slice

- Keep initial URL replay as a one-time read-only refresh on page load.
- Keep query Clear as `replaceState` so clearing controls does not create a confusing extra browser-history stop.
- Use `pushState` only after explicit user runtime-data query operations that successfully fetch data:
  - row selection
  - pagination / page-size / direct page
  - search
  - filter / two-filter query
  - sort
  - combined query operations
- Add a `popstate` listener that:
  - parses the current browser URL through the existing runtime-data URL query parser,
  - reuses the existing read-only `runtime-data.json` refresh path,
  - restores controls from returned metadata,
  - does not execute mutation behavior,
  - does not create a new history entry while replaying a history entry.

## Guardrails

- Do not change endpoint contracts.
- Do not make immutable artifact-key previews live.
- Do not use `pushState` for initial page load replay.
- Do not use `pushState` while handling `popstate`.
- Do not run submit/outbox mutation behavior from browser-history navigation.

## Verification Target

- Browser smoke should prove:
  - a user query operation creates a browser-history entry,
  - going back replays the previous runtime-data query,
  - going forward replays the later runtime-data query,
  - no extra history entry is created during replay,
  - retained controls match the replayed query.

## Boundary

- In scope: plan and guardrails for a future browser back/forward replay first slice.
- Out of scope: code changes, endpoint contract changes, typed operators, multi-column sort, broader read-model shape, mutation behavior, and push.
