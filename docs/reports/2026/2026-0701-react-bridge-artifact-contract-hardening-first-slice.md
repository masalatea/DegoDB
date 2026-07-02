# React bridge artifact contract hardening first slice

## Status

`FIRST_SLICE_DONE`

## Summary

React bridge の generated artifact contract を少し固めた。`bridge-contract.json` に `contract_schema_version` と `contract_invariants` を追加し、build smoke / browser smoke / PHP integration coverage で同じ不変条件を見るようにした。

この slice は React UI の durable ownership を Mtool に寄せるものではなく、framework-facing artifact consumer が見る JSON contract の境界を安定させるためのもの。

## 実装

- `bridge-contract.json` に `contract_schema_version: no-code-react-bridge-contract-v0` を追加。
- `contract_invariants` を追加。
  - `screen_definition_version`
  - `runtime_preview_version`
  - `action_intent_version`
  - `required_files`
  - `screen_model_required_keys`
  - `runtime_cell_shape`
- generated TypeScript contract type に `contract_schema_version` / `contract_invariants` を追加。
- `check_no_code_react_bridge_build_smoke.js` を更新し、contract schema / invariant / required files を確認。
- `check_no_code_react_bridge_browser_smoke.js` を更新し、browser exposed contract でも schema / runtime invariant を確認。
- sample28 checker と shared foundation test に React bridge contract invariant assertion を追加。

## 境界

In scope:

- `bridge-contract.json` の first-slice schema marker。
- React bridge artifact の基本 invariant 固定。
- sample28 and shared foundation test coverage。

Out of scope:

- schema file publication。
- semantic version negotiation。
- durable React component library ownership inside Mtool。
- editable form UX。
- JSON Forms / rjsf transform。

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `php -l mtool/scripts/lib/sample28_no_code_data_app_mvp_check.php`
- `node mtool/scripts/check_no_code_react_bridge_build_smoke.js --help`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`
- `make sample28-no-code-react-bridge-build-smoke`
- `make test`

## 次

次は post-contract-hardening replan として、editable React bridge form state first slice、JSON Forms / rjsf transform probe、または別の no-code product-facing gap を選ぶ。
