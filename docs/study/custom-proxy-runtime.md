# Custom Proxy Runtime

English companion:
This study note uses `sample14-custom-proxy-runtime` to show the smallest custom proxy server artifact publish flow.

`sample14-custom-proxy-runtime` は、custom proxy metadata を使って複数 DBAccess step を 1 つの generated proxy endpoint に束ねる tutorial です。
`CATALOG-SUMMARY` custom proxy を `CUSTOM-PROXY-SERVER` に bind し、PHP proxy server artifact を publish します。

## 実行

```bash
make sample14-pack-runtime-test
```

## 読むファイル

- [sample14 README](../../sample/tutorials/sample14-custom-proxy-runtime/README.md)
- [seed](../../sample/tutorials/sample14-custom-proxy-runtime/seed/)
- [reference/CUSTOM-PROXY-SERVER](../../sample/tutorials/sample14-custom-proxy-runtime/reference/CUSTOM-PROXY-SERVER/)

## 見るポイント

- `project_custom_proxies.custom_proxy_key = CATALOG-SUMMARY`
- `project_custom_proxy_steps`
- `project_custom_proxy_source_output_targets`
- `project_source_outputs.artifact_strategy = custom-proxy-server`
- generated output: `proxyserver-Catalog-Summary.php`

`sample14` では proxy client generation と auth behavior は扱いません。token / auth / fail-closed 境界は `sample16` に分けます。
