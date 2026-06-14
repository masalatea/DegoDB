# pattern06-companion-declarations-no-top-level

- 役割: legacy `data-BuildLog.php` の no-top-level companion declarations variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-BuildLog.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-BuildLog.php`, `reference/DATACLASS-PHP/base/data-BuildLogBase.php`
- disposable runtime root: `work/sample-packs/pattern06-companion-declarations-no-top-level/output/`

検証:

```bash
php mtool/scripts/check_sample15_buildlog_companion_declarations_outputs.php
make pattern06-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample15_buildlog_companion_declarations_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern06-companion-declarations-no-top-level/output/DATACLASS-PHP/data-BuildLog.php
work/sample-packs/pattern06-companion-declarations-no-top-level/output/DATACLASS-PHP/base/data-BuildLogBase.php
```
