# Output mode config artifact first slice

## Status

`EF_M8_FIRST_SLICE`

## Purpose

Add a small config artifact so users and AI consumers can tell whether the current mobile/app output path is `mtool_no_code`, `external_no_code`, or `hybrid` before following generated artifacts.

This avoids making the growing artifact set ambiguous.

## What changed

Added `output-mode-config` to mobile wrapper target tooling.

The artifact emits only:

```text
output-mode-config.json
OUTPUT-MODE-CONFIG.md
```

CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=output-mode-config \
  --output-mode=external_no_code \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/output-mode-config
```

Supported modes:

- `mtool_no_code`
- `external_no_code`
- `hybrid`

The mobile wrapper bundle manifest now includes:

```text
output_mode_config
```

## Boundary

This artifact is selection/config metadata only.

It does not:

- create a React app;
- install dependencies;
- initialize Capacitor / Flutter / React Native;
- run `cap sync`;
- create native projects;
- sign or submit apps;
- enable offline sync;
- overwrite existing app files.

## Dry-run evidence

Command:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=output-mode-config \
  --output-mode=external_no_code \
  --target-dir=/private/tmp/mtool-output-mode-config-proof-20260715-efm8
```

Result:

```json
{
  "ok": true,
  "source": "sample28",
  "artifact": "output-mode-config",
  "output_mode": "external_no_code",
  "files": [
    "OUTPUT-MODE-CONFIG.md",
    "output-mode-config.json"
  ],
  "error": ""
}
```

Machine check:

```json
{
  "schema_version": "mobile-output-mode-config-v1",
  "selected_mode": "external_no_code",
  "selected_artifact_keys": [
    "external_optional_output",
    "ai_task_packet"
  ],
  "forbids_cap_sync": true,
  "has_ai_task_packet": true
}
```

## Verification target

Focused verification:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
node sample/tutorials/sample35-capacitor-artifact-import/scripts/validate-sample.mjs
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Initial focused PHPUnit result:

```text
OK (36 tests, 267 assertions)
```
