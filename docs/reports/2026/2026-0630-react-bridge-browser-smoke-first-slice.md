# React bridge browser smoke first slice

## Status

`FIRST_SLICE_DONE`

## Summary

`no-code-react-bridge` が生成する React + TypeScript scaffold について、build だけでなく一時 Vite server 上で headless Chrome rendering まで確認する browser smoke を追加した。

この slice は、Mtool の責務を JSON contract / action intent 境界に保ったまま、React artifact が `bridge-contract.json` を読んで screen を render し、action intent helper を browser 上で観測できることを確認する。

## 実装

- `mtool/scripts/check_no_code_react_bridge_browser_smoke.js` を追加。
  - generated `NO-CODE-REACT-BRIDGE` を `work/tmp/no-code-react-bridge-browser-smoke` へコピー。
  - `npm install` と `npm run build` を実行。
  - 一時 Vite dev server を起動し、Playwright / headless Chrome で opening smoke を実行。
  - `no-code-react-bridge-v0` root、`no-code-runtime-v0` runtime version、3 screen、disabled action、operation metadata、action-intent helper を確認。
  - screenshot を `output/playwright/no-code-react-bridge/` に保存。
- `make sample28-no-code-react-bridge-browser-smoke` を追加。
- generated React scaffold に smoke 用の安定した観測点を追加。
  - root / screen / field / action の data attributes。
  - `window.__mtoolNoCodeReactBridgeContract`。
  - `window.__mtoolNoCodeReactBridgeCreateActionIntent`。
  - `window.__mtoolNoCodeReactBridgeLastIntent`。

## 境界

In scope:

- sample28 generated React bridge artifact の browser rendering smoke。
- React artifact が runtime screen を render できることの確認。
- disabled action state と operation metadata の確認。
- browser 上の action-intent helper 観測。

Out of scope:

- durable React component library ownership inside Mtool。
- visual polish。
- form input editing / client state management。
- JSON Forms / rjsf transform。
- full generated application shell。
- remote transport / conflict resolution。

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`

Manual issue found and fixed:

- Disabled button を DOM 側で強制クリックする probe では React props 境界により action intent を安定観測できなかったため、generated App 側に explicit browser-smoke helper を公開した。

## 次

次は post-browser-smoke replan として、React bridge contract hardening、display value / form state shaping、JSON Forms / rjsf transform probe のどれを先に進めるかを決める。
