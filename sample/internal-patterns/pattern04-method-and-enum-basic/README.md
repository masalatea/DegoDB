# pattern04-method-and-enum-basic

- 役割: legacy `data-Req.php` の helper method + bottom helper function + trailing enum class を、generated base + wrapper へ安全に分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-Req.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-Req.php`, `reference/DATACLASS-PHP/base/data-ReqBase.php`
- disposable runtime root: `work/sample-packs/pattern04-method-and-enum-basic/output/`

検証:

```bash
php mtool/scripts/check_sample13_req_method_and_enum_outputs.php
make pattern04-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample13_req_method_and_enum_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern04-method-and-enum-basic/output/DATACLASS-PHP/data-Req.php
work/sample-packs/pattern04-method-and-enum-basic/output/DATACLASS-PHP/base/data-ReqBase.php
```
