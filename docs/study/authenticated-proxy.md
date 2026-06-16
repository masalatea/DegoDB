# Authenticated Proxy

English companion:
This study note uses `sample16-authenticated-proxy` to show the smallest ProjectToken authenticated generated proxy flow.

`sample16-authenticated-proxy` は、generated single proxy server endpoint の `ProjectToken` auth と fail-closed behavior を確認する tutorial です。
`TOKEN` が無い、空、間違い、または `MTOOL_PROXY_PROJECT_TOKEN` が未設定の場合に失敗し、正しい token だけが通ることを固定します。

## 実行

```bash
make sample16-pack-runtime-test
```

## 読むファイル

- [sample16 README](../../sample/tutorials/sample16-authenticated-proxy/README.md)
- [seed](../../sample/tutorials/sample16-authenticated-proxy/seed/)
- [reference/AUTH-PROXY-SERVER](../../sample/tutorials/sample16-authenticated-proxy/reference/AUTH-PROXY-SERVER/)
- [Auth plan](../reports/2026/2026-0525-openapi-auth-persistence-plan.md)

## 見るポイント

- `AUTH-PROXY-SERVER` は generated single proxy server artifact です。
- test は missing `TOKEN`、empty `TOKEN`、wrong token、env missing を fail-closed として確認します。
- HTTP server ではなく、generated handler の auth 境界を直接検証します。

## Boundary

`sample16` では `ProjectToken` だけを扱います。`GetFunc`、`ProjectTokenOrGetFunc`、`LoginCookieToken`、HTTP browser smoke は後続 sample に分けます。
