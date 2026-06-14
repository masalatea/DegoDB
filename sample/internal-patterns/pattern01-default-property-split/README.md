# pattern01-default-property-split

- 役割: legacy `data-TestPattern.php` の `ADDITIONAL CLASS DEFINITION` にある default property を、generated base + wrapper へ安全に分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-TestPattern.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-TestPattern.php`, `reference/DATACLASS-PHP/base/data-TestPatternBase.php`
- disposable runtime root: `work/sample-packs/pattern01-default-property-split/output/`

検証:

```bash
php mtool/scripts/check_sample9_testpattern_default_property_outputs.php
make pattern01-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample9_testpattern_default_property_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern01-default-property-split/output/DATACLASS-PHP/data-TestPattern.php
work/sample-packs/pattern01-default-property-split/output/DATACLASS-PHP/base/data-TestPatternBase.php
```
