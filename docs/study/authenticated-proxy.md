# Authenticated Proxy

English companion:
This study note uses `sample16-authenticated-proxy` to show the smallest static bearer authenticated generated proxy flow.

`sample16-authenticated-proxy` は、generated single proxy server endpoint の `static-bearer` auth と fail-closed behavior を確認する tutorial です。
`Authorization: Bearer <token>` が無い、形式が違う、間違い、または `DEGODB_PROXY_BEARER_TOKEN` が未設定の場合に失敗し、正しい bearer token だけが通ることを固定します。

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
- test は missing `Authorization`、malformed `Authorization`、wrong token、env missing を fail-closed として確認します。
- HTTP server ではなく、generated handler の auth 境界を直接検証します。

## Boundary

`sample16` では `static-bearer` だけを扱います。legacy `ProjectToken`、`GetFunc`、`ProjectTokenOrGetFunc`、`LoginCookieToken`、HTTP browser smoke は別 scope に分けます。
