# pattern07-companion-declarations-multiclass

- 役割: legacy `data-LiveCheckResult.php` の 3-class no-top-level companion declarations variant を、generated base + wrapper へ分離できることを確認する file-based sample
- 入力 source: `tests/fixtures/legacy-dbclasses/data-LiveCheckResult.php`
- durable actual output sample: `reference/DATACLASS-PHP/data-LiveCheckResult.php`, `reference/DATACLASS-PHP/base/data-LiveCheckResultBase.php`
- disposable runtime root: `work/sample-packs/pattern07-companion-declarations-multiclass/output/`

検証:

```bash
php mtool/scripts/check_sample16_livecheckresult_companion_declarations_outputs.php
make pattern07-output-test
make test
```

reference を更新せず disposable output だけ作る:

```bash
php mtool/scripts/check_sample16_livecheckresult_companion_declarations_outputs.php --no-reference-check
```

生成物:

```text
work/sample-packs/pattern07-companion-declarations-multiclass/output/DATACLASS-PHP/data-LiveCheckResult.php
work/sample-packs/pattern07-companion-declarations-multiclass/output/DATACLASS-PHP/base/data-LiveCheckResultBase.php
```
