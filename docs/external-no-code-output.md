# External No-Code Output / external no-code output

English companion:
This document defines the optional external no-code/tool output packet. It is additive to `mtool_no_code`; it is not a migration away from Mtool's own no-code runtime.

この文書は、optional external no-code/tool output packet を定義します。これは `mtool_no_code` への追加であり、Mtool 独自 no-code runtime からの移行ではありません。

## Position / 位置づけ

`external-output` is the first concrete optional output for external FE/no-code/app-framework consumers.

Current first target:

```text
react_web_capacitor
```

The packet is intended for:

- app creators;
- Codex / Claude style code-builder workflows;
- external React/Web + Capacitor-style builders;
- review tools that need a machine-readable ownership and validation boundary.

## Generated files / 生成ファイル

The artifact emits only:

```text
react-web-capacitor-output/
  external-output.json
  EXTERNAL-OUTPUT.md
```

The companion AI task packet emits only:

```text
ai-task-packet/
  task.json
  TASK.md
  input/
    external-output.json
    mobile-app-handoff.json
```

It must not create:

- `package.json`;
- `capacitor.config.ts`;
- `ios/`;
- `android/`;
- dependency folders;
- signing files;
- store submission files.

## CLI / CLI

Sample28 proof:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=external-output \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/react-web-capacitor-output
```

AI task packet:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=ai-task-packet \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/ai-task-packet
```

From a handoff file:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --handoff-file=work/mobile-app-handoff.json \
  --artifact=external-output \
  --target-dir=work/mobile-wrapper-target/react-web-capacitor-output
```

From project/source-output lookup:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --project-key=PROJECT \
  --source-output-key=MOBILE-HANDOFF \
  --artifact=external-output \
  --target-dir=work/mobile-wrapper-target/react-web-capacitor-output
```

## JSON shape / JSON shape

Minimum top-level sections:

| Field | Meaning |
| --- | --- |
| `schema_version` | Current value: `mobile-external-optional-output-v1`. |
| `mode` | Current value: `external_no_code`. |
| `target` | Current value: `react_web_capacitor`. |
| `baseline` | States that `mtool_no_code` stays the supported baseline and no replacement is claimed. |
| `project_identity` | Project/app identity from the validated handoff packet. |
| `source_artifacts` | Source refs and hashes. |
| `screens` | Screen flow mapping from Mtool metadata. |
| `actions` | Action mapping and mutation/idempotency boundary. |
| `readiness` | Readiness source and server-authoritative boundary. |
| `server_authority` | Authorization, CSRF, idempotency, Transaction Full, audit, outbox, and approval/current/alias ownership. |
| `ownership_boundary` | `contract_owned_core` and `external_custom_extension_owned` lists. |
| `requires_user_confirmation` | Actions external tools must not perform without confirmation. |
| `forbidden_without_artifact` | Behaviors that must fail closed unless an explicit artifact exists. |
| `validation` | Mtool and consumer validation gates. |
| `non_goals` | Explicit non-claims. |

## Boundary / 境界

Mtool owns:

- contract shape;
- source artifact references;
- screen/action/readiness metadata;
- validation map;
- server authority statement;
- confirmation-required and forbidden-without-artifact lists;
- non-goals.

External owner/tool owns:

- React app shell;
- routing and component system;
- form binding implementation;
- API client and retry strategy;
- Capacitor/native project;
- dependencies;
- native build;
- signing and store submission.

## Relationship to bundle manifest / bundle manifest との関係

`external-output` is included in the mobile wrapper bundle manifest as `external_optional_output`.

This makes the optional external output discoverable without making it mandatory and without replacing `mtool_no_code`.

The companion AI task packet is included as `ai_task_packet`. It is a pending, confirmation-driven packet for Codex / Claude style workflows. It does not execute an AI, install dependencies, initialize Capacitor, write app files, run native builds, sign apps, or submit stores.

## AI task packet / AI task packet

The task packet is intended for the flow:

1. AI reads `task.json` first;
2. AI reads `input/external-output.json` and `input/mobile-app-handoff.json`;
3. AI explains the target and boundary;
4. AI asks the declared confirmation question;
5. only after a fresh affirmative answer in that task interaction may the AI write to a user-confirmed external app directory.

Before confirmation, allowed writes are empty.

Without explicit confirmation, the packet forbids:

- dependency installation;
- network calls;
- Capacitor initialization or `cap sync`;
- native project generation;
- overwriting existing external app files;
- token-storage choices;
- offline sync;
- native plugin selection;
- native build, signing, and store submission;
- Mtool metadata or database writes.

## Validation / 検証

Current focused gate:

```sh
bash mtool/scripts/run_sample_pack_phpunit_test.sh \
  --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml \
  --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh \
  --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

The packet should also keep passing:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
git diff --check
```

Sample consumer evidence:

```sh
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
```

`sample35` imports `external-output.sample.json` as a checked-in fixture and validates that `external_no_code` remains additive to `mtool_no_code`. It does not run native build or `cap sync`.
