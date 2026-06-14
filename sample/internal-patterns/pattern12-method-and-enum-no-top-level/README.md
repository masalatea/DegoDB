# pattern12-method-and-enum-no-top-level

- 役割: legacy `data-daCustomProxy.php` の no-top-level method-and-enum variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-daCustomProxy.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-daCustomProxy.php`, `reference/DATACLASS-PHP/base/data-daCustomProxyBase.php`
- disposable runtime root: `work/sample-packs/pattern12-method-and-enum-no-top-level/output/`

検証:

```bash
php mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php
make pattern12-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern12-method-and-enum-no-top-level/output/DATACLASS-PHP/data-daCustomProxy.php
work/sample-packs/pattern12-method-and-enum-no-top-level/output/DATACLASS-PHP/base/data-daCustomProxyBase.php
```
