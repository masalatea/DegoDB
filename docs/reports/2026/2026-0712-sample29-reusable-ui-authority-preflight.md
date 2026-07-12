# Sample29 Reusable UI Authority Preflight

Status: `DONE`

## Decision

Sample29 must not receive a copy of the Sample18-specific flag/helper. Generated UI authority becomes a reusable, default-off project/action policy with an explicit execution model and capability requirement.

## Architecture gap confirmed

The current implementation contains three Sample18-specific assumptions:

- UI enablement reads `MTOOL_SAMPLE18_GENERATED_UI_EXECUTION_ENABLED` and rejects every other project;
- execution binding always injects the `create_task_card` allowlist;
- server availability requires Sample18 route-readiness metadata, a guarded submit route, and `transaction_full_v1` for every candidate.

Sample29 uses a different valid model. `update_support_case` submits to the generic current/alias `execute.json` endpoint, durably enqueues managed-operation outbox work, and is later processed through generated server DBAccess. It has no Sample18 guarded submit route and does not represent a same-request composite transaction.

## Execution models

### `direct_guarded_route`

Used by the qualified Sample18 create slice.

Required availability inputs:

- authenticated operation policy;
- immutable current/alias artifact identity;
- explicit overlay/UI authority policy;
- guarded submit route and binding metadata;
- route/executor readiness candidate;
- `transaction_full_v1` capability;
- action-specific server mutation/executor gates.

### `managed_operation_outbox`

Used by Sample29 `update_support_case`.

Required availability inputs:

- authenticated operation policy;
- immutable current/alias artifact identity;
- explicit overlay/UI authority policy;
- generic selector execution endpoint;
- managed operation is active and its key/input contract is complete;
- `managed_outbox_v1` capability confirming durable enqueue, operator-visible failure state, and guarded retry/requeue behavior;
- generated server DBAccess binding remains a processing concern, not browser authority.

Transaction Full is not required merely to enqueue one outbox item. If a future outbox handler performs multiple required same-database writes, that handler must independently use Transaction Full.

## Authority configuration

Introduce a reusable policy boundary with both conditions required:

- a global generated-UI authority switch, default off;
- a normalized allowlist of `PROJECT_KEY:action_key` entries.

Recommended environment contract:

- `MTOOL_NO_CODE_GENERATED_UI_EXECUTION_ENABLED` — boolean, default false;
- `MTOOL_NO_CODE_GENERATED_UI_EXECUTION_ALLOWLIST` — comma-separated normalized entries such as `SAMPLE18:create_task_card,SAMPLE29:update_support_case`.

The server resolves a project-specific list and injects it only into authenticated current/alias execution bindings. Static artifact bindings receive neither execution authority nor allowlist.

Sample18's existing flag may remain as a temporary compatibility input during #751, but the normalized policy result must be the only value consumed by binding/render code. New projects must use the reusable contract.

## Availability response extension

Each action diagnostic should include:

- `execution_model`;
- `required_capability`;
- `capability_satisfied`;
- existing authorization, selection identity, availability, and failed checks.

Availability stays GET-only and zero-dispatch. It never authorizes by itself; browser submission still requires the injected UI policy allowlist and a matching enabled live diagnostic.

## Browser behavior

The browser may submit only when all of these match:

- server-injected global policy enabled;
- action is in the project-specific allowlist;
- live availability reports the same action enabled;
- project/artifact/selector identity matches execution binding;
- action's execution model matches the binding route being used.

The Sample29 test must stop rewriting action availability/enabled state in page memory.

## Ordered implementation

1. #751: reusable policy parser/resolver and execution-binding injection; preserve Sample18 with fast compatibility tests.
2. #752: execution-model/capability-aware availability policy for direct guarded and managed outbox actions.
3. #753: Sample29 current/alias browser integration using live authority and real pending-outbox POST, plus blocked-path zero-POST matrix.
4. #754: qualification closure and G-L2 decision.

## Required fail-closed cases

- global policy off;
- missing/malformed allowlist entry;
- project or action mismatch;
- unauthenticated/denied principal;
- artifact preview;
- stale selector/artifact identity;
- required capability missing;
- execution-model/binding mismatch;
- endpoint/outbox enqueue failure.

## Estimate

- #751 policy/binding foundation: 1–2 days;
- #752 availability model: 1–2 days;
- #753 browser integration: 1–2 days;
- #754 closure: 0.25–0.5 day.

These are bounded slices, not a claim that the entire lane is a 1–2 day task.
