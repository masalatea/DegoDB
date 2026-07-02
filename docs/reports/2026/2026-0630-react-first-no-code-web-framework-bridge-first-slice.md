# React-first no-code Web framework bridge first slice

## Status

`FIRST_SLICE_DONE`

## Summary

React + TypeScript を no-code Web bridge の first adapter 基本方向として、Mtool の既存 `screen-definition.json` / `runtime-preview.json` から framework-facing artifact を生成する first slice を追加した。

FS の判断としては、React + JSON Forms / rjsf へ即寄せるよりも、まず Mtool の `no-code-screen-definition-v0` と `no-code-runtime-v0` を保った custom React adapter scaffold を出す方が小さく安全とした。これにより、Mtool は design metadata / screen definition / action intent / validation / sync hint の境界に留まり、実 UI component / routing / client state は frontend 側へ渡す。

## 実装

- 新しい Source Output strategy `no-code-react-bridge` を追加。
- 新しい class type `NoCodeReactBridge` を追加。
- `NO-CODE-REACT-BRIDGE` artifact が以下を生成するようにした。
  - `bridge-contract.json`
  - `package.json`
  - `tsconfig.json`
  - `vite.config.ts`
  - `src/mtoolNoCodeBridge.ts`
  - `src/MtoolNoCodeRuntime.tsx`
  - `src/App.tsx`
  - `src/main.tsx`
  - `README.md`
- `bridge-contract.json` に `screen_definition`、`runtime_preview`、`no-code-runtime-action-intent-v0`、Mtool/frontend ownership boundary を含めた。
- sample28 に `NO-CODE-REACT-BRIDGE` source output seed を追加。
- sample28 pack checker で React bridge artifact 生成・publish と contract / TypeScript helper を確認するようにした。

## 境界

In scope:

- React + TypeScript first adapter scaffold。
- Mtool JSON artifact から React 側への bridge contract。
- list / detail / form runtime preview data と action intent helper の接続。
- sample28 source output seed と focused smoke。

Out of scope:

- React app の npm install / build 実行。
- durable component library ownership を Mtool 内に持つこと。
- JSON Forms / rjsf への正式変換。
- visual builder。
- full generated application shell。
- remote transport / conflict resolution / native target。

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/app/domain_validation.php`
- `php -l mtool/app/project_output_service.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `php -l tests/Integration/SharedDataClassContractFoundationTest.php`
- `bash mtool/scripts/run_sample_pack_phpunit_test.sh --compose-file=sample/tutorials/sample01-simple-table-runtime/compose.yaml --run-script=./sample/tutorials/sample01-simple-table-runtime/run.sh --phpunit-target=/var/www/tests/Integration/SharedDataClassContractFoundationTest.php`
- `make sample28-pack-runtime-test`
- `make test`

## 次

次は post-bridge first slice replan として、React bridge artifact contract を固めるか、React adapter smoke / build proof へ進むか、または JSON Forms / rjsf 変換を小さく試すかを選ぶ。
