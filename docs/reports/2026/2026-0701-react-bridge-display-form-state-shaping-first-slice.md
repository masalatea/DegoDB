# React bridge display/form state shaping first slice

## Status

`FIRST_SLICE_DONE`

## Summary

React bridge browser smoke で見えた `[object Object]` 表示を解消するため、generated React + TypeScript scaffold に runtime cell helper を追加した。

この slice では、runtime preview の `{ value, display_value }` cell shape を React 側でそのまま文字列化せず、表示は `display_value` 優先、action intent input は `value` へ正規化する。

## 実装

- `src/mtoolNoCodeBridge.ts` template に helper を追加。
  - `currentItem(screen)`
  - `displayRuntimeValue(value)`
  - `runtimeInputValue(value)`
- `createActionIntent()` が input cell object を scalar / null に正規化するようにした。
- `src/MtoolNoCodeRuntime.tsx` template を更新。
  - list/detail は `displayRuntimeValue()` で表示。
  - form screen は readonly input として field value を表示。
  - smoke 用の `data-display-value` を出す。
- React bridge browser smoke を更新。
  - form input が生成されることを確認。
  - body text に `[object Object]` が出ないことを確認。
  - action intent input が raw cell object ではなく value shape になることを確認。

## 境界

In scope:

- generated React bridge scaffold 内の runtime cell display helper。
- generated React bridge scaffold 内の action-intent input normalization。
- sample28 browser smoke coverage。

Out of scope:

- editable form state / validation UX。
- visual styling polish。
- durable React component library ownership inside Mtool。
- JSON Forms / rjsf transform。
- full generated application shell。

## Verification

- `php -l mtool/app/project_output_no_code_runtime_generator.php`
- `node mtool/scripts/check_no_code_react_bridge_browser_smoke.js --help`
- `make sample28-no-code-react-bridge-browser-smoke`

## 次

次は post-display/form-state-shaping replan として、React bridge artifact contract hardening、editable form state first slice、JSON Forms / rjsf transform probe のどれを先に進めるかを決める。
