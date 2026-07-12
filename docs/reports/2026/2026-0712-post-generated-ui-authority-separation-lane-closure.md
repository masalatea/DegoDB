# Post Generated UI Authority Separation Lane Closure

## Decision

The UI authority separation lane is complete enough to promote an authenticated current/alias integration smoke. The candidate remains explicit and default-off. The next browser slice will use live server availability but a stubbed guarded POST; real mutation remains covered separately by the isolated HTTP transaction smoke.

## Why this is the next safe step

- publish candidate creation already returns immutable artifact, current, and alias URLs;
- current and alias previews inject execution and availability bindings;
- the availability endpoint requires authentication and applies current principal policy;
- UI authority now requires a separate Sample18-only flag, create allowlist, and validated availability;
- the focused browser matrix proves local gating but uses a stubbed availability response;
- the isolated HTTP smoke already proves the real guarded route commits and rolls back MariaDB correctly.

Combining live availability with real mutation in the same first browser integration would make failures harder to classify and duplicate the transaction proof. The first integration should isolate the missing seam: authenticated selector preview -> live availability -> browser authority -> one stubbed guarded POST.

## Required integration matrix

### Current selector

- authenticate before loading preview;
- enable server availability overlay, Transaction Full gate, and UI execution flag explicitly;
- load approved current preview;
- require matching live availability for `create_task_card`;
- click create and observe exactly one stubbed POST;
- require excluded actions to remain non-POST.

### Alias selector

- repeat against the approved alias URL;
- require alias resolves to the same immutable artifact identity;
- require exactly one stubbed create POST and no excluded action POST.

### Fail-closed controls

- UI flag off with backend flags on: zero POST;
- unauthenticated availability: zero POST;
- stale artifact mismatch remains covered by the focused matrix;
- artifact-key static preview receives no UI execution authority.

## Deferred

- default enablement;
- update/complete/reopen/delete;
- broad sample rollout;
- browser-driven real mutation, because route-level authenticated real commit/rollback is already independently covered.

