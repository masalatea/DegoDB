# 2026-0711 Sample18 Server Overlay Wiring Gap

Status: `DONE_ARCHITECTURE_GAP_FOUND`

## Finding

The server policy builder cannot be safely wired into static `runtime-preview` artifact generation.

Artifact generation calls the screen-definition builder without a principal. Authorization therefore fails with `principal.missing`. Enabling actions there would bypass authorization; keeping authorization intact means the static artifact can never produce an enabled candidate.

## Request-Time Paths

The public runtime execution endpoint has an authenticated principal and already builds an authorization-aware policy definition. However, applying the readiness overlay directly in that endpoint would feed `availability=enabled` into the real execution dispatcher. That crosses the presentation-only boundary of #721/#723.

The read-only public runtime data response is safe, but it currently returns data screens without action policy or availability output. There is no authenticated read-only response surface where the server can expose candidate action availability.

## Decision

Do not wire the overlay into either unsafe location:

- not into principal-less static artifact generation;
- not directly into the mutating execution endpoint.

The next slice must first define an authenticated, read-only action availability response. That response may combine:

- artifact-bound screen definition;
- current principal authorization policy;
- persisted readiness metadata;
- explicit overlay flag;
- Transaction Full capability gate;
- mutation-disabled diagnostics.

The browser may consume this response to update candidate presentation, while real execution remains governed by its separate route and feature flags.
