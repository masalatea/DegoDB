# React/Web + Capacitor optional output packet first slice

## Status

`FIRST_SLICE_DONE`

## Purpose

Implement EF-M2: the first concrete optional external no-code/tool output packet for React/Web + Capacitor-style consumers.

This does not replace `mtool_no_code`. It adds an optional `external_no_code` packet that external tools, app builders, or AI/code-builder agents can consume.

## What changed

Added `external-output` support to the existing mobile wrapper target tooling.

New behavior:

- `app_mobile_wrapper_target_build_external_optional_output_packet()`;
- `app_mobile_wrapper_target_emit_external_optional_output_packet()`;
- `app_mobile_wrapper_target_emit_sample28_external_optional_output_packet()`;
- CLI support for `--artifact=external-output`;
- focused tests in `MobileWrapperTargetTest`.

Generated files:

```text
react-web-capacitor-output/
  external-output.json
  EXTERNAL-OUTPUT.md
```

The packet includes:

- `schema_version`;
- `mode=external_no_code`;
- `target=react_web_capacitor`;
- project identity;
- source artifact refs;
- screen/action/readiness mapping;
- server authority boundary;
- ownership boundary;
- user-confirmation-required actions;
- forbidden-without-artifact list;
- validation gates;
- non-goals.

## Boundary

The packet is additive and explicit:

- keeps `mtool_no_code` as the supported baseline;
- does not claim replacement of Mtool runtime;
- does not create a React app project;
- does not initialize Capacitor;
- does not install dependencies;
- does not create `package.json`, `capacitor.config.ts`, `ios/`, or `android/`;
- does not own native build, signing, store submission, or secure token storage choices.

Mtool owns:

- contract;
- source artifact references;
- validation map;
- server authority statement;
- non-goals and forbidden/confirmation boundaries.

External owner/tool owns:

- React app shell;
- routing and component system;
- form binding implementation;
- API client/retry strategy;
- Capacitor/native project;
- dependencies;
- native build/signing/store submission.

## Verification

Passed:

```sh
php -l mtool/app/mobile_wrapper_target.php
php -l mtool/scripts/create_mobile_wrapper_target.php
git diff --check
bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/MobileWrapperTargetTest.php
```

Focused PHPUnit result:

```text
OK (28 tests, 193 assertions)
```

## Next candidate

Next useful slice:

`EF-M3 external-output schema/doc hardening`

Possible scope:

- document the `external-output.json` schema in a date-less doc;
- add CLI usage to the durable mobile wrapper docs;
- decide whether `external-output` should be included in the bundle manifest artifact order;
- optionally create a sample35 consumer check that reads `external-output.json` directly.
