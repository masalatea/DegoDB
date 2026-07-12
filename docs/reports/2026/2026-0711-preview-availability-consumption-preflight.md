# Preview Availability Consumption Preflight

## Decision

The authenticated action-availability response may be consumed by generated previews only as presentation diagnostics in the first slice. It is not execution authority and must not enable an action button, a guarded-submit button, or the runtime execute button.

## Current boundary

- Generated preview HTML contains an optional `no-code-runtime-execution-binding` JSON block.
- Current and alias preview responses inject `execution_url`, CSRF, immutable artifact identity, and optional `runtime_data_url`.
- Runtime execute readiness currently depends on execution binding presence and local draft blockers.
- Action buttons are rendered from the stored artifact definition; the static artifact preview has no authenticated principal policy.
- The new availability endpoints are authenticated, GET-only, selector-bound, default-off, Transaction Full-gated, and zero-dispatch.

Therefore, directly copying `availability=enabled` into the existing action or execution readiness state would collapse presentation policy into execution authority. That is explicitly excluded.

## First safe UI slice

1. Add an optional `action_availability_url` to the injected preview binding for artifact, current, and alias selectors.
2. Fetch it with `credentials: same-origin` and `Accept: application/json`.
3. Accept only `server-action-availability-v1`, `ok=true`, `mutation_enabled=false`, and a response whose normalized project and resolved artifact identity match the injected binding.
4. Render per-action availability and failed-check diagnostics into dedicated status nodes or diagnostic attributes.
5. Keep every existing `disabled`, `data-action-enabled`, guarded-submit, and runtime-execute state unchanged.
6. On 401/403, redirects/non-JSON, network failure, stale selector, contract mismatch, or artifact mismatch, render an unavailable diagnostic and otherwise fail closed.

## Selector and staleness rules

- Artifact preview uses the immutable artifact availability URL and must match that artifact exactly.
- Current and alias previews use their selector URL, but the response must also resolve to the artifact identity embedded in the served preview.
- If current or alias moves between serving the preview and fetching availability, the artifact mismatch is treated as stale preview state. The browser does not apply the response and may tell the user to refresh.

## Explicit exclusions

- No button enablement.
- No dispatcher call, POST, outbox write, audit write, or DB mutation.
- No change to execution endpoint authorization or CSRF validation.
- No use of availability response as proof that execution remains authorized at click time.
- No automatic retry that could hide authentication or stale-artifact failures.

## Required coverage

- binding URL generation for artifact/current/alias;
- valid response diagnostics rendering while controls remain disabled;
- flag-off and authorization-denied diagnostics;
- missing principal/non-JSON failure;
- project/artifact/contract mismatch rejection;
- current/alias stale selector rejection;
- assertion that no POST or execution fetch occurs during availability loading.

## First slice implementation

- Artifact, current, and alias preview bindings now carry their matching `action_availability_url`.
- Artifact preview receives only presentation identity and the availability URL; it does not gain CSRF or an execution URL.
- Preview JavaScript performs one same-origin GET, validates contract version, `mutation_enabled=false`, project identity, and immutable artifact identity, then adds dedicated per-action diagnostic nodes.
- Invalid, unauthenticated/non-JSON, network, and stale responses render unavailable diagnostics.
- The diagnostics code does not write `disabled`, `data-action-enabled`, guarded-submit state, or runtime-execute state.
