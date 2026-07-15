# External no-code AI task packet dry-run

## Status

`EF_M7_DRY_RUN_DONE`

## Purpose

Verify the `ai-task-packet` artifact as an actual CLI output before adding another external no-code feature.

This dry-run checks that the packet can be generated from the supported sample source and that the generated task is a pending, confirmation-driven AI entry point rather than an app generator or execution path.

## Command

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=ai-task-packet \
  --target-dir=/private/tmp/mtool-ai-task-packet-proof-20260715-efm7
```

## Result

```json
{
  "ok": true,
  "source": "sample28",
  "sample": "sample28",
  "artifact": "ai-task-packet",
  "target_dir": "/private/tmp/mtool-ai-task-packet-proof-20260715-efm7",
  "files": [
    "TASK.md",
    "input/external-output.json",
    "input/mobile-app-handoff.json",
    "task.json"
  ],
  "error": ""
}
```

Generated files:

```text
/private/tmp/mtool-ai-task-packet-proof-20260715-efm7/TASK.md
/private/tmp/mtool-ai-task-packet-proof-20260715-efm7/input/external-output.json
/private/tmp/mtool-ai-task-packet-proof-20260715-efm7/input/mobile-app-handoff.json
/private/tmp/mtool-ai-task-packet-proof-20260715-efm7/task.json
```

## Machine check

The generated `task.json` had:

```json
{
  "task_version": "mobile-external-ai-task-packet-v1",
  "state": "pending_user_confirmation",
  "target": "react_web_capacitor",
  "allowed_writes_before_confirmation": [],
  "forbids_cap_sync": true,
  "forbids_database_write": true,
  "confirmation_required": true
}
```

## TASK.md boundary check

The generated `TASK.md` instructs the AI to:

- read `task.json` first;
- read `input/external-output.json` and `input/mobile-app-handoff.json`;
- explain that `mtool_no_code` remains the supported baseline;
- explain that `external_no_code` is optional and additive;
- keep Mtool/server authoritative for auth, CSRF, idempotency, Transaction Full, audit, and outbox policy;
- ask the declared confirmation question before any write.

It forbids, without explicit user confirmation:

- dependency installation;
- network;
- Capacitor init;
- `cap_sync`;
- native project generation;
- existing external app overwrite;
- token-storage choice;
- offline sync;
- native plugin selection;
- native build;
- signing;
- store submission;
- Mtool metadata write;
- database write.

## Conclusion

EF-M7 dry-run is complete.

The first AI task packet is usable as a confirmation-driven entry point for Codex / Claude style external app work. It remains an input packet and does not broaden Mtool ownership into production React/Capacitor app generation.
