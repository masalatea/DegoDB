# Generated UI Execution Authority Browser Matrix

## Result

The focused Playwright matrix now proves that generated guarded POST authority requires every new UI gate.

- UI flag absent with otherwise enabled availability: zero POST;
- authorization-denied availability: zero POST;
- stale artifact identity: zero POST;
- unavailable/non-JSON authentication response: zero POST;
- UI flag on, create allowlisted, matching enabled availability: exactly one guarded POST;
- `complete_task_card` reported enabled but absent from the allowlist: no additional POST.

The matrix uses real generated Sample18 preview HTML. It also retains the prior assertion that availability diagnostics alone do not mutate the control's disabled/enabled/state metadata. The single accepted POST is stubbed; real route commit and rollback remain covered by the isolated HTTP transaction smoke.

Run with:

```sh
make sample18-no-code-preview-availability-diagnostics-smoke
```

