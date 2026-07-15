# External no-code AI task packet first slice

## Status

`EF_M6_FIRST_SLICE`

## Purpose

Add a Codex / Claude style AI task packet for optional external no-code output.

This is the selected next scope after EF-M5. The goal is not to make Mtool generate a production app directly. The goal is to give an AI/code-builder a bounded, readable packet so it can explain the external app boundary and ask the user for confirmation before creating or modifying an external app project.

## What changed

Added an `ai-task-packet` artifact to the mobile wrapper target tooling.

The artifact emits only:

```text
task.json
TASK.md
input/external-output.json
input/mobile-app-handoff.json
```

It can be emitted through:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=ai-task-packet \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet
```

The mobile wrapper bundle manifest now includes:

```text
ai_task_packet
```

## Safety boundary

The packet state is:

```text
pending_user_confirmation
```

Before confirmation:

- allowed writes are empty;
- AI may read only the declared task/input files;
- AI must explain that `mtool_no_code` remains the baseline and `external_no_code` is optional/additive;
- AI must ask the declared confirmation question.

Without explicit confirmation, the task forbids:

- dependency installation;
- network calls;
- Capacitor initialization or `cap sync`;
- native project generation;
- overwriting existing external app files;
- token-storage choices;
- offline sync;
- native plugin selection;
- native build;
- signing;
- store submission;
- Mtool metadata writes;
- database writes.

## Not claimed

This slice does not:

- execute Codex, Claude, Ollama, or any model;
- install npm dependencies;
- create a React project;
- initialize Capacitor;
- create `ios/` or `android/`;
- build native apps;
- sign or submit apps;
- validate a generated external app.

Those remain downstream, user-confirmed external-owner steps.

## Verification

Passed:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Focused PHPUnit result:

```text
OK (31 tests, 233 assertions)
```

## Next candidate

After this first slice is committed, the next likely candidates are:

- generate a sample task packet fixture and dry-run an AI-readable review path;
- add output-mode config selection;
- add PWA readiness metadata;
- add another external consumer packet.
