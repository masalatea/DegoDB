# PWA readiness artifact first slice

## Status

`EF_M9_FIRST_SLICE`

## Purpose

Add PWA readiness metadata/checklist output after the output-mode config artifact.

PWA readiness is a delivery/runtime metadata layer. It is not a frontend framework replacement and not a service-worker/offline-sync implementation.

## What changed

Added `pwa-readiness` to mobile wrapper target tooling.

The artifact emits only:

```text
pwa-readiness.json
PWA-READINESS.md
```

CLI:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=pwa-readiness \
  --target-dir=work/source-outputs/SAMPLE28/MOBILE-WRAPPER-TARGET/pwa-readiness
```

The mobile wrapper bundle manifest now includes:

```text
pwa_readiness
```

## Packet contents

The packet records:

- PWA readiness modes:
  - `pwa_disabled`
  - `pwa_installable_online_only`
  - `pwa_static_cache_only`
  - `pwa_sync_contract_required`
- recommended safe mode: `pwa_static_cache_only`;
- app manifest requirements;
- service worker/cache policy expectations;
- browser storage policy;
- offline/sync policy;
- API cacheability rules;
- action summary;
- Mtool-owned and external-owner boundaries;
- behavior forbidden without an explicit artifact.

## Boundary

This artifact does not:

- generate `manifest.webmanifest`;
- generate `service-worker.js`;
- enable offline sync;
- cache business data;
- persist refresh tokens in browser storage;
- enable background sync;
- enable push notifications;
- install dependencies;
- create or mutate an app project;
- create native project files.

## Verification target

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
OK (39 tests, 298 assertions)
```

## Dry-run evidence

Command:

```sh
php mtool/scripts/create_mobile_wrapper_target.php \
  --sample=sample28 \
  --artifact=pwa-readiness \
  --target-dir=/private/tmp/mtool-pwa-readiness-proof-20260715-efm9
```

Result:

```json
{
  "ok": true,
  "source": "sample28",
  "artifact": "pwa-readiness",
  "files": [
    "PWA-READINESS.md",
    "pwa-readiness.json"
  ],
  "error": ""
}
```

Machine check:

```json
{
  "schema_version": "mobile-pwa-readiness-v1",
  "recommended_mode": "pwa_static_cache_only",
  "service_worker_generated": false,
  "offline_sync_default": false,
  "forbids_offline_sync": true
}
```
