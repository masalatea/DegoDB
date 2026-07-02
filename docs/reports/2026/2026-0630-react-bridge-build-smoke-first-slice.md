# React bridge build smoke first slice

## Status

`FIRST_SLICE_DONE`

## Summary

`no-code-react-bridge` が生成する React + TypeScript scaffold について、sample28 の generated artifact を実際に `npm install` / `npm run build` できることを確認する build smoke を追加した。

この slice では、generated source output に `node_modules` や `dist` を残さないよう、bridge artifact を `work/tmp/no-code-react-bridge-build-smoke` へコピーしてから npm build を実行する。

## 実装

- `mtool/scripts/check_no_code_react_bridge_build_smoke.js` を追加。
  - required bridge files を確認。
  - generated `NO-CODE-REACT-BRIDGE` を一時 build directory へコピー。
  - `npm install` と `npm run build` を実行。
  - `bridge-contract.json` の bridge version / framework / action intent version / screen count を確認。
  - `dist/index.html` が生成されたことを確認。
- `make sample28-no-code-react-bridge-build-smoke` を追加。
- React scaffold を build できるように修正。
  - `@types/react` / `@types/react-dom` を devDependencies に追加。
  - imported `bridge-contract.json` を `MtoolBridgeContract` として扱う cast を追加。
  - runtime JSON の実 shape に合わせて field / data TypeScript type を修正。

## 境界

In scope:

- sample28 generated React bridge artifact の install/build smoke。
- generated package の basic TypeScript / Vite buildability。
- generated source output に build byproducts を残さない build smoke。

Out of scope:

- browser rendering smoke。
- React component visual polish。
- JSON Forms / rjsf transform。
- full generated application shell。
- npm package publishing。

## Verification

- `make sample28-no-code-react-bridge-build-smoke`
- `make test`

Manual issue found and fixed:

- Initial build failed because React type packages were missing and `bridge-contract.json` imported as broad JSON types.
- Initial runtime TypeScript type expected `data_type` / `editable` / `current_item`, while actual runtime preview fields use `type` / `readonly` and data uses `rows` or `item`.

## 次

次は post-build-smoke replan として、React bridge browser smoke、artifact contract hardening、JSON Forms / rjsf transform probe のどれを先に進めるかを決める。
