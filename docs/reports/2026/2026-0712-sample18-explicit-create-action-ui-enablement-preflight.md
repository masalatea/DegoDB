# Sample18 Explicit Create Action UI Enablement Preflight

## Architecture finding

The current route-compatible generated actions are already clickable even when their declared availability is `disabled`.

`app_no_code_runtime_render_actions()` treats a guarded-submit binding as enabled when the static metadata says:

- a submit route exists;
- network submit is enabled;
- runtime click binding is present;
- mutation gate metadata is false;
- click state is `blocked_route_enabled`;
- trigger is `guarded_click`.

The browser `isGuardedSubmitButton()` then checks only submit-trigger, network-submit, click-state, and URL. It does not require:

- the authenticated server availability response;
- a separate UI execution flag;
- an immutable artifact match at click time in the browser;
- `data-action-availability=enabled`.

This was safe while the backend route remained mutation-disabled: clicking displayed a blocked response. Once mutation and executor flags are enabled, the same existing button can reach real mutation. Therefore the next task is not merely enabling a new button. It is separating UI execution authority from backend execution capability.

## Required correction

Introduce a separate default-off server-injected UI execution gate. The gate must not be stored as enabled in generated artifact HTML.

The first candidate may be `create_task_card` only, and only when all conditions pass:

1. authenticated current or alias preview injects execution binding;
2. `MTOOL_NO_CODE_GENERATED_UI_EXECUTION_ENABLED=1` (provisional name) is explicit;
3. server availability response is valid, project/artifact matched, `mutation_enabled=false`, and reports `create_task_card=enabled`;
4. Transaction Full gate is `transaction_full_v1`;
5. action is allowlisted as `create_task_card`;
6. DOM submit metadata is guarded and route-compatible;
7. click still posts to the existing route, which revalidates authentication, CSRF, input, idempotency, executor config, and transaction state.

## Default and failure behavior

- With no UI gate, guarded action buttons may still prepare local intent, but must not issue network POST.
- Flag off, availability denied/unavailable/stale, artifact mismatch, missing auth, and non-allowlisted operations must remain network-disabled.
- `update_task_card`, `complete_task_card`, `reopen_task_card`, and `delete_task_card` remain excluded from the first candidate.
- A valid availability GET alone never enables POST.
- Backend mutation/executor flags alone never enable browser POST.

## Compatibility consideration

Existing blocked-route browser tests intentionally use guarded clicks to observe blocked responses. Those tests must move to an explicit test UI gate rather than relying on static generated metadata. This makes the test authority visible and prevents production artifacts from implicitly acquiring execution behavior when backend flags change.

## Required coverage before implementation closes

- default generated preview: no guarded POST;
- backend execution flags on, UI flag off: no guarded POST;
- UI flag on, server availability flag off: no guarded POST;
- stale/denied/unavailable availability: no guarded POST;
- all gates on for `create_task_card`: exactly one POST;
- other operations: no POST;
- click-time auth/CSRF rejection remains visible;
- real commit and rollback smoke remains green;
- static artifact preview never receives execution authority.

## First slice implementation

- Current/alias execution bindings now carry a Sample18-only default-off UI execution boolean and a `create_task_card`-only allowlist.
- Static artifact bindings still carry no execution URL, CSRF, UI execution boolean, or allowlist.
- Browser guarded-submit classification now additionally requires the UI boolean, allowlist membership, and a validated server availability result for the same action.
- Before availability completes, or after denied/unavailable/stale results, clicks remain local intent preparation and cannot issue guarded POST.
- Backend mutation and executor flags remain independent and cannot grant browser POST authority by themselves.

## Browser matrix completed

Real generated Sample18 preview HTML now proves zero POST for UI-flag-off, authorization-denied, stale, unavailable, and excluded-operation scenarios. With UI flag, create allowlist, matching identity, and enabled server availability together, `create_task_card` issues exactly one guarded POST. An enabled `complete_task_card` response still cannot POST because it is outside the injected allowlist.
