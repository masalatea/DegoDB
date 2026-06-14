# pattern02-wrapper-property-helper

- 役割: legacy `data-dbtablecolumns.php` の wrapper property + helper method `ADDITIONAL CLASS DEFINITION` を、generated base + wrapper へ安全に分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-dbtablecolumns.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-dbtablecolumns.php`, `reference/DATACLASS-PHP/base/data-dbtablecolumnsBase.php`
- disposable runtime root: `work/sample-packs/pattern02-wrapper-property-helper/output/`

検証:

```bash
php mtool/scripts/check_sample12_dbtablecolumns_wrapper_property_outputs.php
make pattern02-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample12_dbtablecolumns_wrapper_property_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern02-wrapper-property-helper/output/DATACLASS-PHP/data-dbtablecolumns.php
work/sample-packs/pattern02-wrapper-property-helper/output/DATACLASS-PHP/base/data-dbtablecolumnsBase.php
```
