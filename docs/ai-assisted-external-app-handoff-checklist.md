# AI-Assisted External App Handoff Checklist / AI支援 external app handoff checklist

この文書は、Codex / Claude などの AI に Mtool の外部 consumer 向け artifact を読ませ、外部 app 作成や wrapper 実装の準備を依頼するときの checklist である。

これは production app 生成手順ではない。AI が実装に入る前に、何を読むか、何を確認するか、どこまでが Mtool 所有で、どこからが外部 owner 所有かを揃えるための handoff checklist である。

## 対象

AI が読む可能性のある Mtool artifact / docs:

- `external-output.json`
- `EXTERNAL-OUTPUT.md`
- `task.json`
- `TASK.md`
- `mobile-app-handoff.json`
- `output-mode-config.json`
- `pwa-readiness.json`
- `flutter-input-packet.json`
- `react-native-input-packet.json`
- `sync-server-input.json`
- `sync-client-input.json`

## 最初に AI が確認すること

AI は作業前に次を説明する。

| 確認項目 | 説明 |
| --- | --- |
| 目的 | 何を作ろうとしているか |
| 入力 | どの Mtool artifact / packet を読むか |
| 出力先 | どの directory に書くか |
| 上書き | 既存 file を上書きするか |
| 実行 command | どの command を実行するか |
| validation | 作成後に何で検証するか |
| 禁止 action | 何をしないか |
| 外部 owner の責務 | app creator / external framework が持つ範囲 |

AI はこの説明後、file 書き込み、dependency install、project init、network call、native build の前に利用者へ明示確認する。

## 入力優先順位

入力が複数ある場合、優先順位は次。

1. user instruction in the current task
2. generated `task.json` / `TASK.md`
3. selected Mtool packet
4. permanent docs
5. dated history files
6. AI inference

AI inference は最後であり、packet / docs と矛盾する場合は packet / docs を優先する。

## Output mode 確認

`output-mode-config.json` がある場合、AI はまず mode を確認する。

| Mode | AI の扱い |
| --- | --- |
| `mtool_no_code` | Mtool output を primary として扱う。外部 app 生成へ進まない |
| `external_no_code` | 外部 app / framework handoff を扱う。ただし実装前に確認する |
| `hybrid` | Mtool output と外部 handoff の両方を扱う。canonical surface を確認する |

## Consumer 別 checklist

### React/Web + Capacitor

読むもの:

- `external-output.json`
- `mobile-app-handoff.json`
- `output-mode-config.json`
- 必要なら `pwa-readiness.json`

確認すること:

- React app shell は外部 owner が持つ
- Capacitor init / `cap sync` は明示確認なしに行わない
- `ios/` / `android/` を作らない
- dependency install をしない
- signing / store submission をしない

### PWA

読むもの:

- `pwa-readiness.json`
- `output-mode-config.json`
- `external-output.json`

確認すること:

- manifest / service worker は自動生成しない
- offline sync / background sync / push は自動実装しない
- browser storage と API cache policy は app owner が確認する

### Flutter WebView

読むもの:

- `flutter-input-packet.json`
- `output-mode-config.json`
- `pwa-readiness.json`

確認すること:

- Flutter project は自動生成しない
- React/PWA source を WebView で読む boundary を確認する
- auth / deep link / token storage は app owner が決める
- native bridge は default-off

### React Native

読むもの:

- `react-native-input-packet.json`
- `external-output.json`
- `output-mode-config.json`

確認すること:

- React Native source は自動生成しない
- package / navigation / state management 選定は app owner が決める
- native module setup / build / signing は行わない

### Shared-state sync external runtime

読むもの:

- `sync-server-input.json`
- `sync-client-input.json`
- [Shared State Sync Bundle Manifest](shared-state-sync-bundle-manifest.md)
- [Shared State Sync Validation Checklist](shared-state-sync-validation-checklist.md)

確認すること:

- production Node.js server は自動生成しない
- client SDK は自動生成しない
- SSO setup / token storage は app owner が持つ
- server/client packet の責務を混ぜない

## 禁止 action

明示確認なしに行わない。

- dependency install
- project init
- native project generation
- app source overwrite
- DB migration / DB write
- network call
- production server start
- public port open
- signing
- store submission
- token / secret 書き込み

## 確認文テンプレート

AI は作業前に、少なくとも次の形で確認する。

```text
Mtool の次の artifact を読み、外部 app handoff の準備をします。

読むもの:
- ...

書き込み先:
- ...

実行する command:
- ...

実行しないこと:
- dependency install
- native project generation
- signing / store submission
- user source overwrite

生成後の validation:
- ...

この範囲で進めてよいですか？
```

## 成功条件

この checklist の成功条件は次。

- AI が読む artifact を明示できる
- 書き込み先が明示されている
- 上書き policy が明示されている
- 禁止 action が明示されている
- validation command が明示されている
- Mtool-owned と external-owned の境界が説明できる

production app が完成したことは、この checklist の成功条件ではない。
