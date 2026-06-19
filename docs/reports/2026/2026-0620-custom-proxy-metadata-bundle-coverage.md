# 2026-06-20 Custom Proxy Metadata Bundle Coverage

## Status

- status: `DONE`
- purpose: project metadata bundle の custom proxy coverage first slice

## Summary

`project-core` metadata bundle に `custom_proxies` section を追加した。

これにより、custom proxy metadata のうち以下を export / preview / apply で持ち運べる。

- custom proxy 本体
- custom proxy steps
- custom proxy target source output keys
- `auth_policy_version`
- `auth_policy_json`

## Scope

Included:

- `custom-proxies.json` を project metadata bundle section として追加。
- bundle manifest の `included_sections` / `files` / summary に custom proxy count を追加。
- custom proxy auth policy JSON を DBAccess single-function proxy と同じ validator で検証。
- populated secret-like fields を含む custom proxy auth policy JSON を preview で拒否。
- import apply で custom proxy 本体、target source output binding、step を復元。
- sample15 / sample26 の project metadata bundle reference を更新。

Out of scope:

- custom proxy runtime auth の新方式追加。
- HTML / LanguageResource / broader security setting bundle expansion。
- approval workflow。

## Verification

- `php -l mtool/app/project_metadata_bundle.php`
- `php -l tests/Integration/ProjectMetadataBundleContractTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample15-project-metadata-export-import/compose.yaml --run-script=./sample/tutorials/sample15-project-metadata-export-import/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/ProjectMetadataBundleContractTest.php`
  - `OK (4 tests, 245 assertions)`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample15-project-metadata-export-import/compose.yaml --run-script=./sample/tutorials/sample15-project-metadata-export-import/run.sh --apply-pack-seed --phpunit-target=/var/www/tests/Integration/Sample15ProjectMetadataExportImportTest.php`
  - `OK (1 test, 8 assertions)`
- `make sample26-pack-runtime-test`
  - `OK (1 test, 11 assertions)`
