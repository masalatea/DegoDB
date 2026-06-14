# pattern03-method-only-split

- 役割: legacy `data-da.php` / `data-dataclass.php` の method-only `ADDITIONAL CLASS DEFINITION` を、generated base + wrapper へ安全に分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-da.php`, `tests/fixtures/legacy-dbclasses/data-dataclass.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-da.php`, `reference/DATACLASS-PHP/base/data-daBase.php`, `reference/DATACLASS-PHP/data-dataclass.php`, `reference/DATACLASS-PHP/base/data-dataclassBase.php`
- disposable runtime root: `work/sample-packs/pattern03-method-only-split/output/`

検証:

```bash
php mtool/scripts/check_sample11_da_dataclass_method_only_outputs.php
make pattern03-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample11_da_dataclass_method_only_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern03-method-only-split/output/DATACLASS-PHP/data-da.php
work/sample-packs/pattern03-method-only-split/output/DATACLASS-PHP/base/data-daBase.php
work/sample-packs/pattern03-method-only-split/output/DATACLASS-PHP/data-dataclass.php
work/sample-packs/pattern03-method-only-split/output/DATACLASS-PHP/base/data-dataclassBase.php
```
