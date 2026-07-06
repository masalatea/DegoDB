# Runtime Data Web DB Binding Replan

Status: DONE
Date: 2026-07-05

## Context

#206 added authenticated current/alias `runtime-data.json` routes and a versioned `no-code-runtime-data-v0` response contract. The sample28 public smoke now proves the route contract and fail-closed JSON behavior. It does not yet prove a successful live row read in the web request path because the Apache runtime request does not have a deterministic runtime database binding for the generated DBAccess class.

## Decision

The next slice should make successful live reads work by binding the runtime database into the web request path. It should not turn `runtime-data.json` into a static `runtime-preview.json` fallback just to get a 200.

## Boundary

- Keep artifact-key preview static and cacheable.
- Keep current/alias data routes authenticated, read-only, `GET` only, and `no-store`.
- Keep fail-closed JSON for missing current/alias candidates, invalid screen definitions, missing generated read methods, and unavailable runtime DB connections.
- Do not process outbox, retry mutations, regenerate artifacts, publish candidates, or switch current/alias selection from this endpoint.
- Do not hide live-read failures by returning generated preview fixture rows as if they were fresh business data.

## Candidate Implementation Path

1. Identify the existing sample/tutorial mechanism that creates business tables and rows for web-admin generated DBAccess reads.
2. Bind that runtime DB location into Apache/web request execution in the sample28 public smoke path.
3. Keep the existing fail-closed smoke assertion available for unbound environments.
4. Add a success assertion for sample28 current and alias `runtime-data.json`:
   - status 200
   - `ok: true`
   - `contract_version: no-code-runtime-data-v0`
   - project/artifact/revision selection present
   - at least three screens
   - list rows include seeded row `1001`
5. After sample28 succeeds, repeat the same success path for sample29 and sample31.

## Recommended Next Slice

Implement the sample28 web runtime DB binding and success smoke first. Then promote the same contract to sample29/sample31 after the binding path is stable.
